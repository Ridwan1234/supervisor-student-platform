<?php

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $progress;

    public function __construct($progress)
    {
        $this->progress = $progress;
    }

    public function broadcastOn()
    {
        return ['project-progress'];
    }

    public function broadcastWith()
    {
        return ['progress' => $this->progress];
    }
}
