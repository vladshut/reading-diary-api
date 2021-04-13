<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Author;
use App\Models\UserBook;
use Carbon\Carbon;
use DateTimeImmutable;
use JsonException;
use Tests\DataStructures;
use Tests\TestCase;

class UserBookTest extends TestCase
{
    public function testShow(): void
    {
        $user = $this->login();
        /** @var Book $book */
        $book = factory(Book::class)->create();

        $data = $this->jsonApi('GET', "books/{$book->id}");

        $this->assertStructure($data, DataStructures::BOOK);
    }

    public function testIndex(): void
    {
        $booksCount = 5;
        $this->login();
        factory(Book::class, $booksCount)->create();

        $data = $this->jsonApi('GET', 'books');

        $this->assertStructure($data, ['data' => ['*' => DataStructures::BOOK]]);
        $this->assertCount($booksCount, $data['data']);
    }

    public function testMyBooks(): void
    {
        $booksCount = 5;
        $user = $this->login();
        $books = factory(Book::class, $booksCount)->create();

        foreach ($books as $book) {
            $user->addBook($book);
        }

        $data = $this->jsonApi('GET', 'books/my');

        $this->assertStructure($data, ['data' => ['*' => DataStructures::USER_BOOK]]);
        $this->assertCount($booksCount, $data['data']);
    }

    public function testMyBooksQuery(): void
    {
        $searchQuery = 'tItLe';

        $booksCount = 5;
        $user = $this->login();
        $books = factory(Book::class, $booksCount)->create();

        foreach ($books as $book) {
            $user->addBook($book);
        }

        /** @var Book $expectedBook */
        $expectedBook = factory(Book::class)->create(['title' => 'Super title!!!']);
        $expectedUserBook = $user->addBook($expectedBook);

        $data = $this->jsonApi('GET', "books/my?query={$searchQuery}");

        $this->assertStructure($data, ['data' => ['*' => DataStructures::USER_BOOK]]);
        self::assertCount(1, $data['data']);
        self::assertEquals($expectedUserBook->id, $data['data']['0']['id']);
        self::assertEquals($expectedBook->title, $data['data']['0']['book']['title']);
        self::assertEquals($expectedBook->id, $data['data']['0']['book']['id']);
    }

    public function testMyBooksStatus(): void
    {
        $filterStatus = UserBook::STATUS_READING;

        $booksCount = 5;
        $user = $this->login();
        $books = factory(Book::class, $booksCount)->create();

        foreach ($books as $book) {
            $user->addBook($book);
        }

        /** @var Book $expectedBook */
        $expectedBook = factory(Book::class)->create();
        $expectedUserBook = $user->addBook($expectedBook);
        $expectedUserBook->startReading();

        $data = $this->jsonApi('GET', "books/my?statuses[0]={$filterStatus}");

        $this->assertStructure($data, ['data' => ['*' => DataStructures::USER_BOOK]]);
        self::assertCount(1, $data['data']);
        self::assertEquals($expectedUserBook->id, $data['data']['0']['id']);
        self::assertEquals($expectedBook->title, $data['data']['0']['book']['title']);
        self::assertEquals($expectedBook->id, $data['data']['0']['book']['id']);
    }

    public function testMyBooksSort(): void
    {
        $orderBy = 'created_at';
        $orderDir = 'asc';

        $booksCount = 5;
        $user = $this->login();
        $books = factory(Book::class, $booksCount)->create();

        foreach ($books as $book) {
            $user->addBook($book);
        }

        /** @var Book $expectedBook */
        $expectedBook = factory(Book::class)->create();
        $expectedUserBook = $user->addBook($expectedBook);
        $expectedUserBook->created_at = Carbon::now()->subDays(30);
        $expectedUserBook->save();

        $data = $this->jsonApi('GET', "books/my?order_by={$orderBy}&order_dir={$orderDir}");

        $this->assertStructure($data, ['data' => ['*' => DataStructures::USER_BOOK]]);
        self::assertCount($booksCount + 1, $data['data']);
        self::assertEquals($expectedUserBook->id, $data['data']['0']['id']);
        self::assertEquals($expectedBook->title, $data['data']['0']['book']['title']);
        self::assertEquals($expectedBook->id, $data['data']['0']['book']['id']);
    }

