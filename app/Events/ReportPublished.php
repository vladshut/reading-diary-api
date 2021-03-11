<?php

namespace App\Events;

use App\Models\UserBook;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportPublished
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
        return new PrivateChannel('report_published');
    }

    /**
     * @return UserBook
     */
    public function getUserBook(): UserBook
    {
        return $this->userBook;
    }
}
