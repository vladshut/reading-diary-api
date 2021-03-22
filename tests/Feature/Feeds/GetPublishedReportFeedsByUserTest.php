<?php
declare(strict_types=1);


namespace Tests\Feature\Feeds;


use App\Http\Resources\FeedResource;
use App\Models\Book;
use App\Services\FeedFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class GetPublishedReportFeedsByUserTest extends TestCase
{
    public function testGetPublishedReportFeedsByUser(): void
    {
        Queue::fake();
        Notification::fake();
        Event::fake();

        $feedsAuthor = $this->createUser();

        $publishedReportsCount = 4;

        $feedFactory = new FeedFactory();
        $feeds = [];

        for ($i = 1; $i <= $publishedReportsCount; $i++) {
            $book = factory(Book::class)->create();
            $userBook = $feedsAuthor->addBook($book);
            $userBook->publishReport();
            $feed = $feedFactory->reportPublished($userBook);
            $feed->save();
            $feeds[] = $feed;
        }

        $this->login();
        $responseData = $this->jsonApiGet("feeds?type=report_published&author={$feedsAuthor->id}&per_page=3");

        self::assertCount(3, $responseData['data']);

        foreach (array_slice($feeds, 0, 3) as $feed) {
            $feed = $feed->refresh();
            $feedResource = new FeedResource($feed);
            $expectedData = $feedResource->toArray(new Request());

            self::assertArrayHasArrayWithSubset($responseData['data'], $expectedData);
        }
    }
}
