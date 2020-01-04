<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\BookSection;
use App\Http\Resources\ReportItemResource;
use App\ReportItem;
use App\UserBook;
use Illuminate\Http\Request;
use Tests\DataStructures;
use Tests\TestCase;

class ReportItemTest extends TestCase
{
    public function testIndex(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $bookSection = factory(BookSection::class)->create(['book_user_id' => $userBook->id]);

        $this->createReportItems($userBook, $bookSection);

        $data = $this->jsonApi('GET', "books/my/sections/{$bookSection->id}/report-items");

        $this->assertCount(count(ReportItem::TYPES), $data['data']);
    }

    public function createReportItems(UserBook $userBook, BookSection $bookSection): array
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

    /**
     * @dataProvider storeDataProvider
     * @param array $payload
     */
    public function testStore(array $payload): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $bookSection = factory(BookSection::class)->create(['book_user_id' => $userBook->id]);

        $responseData = $this->jsonApi('POST', "books/my/sections/{$bookSection->id}/report-items", $payload);

        $structure = array_keys($payload) + ['id', 'book_section_id', 'book_user_id'];

        $this->assertStructure($responseData, $structure);

        $this->assertDatabaseHas('report_items', $responseData, 'mongodb');
    }

    public function storeDataProvider(): array
    {
        $data = [];

        foreach (ReportItem::TYPES as $type) {
            $additionalFields = ReportItem::fieldsTypeMap($type, false);
            $data[] = [$this->createReportItemPayload($type, $additionalFields)];
        }

        return $data;
    }

    public function createReportItemPayload(string $type, array $additionalFields = []): array
    {
        $payload = [
            'type' => $type,
        ];

        foreach ($additionalFields as $field) {
            $payload[$field] = 'Some value';
        }

        return $payload;
    }


    public function testUpdate(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $bookSection = factory(BookSection::class)->create(['book_user_id' => $userBook->id]);

        $reportItems = $this->createReportItems($userBook, $bookSection);

        foreach ($reportItems as $reportItem) {
            $payload = [$reportItem->type => 'updated'];
            $data = $this->jsonApi('PUT', "books/my/sections/report-items/{$reportItem->id}", $payload);

            $this->assertStructure($data, array_keys((new ReportItemResource($reportItem))->toArray(new Request())));

            $criteria = $payload + ['id' => $reportItem->id];
            $this->assertDatabaseHas('report_items', $criteria, 'mongodb');
        }
    }

    public function testDelete(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        $bookSection = factory(BookSection::class)->create(['book_user_id' => $userBook->id]);

        $reportItems = $this->createReportItems($userBook, $bookSection);

        foreach ($reportItems as $reportItem) {
            $this->jsonApi('DELETE', "books/my/sections/report-items/{$reportItem->id}");

            $criteria = ['id' => $reportItem->id];
            $this->assertDatabaseMissing('report_items', $criteria, 'mongodb');
        }
    }
}
