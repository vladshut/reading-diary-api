<?php

namespace App\Listeners;

use App\Events\ReportUnpublished;
use App\Models\Feed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteReportPublishedFeed
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ReportUnpublished  $event
     * @return void
     */
    public function handle(ReportUnpublished $event)
    {
        Feed::query()
            ->where('type', Feed::TYPE_REPORT_PUBLISHED)
            ->where('data->user_book_id', $event->getUserBook()->id)
            ->delete();
    }
}