    public function testStore(): void
    {
        $this->login();
        $payload = factory(Book::class)->raw();

        $data = $this->jsonApi('POST', 'books', $payload);
        $this->assertStructure($data, DataStructures::BOOK);

        $this->assertDatabaseHas('books', $payload);
    }

    public function testAddNew(): void
    {
        $user = $this->login();
        $payload = factory(Book::class)->raw();

        $data = $this->jsonApi('POST', 'books/add-new', $payload);
        $this->assertStructure($data, DataStructures::USER_BOOK);

        $this->assertDatabaseHas('books', $payload);
        $criteria = ['user_id' => $user->id, 'book_id' => $data['id']];
        $this->assertDatabaseHas('book_user', $criteria);
        $userBookId = UserBook::query()->where($criteria)->get(['id'])->first()->id;

        $criteria = ['book_user_id' => $userBookId, 'parent_id' => null, 'name' => $payload['title'], 'order' => 1];
        $this->assertDatabaseHas('book_sections', $criteria);
    }

    public function testAddExisting(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();

        $payload = [
            'book_id' => $book->id
        ];

        $data = $this->jsonApi('POST', 'books/add-existing', $payload);
        $this->assertStructure($data, DataStructures::USER_BOOK);

        $criteria = ['user_id' => $user->id, 'book_id' => $book->id];
        $this->assertDatabaseHas('book_user', $criteria);
        $userBookId = UserBook::query()->where($criteria)->get(['id'])->first()->id;

        $criteria = ['book_user_id' => $userBookId, 'parent_id' => null, 'name' => $book->title, 'order' => 1];
        $this->assertDatabaseHas('book_sections', $criteria);
    }


    public function testStartReading(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);

        $data = $this->jsonApi('POST', "books/my/{$userBook->id}/start-reading", []);
        $this->assertStructure($data, DataStructures::USER_BOOK);

