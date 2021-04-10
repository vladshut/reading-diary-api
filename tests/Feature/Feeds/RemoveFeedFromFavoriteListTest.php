<?php
declare(strict_types=1);


namespace Tests\Feature\Feeds;


use App\Http\Resources\FeedResource;
use App\Models\Book;
use App\Services\FeedFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class RemoveFeedFromFavoriteListTest extends TestCase
{
    public function testRemoveFeedFromFavoriteList(): void
    {
        Queue::fake();
        Notification::fake();
        Event::fake();

        $feedsAuthor = $this->createUser();
        $feedFactory = new FeedFactory();

        $book = factory(Book::class)->create();
        $userBook = $feedsAuthor->addBook($book);
        $userBook->publishReport();
        $feed = $feedFactory->reportPublished($userBook);
        $feed->save();

        $user = $this->login();
        $user->addToFavoriteList($feed);

        $this->assertDatabaseHas('feed_user', [
            'feed_id' => $feed->id,
            'user_id' => $user->id,
            'is_favorite' => true,
        ]);

        $this->jsonApiDelete("feeds/{$feed->id}/favorite");
        $this->jsonApiDelete("feeds/{$feed->id}/favorite");

        $this->assertDatabaseHas('feed_user', [
            'feed_id' => $feed->id,
            'user_id' => $user->id,
            'is_favorite' => false,
        ]);
    }
}
