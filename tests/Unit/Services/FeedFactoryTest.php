<?php
declare(strict_types=1);


namespace Tests\Unit\Models;


use App\Events\FeedCreated;
use App\Models\Book;
use App\Models\Feed;
use App\Models\ReportItem;
use App\Services\FeedFactory;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class FeedFactoryTest extends TestCase
{
    public function testReportPublished(): void
    {
        Event::fake();

        $factory = new FeedFactory();

        $user = $this->createUser();
        /** @var Book $book */
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $userBook->addRootSection();
        $section = $userBook->getRootSection();
        $userBook->publishReport();

        $resume = new ReportItem(
            [
                'type' => ReportItem::TYPE_RESUME,
                'book_section_id' => $section->id,
                'book_user_id' => $userBook->id,
                'resume' => $this->faker->text(20),
                'visibility' => true,
                'order' => 1,
                'is_favorite' => true,
            ]
        );
        $resume->save();

        $rating = new ReportItem(
            [
                'type' => ReportItem::TYPE_RATING,
                'book_section_id' => $section->id,
                'book_user_id' => $userBook->id,
                'rating' => $this->faker->numberBetween(1, 5),
                'visibility' => true,
                'order' => 2,
                'is_favorite' => true,
            ]
        );
        $rating->save();

        $expectedData = [
            'author_id' => $user->id,
            'author_name' => $user->name,
            'author_image' => $user->avatar,
            'title' => $book->title,
            'date' => $userBook->report_published_at,
            'body' => $resume->resume . "<br><br><b>{$rating->rating}</b>",
            'image' => $book->getCoverUrl(),
            'type' => Feed::TYPE_REPORT_PUBLISHED,
            'target_id' => null,
            'data' => [
                'user_book_id' => $userBook->id,
                'resume' => $resume->resume,
                'rating' => $rating->rating,
                'book_description' => $book->description,
            ],
        ];


        $feed = $factory->reportPublished($userBook);

        foreach ($expectedData as $expectedKey => $expectedValue) {
            self::assertEquals($expectedValue, $feed->$expectedKey);
        }
    }
}
