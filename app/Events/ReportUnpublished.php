<?php

namespace App\Events;

use App\Models\UserBook;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportUnpublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private UserBook $userBook;

    /**
     * Create a new event instance.
     *
     * @param UserBook $userBook
     */
    public function __construct(UserBook $userBook)
    {
        $this->userBook = $userBook;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * @return UserBook
     */
    public function getUserBook(): UserBook
    {
        return $this->userBook;
    }
}