        $this->assertDatabaseHas('book_user', ['id' => $userBook->id, 'status' => UserBook::STATUS_READING]);
    }

    public function testFinishReading(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $userBook->startReading();

        $data = $this->jsonApi('POST', "books/my/{$userBook->id}/finish-reading", []);
        $this->assertStructure($data, DataStructures::USER_BOOK);

        $this->assertDatabaseHas('book_user', ['id' => $userBook->id, 'status' => UserBook::STATUS_READ]);
    }

    public function testResumeReading(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $userBook->startReading();
        $userBook->finishReading();

        $data = $this->jsonApi('POST', "books/my/{$userBook->id}/resume-reading", []);
        $this->assertStructure($data, DataStructures::USER_BOOK);

        $this->assertDatabaseHas('book_user', ['id' => $userBook->id, 'status' => UserBook::STATUS_READING, 'end_reading_dt' => null]);
    }

    /**
     * @dataProvider searchDataProvider
     * @param string $term
     * @param array $titlesOfResults
     * @throws JsonException
     */
    public function testSearch(string $term, array $titlesOfResults): void
    {
        $expectedCount = count($titlesOfResults);

        $this->login();

        $authorsData = [
            [
                'title' => 'Smith & Johnathan',
                'isbn10' => '1861972717',
                'isbn13' => null,
            ],
            [
                'title' => 'Johnny Horror',
                'isbn10' => '0198526636',
                'isbn13' => null,
            ],
            [
                'title' => 'Face Down',
                'isbn10' => null,
                'isbn13' => '9781861978769',
            ],
            [
                'title' => 'Alice Bob',
                'isbn10' => null,
                'isbn13' => '9781402894626',
            ],
        ];

        foreach ($authorsData as $authorData) {
            factory(Book::class)->create($authorData);
        }


        $data = $this->jsonApi('GET', "books/search?term={$term}");

        $this->assertStructure($data, ['data' => ['*' => DataStructures::BOOK]]);
        $this->assertCount($expectedCount, $data['data']);
        $actualTitles = array_column($data['data'], 'title');
        $this->assertEquals($titlesOfResults, $actualTitles, json_encode($actualTitles));
    }

    public function searchDataProvider(): array
    {
        return [
            ['joHn', ['Smith & Johnathan', 'Johnny Horror']],
            ['9781', ['Face Down', 'Alice Bob']],
            ['97814', ['Alice Bob']],
            ['28946', ['Alice Bob']],
            ['2717', ['Smith & Johnathan']],
        ];
    }

    public function testTopLanguages(): void
    {
        $user = $this->createUser();

        $booksLanguages = ['ukr' => 7, 'eng' => 6, 'tur' => 5, 'aze' => 4, 'arc' => 3, 'rus' => 1];

        foreach ($booksLanguages as $booksLanguage => $booksCount) {
            for ($i = 0; $i < $booksCount; $i++) {
                $book = factory(Book::class)->create(['lang' => $booksLanguage]);
                $user->addBook($book);
            }
        }

        // add books to another user
        $otherUser = $this->login();
        $book = factory(Book::class)->create(['lang' => 'ukr']);
        $otherUser->addBook($book);

        $data = $this->jsonApi('GET', "users/{$user->id}/books/top-languages");

        $expectedData = [];

        foreach ($booksLanguages as $lang => $count) {
            $expectedData[] = ['lang' => $lang, 'count' => $count];
        }

        self::assertEquals($expectedData, $data);
    }

    public function testTopAuthors(): void
    {
        $this->withoutExceptionHandling();
        $user = $this->createUser();

        $booksAuthors = [
            'Author One' => 7,
            'Author Two' => 6,
            'Author Three' => 5,
            'Author Four' => 4,
            'Author Five' => 3,
            'Author Six' => 1
        ];

        foreach ($booksAuthors as $authorName => $authorCount) {
            $author = factory(Author::class)->create(['name' => $authorName]);

            for ($i = 0; $i < $authorCount; $i++) {
                $book = factory(Book::class)->create(['author_id' => $author->id]);
                $user->addBook($book);
            }
        }

        // add books to another user
        $otherUser = $this->login();
        $author = factory(Author::class)->create(['name' => 'Author One']);
        $book = factory(Book::class)->create(['author_id' => $author->id]);
        $otherUser->addBook($book);

        $data = $this->jsonApi('GET', "users/{$user->id}/books/top-authors");

        $expectedData = [];

        foreach ($booksAuthors as $name => $count) {
            $expectedData[] = ['name' => $name, 'count' => $count];
        }

        self::assertEquals($expectedData, $data);
    }

    public function testTopStatuses(): void
    {
        $this->withoutExceptionHandling();
        $user = $this->createUser();

        $booksStatuses = [
            UserBook::STATUS_NOT_READ => 4,
            UserBook::STATUS_READING => 3,
            UserBook::STATUS_READ => 2,
            UserBook::STATUS_CANCELED => 1,
        ];

        foreach ($booksStatuses as $bookStatus => $statusCount) {
            for ($i = 0; $i < $statusCount; $i++) {
                $book = factory(Book::class)->create();
                $userBook = $user->addBook($book);
                $userBook->status = $bookStatus;
                $userBook->save();
            }
        }

        // add books to another user
        $otherUser = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $otherUser->addBook($book);
        $userBook->status = $bookStatus;

        $data = $this->jsonApi('GET', "users/{$user->id}/books/top-statuses");

        $expectedData = [];

        foreach ($booksStatuses as $status => $count) {
            $expectedData[] = ['status' => $status, 'count' => $count];
        }

        self::assertEquals($expectedData, $data);
    }
}
