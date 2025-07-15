<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeAcceptedNow implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $creatorId;
    public int $challengeId;

    public function __construct(int $creatorId, int $challengeId)
    {
        $this->creatorId   = $creatorId;
        $this->challengeId = $challengeId;
    }

    public function broadcastOn(): PrivateChannel
    {
        // Must match the channel in routes/channels.php
        return new PrivateChannel("App.Models.User.{$this->creatorId}");
    }

    public function broadcastWith(): array
    {
        return [
            'challenge_id' => $this->challengeId,
            'message'      => "Your challenge #{$this->challengeId} was just accepted!",
        ];
    }
}
