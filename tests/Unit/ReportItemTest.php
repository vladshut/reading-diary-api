<?php

namespace Tests\Unit;

use App\Book;
use App\BookSection;
use App\Filepond;
use App\ReportItem;
use Spatie\MediaLibrary\Models\Media;
use Tests\TestCase;

class ReportItemTest extends TestCase
{
    public function testUpdateFigureItem(): void
    {
        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        /** @var BookSection $bookSection */
        $bookSection = factory(BookSection::class)->create(['book_user_id' => $userBook->id]);

        $type = ReportItem::TYPE_FIGURE;
        $additionalFields = ReportItem::fieldsTypeMap($type, false);
        $data = $this->createReportItemPayload($type, $additionalFields);
        $data['book_section_id'] = $bookSection->id;
        $data['book_user_id'] = $userBook->id;

        $reportItem = new ReportItem($data);
        $reportItem->save();

        $order = random_int(1, 100);
        $rawValue = uuid4()->toString();
        $expectedValue = __DIR__ . '/test.jpg';
        $isFavourite = $this->faker->boolean;

        $filePath = __DIR__ . '/example.jpg';
        shell_exec("rm $expectedValue 2>&1");
        shell_exec("cp $filePath $expectedValue 2>&1");

        $attributes = [
            $reportItem->type => $rawValue,
            'order' => $order,
            'is_favourite' => $isFavourite,
        ];

        $filepondMock = $this->mock(Filepond::class);
        $filepondMock->expects('findPathFromServerId')->times(2)
            ->andReturn($expectedValue, null)
        ;

        $this->app->bind(Filepond::class, static function () use ($filepondMock) {
            return $filepondMock;
        });

        $reportItem->update($attributes);

        $criteria = $attributes + ['id' => $reportItem->id];
        unset($criteria[$reportItem->type]);

        $this->assertDatabaseHas('report_items', $criteria, 'mongodb');

        /** @var ReportItem $reportItem */
        $reportItem = ReportItem::query()->findOrFail($reportItem->id);
        $mediaId = get_media_id_by_public_url($reportItem->figure);
        /** @var Media $media */
        $media = Media::query()->findOrFail($mediaId);

        self::assertFileExists($media->getPath());
    }

    public function testCreateFigureItem(): void
    {
        $rawValue = uuid4()->toString();
        $expectedValue = __DIR__ . '/test.jpg';

        $filePath = __DIR__ . '/example.jpg';
        shell_exec("rm $expectedValue 2>&1");
        shell_exec("cp $filePath $expectedValue 2>&1");

        $filepondMock = $this->mock(Filepond::class);
        $filepondMock->expects('findPathFromServerId')->times(2)
            ->andReturn($expectedValue, null)
        ;

        $this->app->bind(Filepond::class, static function () use ($filepondMock) {
            return $filepondMock;
        });


        $user = $this->login();
        $book = factory(Book::class)->create();
        $userBook = $user->addBook($book);
        /** @var BookSection $bookSection */
        $bookSection = factory(BookSection::class)->create(['book_user_id' => $userBook->id]);

        $type = ReportItem::TYPE_FIGURE;
        $additionalFields = ReportItem::fieldsTypeMap($type, false);
        $data = $this->createReportItemPayload($type, $additionalFields);
        $data['book_section_id'] = $bookSection->id;
        $data['book_user_id'] = $userBook->id;
        $data['figure'] = $rawValue;

        $reportItem = new ReportItem($data);
        $reportItem->save();

        $criteria = $data + ['id' => $reportItem->id];
        unset($criteria[$reportItem->type]);

        $this->assertDatabaseHas('report_items', $criteria, 'mongodb');

        /** @var ReportItem $reportItem */
        $reportItem = ReportItem::query()->findOrFail($reportItem->id);
        $mediaId = get_media_id_by_public_url($reportItem->figure);
        /** @var Media $media */
        $media = Media::query()->findOrFail($mediaId);

        self::assertFileExists($media->getPath());
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
}
