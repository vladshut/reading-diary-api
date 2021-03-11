<?php
declare(strict_types=1);


namespace App\Services;


use App\Models\Book;
use App\Models\Feed;
use App\Models\ReportItem;
use App\Models\User;
use App\Models\UserBook;
use App\Utils\Assert;

class FeedFactory
{
    public function reportPublished(UserBook $userBook): Feed
    {
        Assert::true($userBook->isReportPublished());

        /** @var User $user */
        $user = $userBook->user()->firstOrFail();

        /** @var Book $book */
        $book = $userBook->book()->firstOrFail();

        $resume = $userBook->getRootSection()->reportItems()->where('type', ReportItem::TYPE_RESUME)->first();
        $resume = $resume ? $resume->resume : null;
        $rating = $userBook->getRootSection()->reportItems()->where('type', ReportItem::TYPE_RATING)->first();
        $rating = $rating ? $rating->rating : null;

        $feed = new Feed();
        $feed->author_id = $userBook->user_id;
        $feed->author_name = $user->name;
        $feed->author_image = $user->avatar ?? $user->avatar_original;
        $feed->title = $book->title;
        $feed->date = $userBook->report_published_at;
        $feed->body = $resume . "<br><br><b>{$rating}</b>";
        $feed->image = $book->getCoverUrl();
        $feed->type = Feed::TYPE_REPORT_PUBLISHED;
        $feed->target_id = null;
        $feed->data = [
            'user_book_id' => $userBook->id,
            'resume' => $resume,
            'rating' => $rating,
            'book_description' => $book->description,
        ];

        return $feed;
    }
}
