<?php

namespace App\Services;

use App\Events\NotificationSent;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a new message notification
     */
    public static function newMessage($sender, $receiver, $message)
    {
        $notification = [
            'type' => 'new_message',
            'title' => 'New Message',
            'message' => "You have a new message from {$sender->name}",
            'data' => [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'message_preview' => substr($message, 0, 100),
                'conversation_id' => $receiver->id,
            ],
            'icon' => 'bi bi-chat-dots',
            'color' => 'primary',
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $receiver->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        Log::info("notification sent");

        event(new NotificationSent($notification, $receiver->id));
    }

    /**
     * Send a task assignment notification
     */
    public static function taskAssigned($task, $assignee)
    {
        $notification = [
            'type' => 'task_assigned',
            'title' => 'New Task Assigned',
            'message' => "You have been assigned a new task: {$task->title}",
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'due_date' => $task->due_date,
                'project_id' => $task->project_id,
            ],
            'icon' => 'bi bi-list-check',
            'color' => 'warning',
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $assignee->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        event(new NotificationSent($notification, $assignee->id));
    }

    /**
     * Send a project assignment notification
     */
    public static function projectAssigned($project, $student)
    {
        $notification = [
            'type' => 'project_assigned',
            'title' => 'New Project Assignment',
            'message' => "You have been assigned to project: {$project->title}",
            'data' => [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'supervisor_id' => $project->supervisor_id,
            ],
            'icon' => 'bi bi-folder',
            'color' => 'info',
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $student->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        event(new NotificationSent($notification, $student->id));
    }

    /**
     * Send a deadline reminder notification
     */
    public static function deadlineReminder($item, $user, $type = 'task')
    {
        $title = $type === 'task' ? $item->title : $item->name;
        $notification = [
            'type' => 'deadline_reminder',
            'title' => 'Deadline Reminder',
            'message' => "Reminder: {$title} is due soon",
            'data' => [
                'item_id' => $item->id,
                'item_title' => $title,
                'due_date' => $item->due_date,
                'type' => $type,
            ],
            'icon' => 'bi bi-clock',
            'color' => 'danger',
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $user->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        event(new NotificationSent($notification, $user->id));
    }

    /**
     * Send a file shared notification
     */
    public static function fileShared($file, $sharedWith)
    {
        $notification = [
            'type' => 'file_shared',
            'title' => 'File Shared',
            'message' => "A file has been shared with you: {$file->name}",
            'data' => [
                'file_id' => $file->id,
                'file_name' => $file->name,
                'shared_by' => Auth::user()->name,
            ],
            'icon' => 'bi bi-file-earmark',
            'color' => 'success',
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $sharedWith->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        event(new NotificationSent($notification, $sharedWith->id));
    }

    /**
     * Send a group message notification
     */
    public static function groupMessage($group, $sender, $message)
    {
        // Notify all group members except the sender
        foreach ($group->members as $member) {
            if ($member->id !== $sender->id) {
                $notification = [
                    'type' => 'group_message',
                    'title' => 'New Group Message',
                    'message' => "New message in {$group->name} from {$sender->name}",
                    'data' => [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'sender_id' => $sender->id,
                        'sender_name' => $sender->name,
                        'message_preview' => substr($message, 0, 100),
                    ],
                    'icon' => 'bi bi-people-fill',
                    'color' => 'primary',
                ];

                // Create notification in database
                Notification::create([
                    'user_id' => $member->id,
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'data' => $notification['data'],
                    'icon' => $notification['icon'],
                    'color' => $notification['color'],
                ]);

                event(new NotificationSent($notification, $member->id));
            }
        }
    }

    /**
     * Send a progress update notification
     */
    public static function progressUpdate($project, $student, $progress)
    {
        $notification = [
            'type' => 'progress_update',
            'title' => 'Progress Update',
            'message' => "{$student->name} updated progress on {$project->title}",
            'data' => [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'progress' => $progress,
            ],
            'icon' => 'bi bi-graph-up',
            'color' => 'success',
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $project->supervisor_id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        event(new NotificationSent($notification, $project->supervisor_id));
    }

    /**
     * Send a custom notification
     */
    public static function custom($user, $title, $message, $type = 'info', $data = [])
    {
        $notification = [
            'type' => 'custom',
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => 'bi bi-bell',
            'color' => $type,
        ];

        // Create notification in database
        Notification::create([
            'user_id' => $user->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'icon' => $notification['icon'],
            'color' => $notification['color'],
        ]);

        event(new NotificationSent($notification, $user->id));
    }
} 