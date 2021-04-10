<?php
declare(strict_types=1);


namespace Tests\Feature\Feeds;


use App\Models\Book;
use App\Services\FeedFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class AddFeedToFavoriteListTest extends TestCase
{
    public function testAddFeedToFavoriteList(): void
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

        $this->assertDatabaseMissing('feed_user', [
            'feed_id' => $feed->id,
        ]);

        $user = $this->login();
        $this->jsonApiPost("feeds/{$feed->id}/favorite");
        $this->jsonApiPost("feeds/{$feed->id}/favorite");

        $this->assertDatabaseHas('feed_user', [
            'feed_id' => $feed->id,
            'user_id' => $user->id,
            'is_favorite' => true,
        ]);
    }
}
