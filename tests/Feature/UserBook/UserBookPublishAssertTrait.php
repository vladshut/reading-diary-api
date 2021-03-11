<?php

namespace Tests\Feature\UserBook;

use App\Events\ReportPublished;
use App\Events\ReportUnpublished;
use App\Models\UserBook;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

trait UserBookPublishAssertTrait
{
    static protected function assertUserBookReportPublished(UserBook $userBook)
    {
        self::assertTrue($userBook->isReportPublished());
        self::assertInstanceOf(Carbon::class, $userBook->report_published_at);
    }

    static protected function assertUserBookReportPublishedAndDispatched(UserBook $userBook)
    {
        self::assertUserBookReportPublished($userBook);
        Event::assertDispatched(ReportPublished::class, fn(ReportPublished $event) => $event->getUserBook()->id === $userBook->id);
    }

    static protected function assertUserBookReportNotPublished(UserBook $userBook)
    {
        self::assertUserBookReportUnpublished($userBook);
        Event::assertNotDispatched(ReportPublished::class);
    }

    static protected function assertUserBookReportUnpublished(UserBook $userBook)
    {
        self::assertFalse($userBook->isReportPublished());
        self::assertNotInstanceOf(Carbon::class, $userBook->report_published_at);
    }

    static protected function assertUserBookReportUnpublishedAndDispatched(UserBook $userBook)
    {
        self::assertUserBookReportUnpublished($userBook);
        Event::assertDispatched(ReportUnpublished::class, fn(ReportUnpublished $event) => $event->getUserBook()->id === $userBook->id);
    }

    static protected function assertUserBookReportNotUnpublished(UserBook $userBook)
    {
        self::assertUserBookReportPublished($userBook);
        Event::assertNotDispatched(ReportUnpublished::class);
    }
}
