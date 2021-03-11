<?php

namespace App\Events;

use App\Models\Feed;
use App\Models\User;
use App\Models\UserBook;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Feed $feed;

    /**
     * Create a new event instance.
     *
     * @param UserBook $userBook
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        /** @var User $author */
        $author = User::query()->findOrFail($this->feed->author_id);
        $followers = $author->followers()->pluck('id');

        $channels = [];

        foreach ($followers as $followerId) {
            $channels[] = new PrivateChannel("feeds.{$followerId}");
        }

        if ($this->feed->target_id) {
            $channels[] = new PrivateChannel("feeds.{$this->feed->target_id}");
        }

        return $channels;
    }

    /**
     * @return Feed
     */
    public function getFeed(): Feed
    {
        return $this->feed;
    }
}
