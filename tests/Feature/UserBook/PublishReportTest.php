<?php

namespace Tests\Feature\UserBook;

use App\Models\Book;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PublishReportTest extends TestCase
{
    use UserBookPublishAssertTrait;

    public function testPublishEndpoint(): void
    {
        Event::fake();

        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);

        self::assertUserBookReportNotPublished($userBook);

        $this->jsonApiPost("books/my/{$userBook->id}/publish-report");

        $userBook->refresh();

        self::assertUserBookReportPublishedAndDispatched($userBook);
    }

    public function testUserCantPublishNotOwnReport(): void
    {
        Event::fake();

        $this->login();

        $user = $this->createUser();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);

        self::assertUserBookReportNotPublished($userBook);

        $response = $this->json('POST',"api/books/my/{$userBook->id}/publish-report");
        $response->assertStatus(403);

        $userBook->refresh();

        self::assertUserBookReportNotPublished($userBook);
    }
}
