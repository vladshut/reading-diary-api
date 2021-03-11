<?php

namespace Tests\Feature\UserBook;

use App\Models\Book;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnpublishReportTest extends TestCase
{
    use UserBookPublishAssertTrait;

    public function testUnpublishEndpoint(): void
    {
        Event::fake();

        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $userBook->publishReport();

        self::assertUserBookReportPublished($userBook);

        $this->jsonApiPost("books/my/{$userBook->id}/unpublish-report");

        $userBook->refresh();

        self::assertUserBookReportUnpublishedAndDispatched($userBook);
    }

    public function testUserCantUnpublishNotOwnReport(): void
    {
        Event::fake();

        $this->login();

        $user = $this->createUser();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $userBook->publishReport();

        self::assertUserBookReportPublished($userBook);

        $response = $this->json('POST',"api/books/my/{$userBook->id}/unpublish-report");
        $response->assertStatus(403);

        $userBook->refresh();

        self::assertUserBookReportNotUnpublished($userBook);
    }
}
