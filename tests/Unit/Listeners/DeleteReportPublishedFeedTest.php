<?php
declare(strict_types=1);


namespace Tests\Unit\Listeners;

use App\Events\ReportUnpublished;
use App\Listeners\DeleteReportPublishedFeed;
use App\Models\Feed;
use App\Models\UserBook;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class DeleteReportPublishedFeedTest extends TestCase
{
    public function testHandle(): void
    {
        Event::fake();

        $userBookId = (string)uuid4();

        /** @var Feed $feed */
        $feed = Feed::query()->create(['type' => Feed::TYPE_REPORT_PUBLISHED, 'data' => ['user_book_id' => $userBookId]]);
        $this->assertDatabaseHas('feeds', ['id' => $feed->id]);

        $userBookMock = $this->mock(UserBook::class);
        $userBookMock->shouldReceive('getAttribute')->with('id')->andReturn($userBookId);

        $listener = new DeleteReportPublishedFeed();

        $event = new ReportUnpublished($userBookMock);

        $listener->handle($event);

        $this->assertDatabaseMissing('feeds', ['id' => $feed->id]);
    }
}
