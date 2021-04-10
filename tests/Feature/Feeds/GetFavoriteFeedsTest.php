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

final class GetFavoriteFeedsTest extends TestCase
{
    public function testGetFavoriteFeeds(): void
    {
        Queue::fake();
        Notification::fake();
        Event::fake();

        $perPage = 3;
        $feedsAuthor = $this->createUser();
        $anotherUser = $this->createUser();
        $user = $this->login();

        $publishedReportsCount = 5;
        $favoriteFeedsCount = 4;

        $feedFactory = new FeedFactory();
        $feeds = [];

        for ($i = 1; $i <= $publishedReportsCount; $i++) {
            $book = factory(Book::class)->create();
            $userBook = $feedsAuthor->addBook($book);
            $userBook->publishReport();
            $feed = $feedFactory->reportPublished($userBook);
            $feed->save();
            $feed->is_favorite = true;
            $feeds[] = $feed;
        }

        for ($i = 0; $i < $favoriteFeedsCount; $i++) {
            $user->addToFavoriteList($feeds[$i]);
            $anotherUser->addToFavoriteList($feeds[$i]);
        }

        $responseData = $this->jsonApiGet("feeds?type=report_published&is_favorite=1&per_page={$perPage}");

        self::assertCount($perPage, $responseData['data']);
        self::assertEquals($favoriteFeedsCount, $responseData['meta']['total']);

        self::assertModelsResourcesInArray(array_slice($feeds, 0, $perPage), $responseData['data'], false);

    }
}
