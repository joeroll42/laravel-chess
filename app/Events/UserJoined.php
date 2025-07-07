<?php

namespace App\Events;

use App\Models\User;
use Exception;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     * @throws Exception
     */
    public function __construct(User $user = null)
    {
        $this->user = $user ?? auth()->user();

        if (! $this->user) {
            throw new Exception('User not found');
        }
    }

    public function broadcastOn(): Channel
    {
        return new PresenceChannel('online-users');
    }

    public function broadcastQueue(): string
    {
        return 'presence-events';
    }


    public function broadcastWith(): array
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserJoined';
    }
}
