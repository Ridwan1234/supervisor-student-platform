<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class MessageController extends Controller
{
    public function fetchMessages(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $messages = Message::with(['sender', 'receiver', 'attachments'])
            ->where(function ($query) use ($request) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $request->receiver_id);
            })->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)
                    ->where('receiver_id', Auth::id());
            })
            ->whereNull('group_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120', // 5MB per file
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'message_type' => 'text',
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('message-attachments');
                
                $message->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Load relationships for broadcasting
        $message->load(['sender', 'receiver', 'attachments']);

        broadcast(new MessageSent($message))->toOthers();

        // Send notification to receiver
        $receiver = User::find($request->receiver_id);
        NotificationService::newMessage(Auth::user(), $receiver, $request->message);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    }

    public function fetchGroupMessages($groupId)
    {
        $group = Group::with('members')->findOrFail($groupId);
        
        // Check if user is member of the group
        if (!$group->members->contains(Auth::id())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $messages = Message::with(['sender', 'attachments'])
            ->where('group_id', $groupId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'group' => $group,
                'messages' => $messages
            ]
        ]);
    }

    public function sendGroupMessage(Request $request, $groupId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120', // 5MB per file
        ]);

        $group = Group::with('members')->findOrFail($groupId);
        
        // Check if user is member of the group
        if (!$group->members->contains(Auth::id())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'group_id' => $groupId,
            'message' => $request->message,
            'message_type' => 'text',
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('message-attachments');
                
                $message->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Load relationships for broadcasting
        $message->load(['sender', 'attachments']);

        // Notify all group members except the sender
        NotificationService::groupMessage($group, Auth::user(), $request->message);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Group message sent successfully',
            'data' => $message
        ]);
    }

    public function getConversations()
    {
        $user = Auth::user();
        
        // Get individual conversations
        $conversations = Message::select('sender_id', 'receiver_id')
            ->where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->whereNull('group_id')
            ->get()
            ->map(function($message) use ($user) {
                $otherUserId = $message->sender_id === $user->id ? $message->receiver_id : $message->sender_id;
                return $otherUserId;
            })
            ->unique()
            ->values();

        $conversationUsers = User::whereIn('id', $conversations)->get();
        
        // Add last message and unread count for each conversation
        foreach ($conversationUsers as $conversationUser) {
            $lastMessage = Message::where(function($query) use ($user, $conversationUser) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $conversationUser->id);
            })->orWhere(function($query) use ($user, $conversationUser) {
                $query->where('sender_id', $conversationUser->id)
                    ->where('receiver_id', $user->id);
            })->with('sender')->latest()->first();
            
            $unreadCount = Message::where('sender_id', $conversationUser->id)
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->count();
            
            $conversationUser->lastMessage = $lastMessage;
            $conversationUser->unreadCount = $unreadCount;
        }

        // Get group conversations
        $groups = Group::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['lastMessage', 'members'])
        ->get();
        
        // Add unread count for each group
        foreach ($groups as $group) {
            $unreadCount = Message::where('group_id', $group->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();
            
            $group->unreadCount = $unreadCount;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'individual_conversations' => $conversationUsers,
                'group_conversations' => $groups
            ]
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id'
        ]);

        Message::whereIn('id', $request->message_ids)
            ->where('receiver_id', Auth::id())
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }

    public function deleteMessage($id)
    {
        $message = Message::findOrFail($id);
        
        // Only sender can delete message
        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function downloadAttachment($id)
    {
        $attachment = \App\Models\MessageAttachment::findOrFail($id);
        
        // Check if user has access to this attachment
        $message = $attachment->message;
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $path = storage_path('app/private/' . $attachment->path);
        
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($path, $attachment->name);
    }
}
