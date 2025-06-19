<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskAssignedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'message' => "تم تعيين مهمة جديدة لك: {$this->task->name}",
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifications.' . $notifiable->id);
    }
}
