<?php

namespace Tests\Feature;

use App\Book;
use App\Author;
use App\UserBook;
use Tests\DataStructures;
use Tests\TestCase;

class BookTest extends TestCase
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

        $this->assertStructure($data, ['data' =>  ['*' => DataStructures::BOOK]]);
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

        $this->assertStructure($data, ['data' =>  ['*' => DataStructures::USER_BOOK]]);
        $this->assertCount($booksCount, $data['data']);
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

        $criteria = ['book_user_id' => $userBookId, 'parent_id' => null, 'name' => 'Content', 'order' => 1];
        $this->assertDatabaseHas('book_sections',$criteria);
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

        $criteria = ['book_user_id' => $userBookId, 'parent_id' => null, 'name' => 'Content', 'order' => 1];
        $this->assertDatabaseHas('book_sections',$criteria);
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

        $this->assertStructure($data, ['data' =>  ['*' => DataStructures::BOOK]]);
        $this->assertCount($expectedCount, $data['data']);
        $actualTitles = array_column($data['data'], 'title');
        $this->assertEquals($titlesOfResults, $actualTitles, json_encode($actualTitles));
    }

    public function searchDataProvider(): array
    {
        return [
            ['joHn',  ['Smith & Johnathan', 'Johnny Horror']],
            ['9781',  ['Face Down', 'Alice Bob']],
            ['97814', ['Alice Bob']],
            ['28946', ['Alice Bob']],
            ['2717',  ['Smith & Johnathan']],
        ];
    }
}
