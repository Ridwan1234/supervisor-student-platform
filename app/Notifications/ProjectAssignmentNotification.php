<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectAssignmentNotification extends Notification
{
    use Queueable;

    protected $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'project_id' => $this->project->id,
            'title' => $this->project->title,
            'message' => "You have been assigned to the project: {$this->project->title}.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'data' => [
                'project_id' => $this->project->id,
                'title' => $this->project->title,
                'message' => "You have been assigned to the project: {$this->project->title}.",
            ],
        ];
    }
}
