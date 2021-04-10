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

final class FindReportPublishedFeedsTest extends TestCase
{
    /**
     * @param string $query
     * @param array $exFeedsIndexes
     * @param int|null $exTotalCount
     * @throws \JsonException
     * @dataProvider findReportPublishedFeedsDataProvider
     */
    public function testFindReportPublishedFeeds(string $query, array $exFeedsIndexes = [], int $exTotalCount = null): void
    {
        Queue::fake();
        Notification::fake();
        Event::fake();

        if ($exTotalCount === null) {
            $exTotalCount = count($exFeedsIndexes);
        }

        $feedsAuthor = $this->createUser();

        $publishedReportsCount = 4;

        $feedFactory = new FeedFactory();
        $feeds = [];

        for ($i = 1; $i <= $publishedReportsCount; $i++) {
            $book = factory(Book::class)->create(['title' => 'book' . $i]);
            $userBook = $feedsAuthor->addBook($book);
            $userBook->publishReport();
            $feed = $feedFactory->reportPublished($userBook);
            $feed->save();
            $feeds[] = $feed;
        }

        $this->login();
        $responseData = $this->jsonApiGet("feeds?type=report_published&per_page=3&query={$query}");

        self::assertCount(count($exFeedsIndexes), $responseData['data']);

        $exFeeds = Arr::only($feeds, $exFeedsIndexes);

        self::assertCount(count($exFeedsIndexes), $exFeeds);

        foreach ($exFeeds as $feed) {
            $feed = $feed->refresh();
            $feedResource = new FeedResource($feed);
            $expectedData = $feedResource->toArray(new Request());

            self::assertArrayHasArrayWithSubset($responseData['data'], $expectedData);
        }

        self::assertEquals($exTotalCount, $responseData['meta']['total']);
    }

    public function findReportPublishedFeedsDataProvider(): array
    {
        return [
            ['NOT MATCHED'],
            ['bOoK', [0, 1, 2], 4], // by book title ALL
            ['bOok2', [1]], // by book title SINGLE
        ];
    }
}
