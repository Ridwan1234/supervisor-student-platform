<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:custom_notifications,id'
        ]);

        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $request->notification_id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();
        $preferences = $user->notification_preferences ?? [
            'email' => true,
            'push' => true,
            'sound' => true,
            'types' => [
                'new_message' => true,
                'task_assigned' => true,
                'project_assigned' => true,
                'deadline_reminder' => true,
                'file_shared' => true,
                'group_message' => true,
                'progress_update' => true,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'email' => 'boolean',
            'push' => 'boolean',
            'sound' => 'boolean',
            'types' => 'array',
            'types.*' => 'boolean'
        ]);

        $user = Auth::user();
        $user->notification_preferences = $request->all();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated'
        ]);
    }

    /**
     * Test notification (for development)
     */
    public function testNotification()
    {
        $user = Auth::user();
        
        NotificationService::custom(
            $user,
            'Test Notification',
            'This is a test notification to verify the system is working.',
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent'
        ]);
    }
} 