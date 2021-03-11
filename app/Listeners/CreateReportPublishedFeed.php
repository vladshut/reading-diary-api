<?php

namespace App\Listeners;

use App\Events\ReportPublished;
use App\Services\FeedFactory;

class CreateReportPublishedFeed
{
    private FeedFactory $feedFactory;

    /**
     * Create the event listener.
     *
     * @param FeedFactory $feedFactory
     */
    public function __construct(FeedFactory $feedFactory)
    {
        $this->feedFactory = $feedFactory;
    }

    /**
     * Handle the event.
     *
     * @param  ReportPublished  $event
     * @return void
     */
    public function handle(ReportPublished $event): void
    {
        $this->feedFactory->reportPublished($event->getUserBook())->save();
    }
}
