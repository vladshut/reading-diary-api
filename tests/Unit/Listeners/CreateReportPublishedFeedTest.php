<?php
declare(strict_types=1);


namespace Tests\Unit\Listeners;


use App\Events\FeedCreated;
use App\Events\ReportPublished;
use App\Listeners\CreateReportPublishedFeed;
use App\Models\Feed;
use App\Models\UserBook;
use App\Services\FeedFactory;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class CreateReportPublishedFeedTest extends TestCase
{
    public function testHandle(): void
    {
        Event::fake();

        $feed = new Feed();
        $feed->type = Feed::TYPE_REPORT_PUBLISHED;

        $userBookMock = $this->createMock(UserBook::class);
        $factoryMock = $this->createMock(FeedFactory::class);
        $factoryMock->expects(self::once())->method('reportPublished')->with($userBookMock)->willReturn($feed);
        $listener = new CreateReportPublishedFeed($factoryMock);

        $event = new ReportPublished($userBookMock);

        $listener->handle($event);

        Event::assertDispatched(FeedCreated::class, fn(FeedCreated $event) => $event->getFeed() === $feed);

        $this->assertDatabaseHas('feeds', ['id' => $feed->id]);
    }
}
