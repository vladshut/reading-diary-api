<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ReportPublished;
use App\Listeners\CreateReportPublishedFeed;
use App\Events\ReportUnpublished;
use App\Events\FeedCreated;
use App\Listeners\DeleteReportPublishedFeed;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ReportPublished::class => [
            CreateReportPublishedFeed::class
        ],
        ReportUnpublished::class => [
            DeleteReportPublishedFeed::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
