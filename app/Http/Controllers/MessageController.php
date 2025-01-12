<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Group;
use App\Models\Message;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function fetchMessages(Request $request)
    {
        $messages = Message::where(function ($query) use ($request) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $request->receiver_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->receiver_id)
                ->where('receiver_id', auth()->id());
        })->orderBy('created_at')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function fetchGroupMessages($groupId)
    {
        $messages = Message::where('group_id', $groupId)->with('sender')->orderBy('created_at')->get();
        return response()->json($messages);
    }

    // public function sendGroupMessage(Request $request)
    // {
    //     $request->validate([
    //         'group_id' => 'required|exists:groups,id',
    //         'message' => 'required|string',
    //     ]);

    //     $message = Message::create([
    //         'sender_id' => auth()->id(),
    //         'group_id' => $request->group_id,
    //         'message' => $request->message,
    //     ]);

    //     broadcast(new MessageSent($message))->toOthers();

    //     return response()->json($message);
    // }


public function sendGroupMessage(Request $request)
{
    $request->validate([
        'group_id' => 'required|exists:groups,id',
        'message' => 'required|string',
    ]);

    $message = Message::create([
        'sender_id' => auth()->id(),
        'group_id' => $request->group_id,
        'message' => $request->message,
    ]);

    // Notify all group members except the sender
    $group = Group::find($request->group_id);
    foreach ($group->members as $member) {
        if ($member->id !== auth()->id()) {
            $member->notify(new GeneralNotification(
                'New Message in Group: ' . $group->name,
                $message->message,
                '/groups/' . $group->id
            ));
        }
    }

    broadcast(new MessageSent($message))->toOthers();

    return response()->json($message);
}

}
