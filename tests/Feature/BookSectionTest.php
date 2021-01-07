<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\BookSection;
use App\ReportItem;
use App\UserBook;
use Tests\DataStructures;
use Tests\TestCase;

class BookSectionTest extends TestCase
{
    public function testIndex(): void
    {
//        dump(env('DB_CONNECTION'));
//        dump(env('DB_DATABASE'));
//        dump(config('database.default'));
        $sectionsCount = 5;

        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        factory(BookSection::class, $sectionsCount)->create(['book_user_id' => $userBook->id]);

        $data = $this->jsonApi('GET', "books/my/{$userBook->id}/sections");

        $this->assertStructure($data, ['*' => DataStructures::SECTION]);
        $expectedSectionsCount = $sectionsCount + 1; // created sections + root section
        $this->assertCount($expectedSectionsCount, $data);
    }

    public function testStore(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);

        $payload = factory(BookSection::class)->raw();
        unset($payload['book_user_id']);
        unset($payload['order']);

        $responseData = $this->jsonApi('POST', "books/my/{$userBook->id}/sections", $payload);

        $this->assertStructure($responseData, DataStructures::SECTION);

        $criteria = $payload + ['book_user_id' => $userBook->id];
        $this->assertDatabaseHas('book_sections', $criteria);

        $payload = factory(BookSection::class)->raw();
        unset($payload['book_user_id']);
        unset($payload['order']);
        $payload['parent_id'] = $responseData['id'];

        $responseData = $this->jsonApi('POST', "books/my/{$userBook->id}/sections", $payload);

        $this->assertStructure($responseData, DataStructures::SECTION);

        $criteria = $payload + ['book_user_id' => $userBook->id];
        $this->assertDatabaseHas('book_sections', $criteria);

    }

    public function testUpdate(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $parentSection = $userBook->addSection('Chapter 1', 1, null);
        $section = $userBook->addSection('Chapter 1.1', 1, null);

        $payload = factory(BookSection::class)->raw();
        $payload['parent_id'] = $parentSection->id;
        unset($payload['book_user_id']);

        $responseData = $this->jsonApi('PUT', "books/my/sections/{$section->id}", $payload);

        $this->assertStructure($responseData, DataStructures::SECTION);

        $criteria = $payload + ['book_user_id' => $userBook->id];

        $this->assertDatabaseHas('book_sections', $criteria);
    }

    public function testDelete(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $section = $userBook->addSection('Chapter 1', 1, null);
        $subSection = $userBook->addSection('Chapter 1.1', 1, $section->id);

        $responseData = $this->jsonApi('DELETE', "books/my/sections/{$section->id}");

        $this->assertStructure($responseData, []);

        $this->assertDatabaseMissing('book_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('book_sections', ['id' => $subSection->id]);
    }

    private function createReportItems(UserBook $userBook, BookSection $bookSection): array
    {
        $reportItems = [];

        foreach (ReportItem::TYPES as $type) {
            $additionalFields = ReportItem::fieldsTypeMap($type, false);
            $data = $this->createReportItemPayload($type, $additionalFields);
            $data['book_section_id'] = $bookSection->id;
            $data['book_user_id'] = $userBook->id;

            $reportItem = new ReportItem($data);
            $reportItem->save();

            $reportItems[] = $reportItem;
        }

        return $reportItems;
    }

    private function createReportItemPayload(string $type, array $additionalFields = []): array
    {
        $payload = [
            'type' => $type,
        ];

        foreach ($additionalFields as $field) {
            $payload[$field] = 'Some value';
        }

        return $payload;
    }
}
