<?php

namespace App\Models;

use App\Events\ReportPublished;
use App\Events\ReportUnpublished;
use App\Models\Book;
use App\Models\BookSection;
use App\Models\User;
use DateTime;
use DateTimeImmutable;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @property string status
 * @property DateTime start_reading_dt
 * @property DateTime end_reading_dt
 * @property mixed id
 * @property UuidInterface report_public_key
 * @property bool is_report_published
 * @property DateTime $report_published_at
 * @property string user_id
 */
class UserBook extends Model
{
    public const STATUS_NOT_READ = 'not_read';
    public const STATUS_READING = 'reading';
    public const STATUS_READ = 'read';
    public const STATUS_CANCELED = 'canceled';

    protected $table = 'book_user';

    protected $attributes = [
        'status' => self::STATUS_NOT_READ,
    ];

    protected $dispatchesEvents = [
        'report_published' => ReportPublished::class,
        'report_unpublished' => ReportUnpublished::class,
    ];

    protected $casts = [
        'is_report_published' => 'boolean',
    ];

    protected $dates = ['report_published_at'];

    public static function getStatuses(): array
    {
        return [self::STATUS_CANCELED, self::STATUS_NOT_READ, self::STATUS_READ, self::STATUS_READING];
    }

    public static function create(User $user, Book $book): self
    {
        $userBook = new self();

        $userBook->user()->associate($user);
        $userBook->book()->associate($book);

        return $userBook;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function startReading(): void
    {
        if ($this->status !== self::STATUS_NOT_READ) {
            return;
        }

        $this->status = self::STATUS_READING;
        $this->start_reading_dt = new DateTime();

        $this->save();
    }

    public function finishReading(): void
    {
        if ($this->status !== self::STATUS_READING) {
            return;
        }

        $this->status = self::STATUS_READ;
        $this->end_reading_dt = new DateTime();

        $this->save();
    }

    public function resumeReading(): void
    {
        if ($this->status !== self::STATUS_READ) {
            return;
        }

        $this->status = self::STATUS_READING;
        $this->end_reading_dt = null;

        $this->save();
    }

    public function sections(): HasMany
    {
        return $this->hasMany(BookSection::class, 'book_user_id');
    }

    public function addRootSection(): void
    {
        if ($this->hasRootSection()) {
            return;
        }

        $bookSection = new BookSection();
        $bookSection->name = $this->book()->get()->first()->title;
        $bookSection->order = 1;
        $bookSection->book_user_id = $this->id;
        $bookSection->parent_id = null;

        $this->sections()->get()->add($bookSection);
        $bookSection->save();
    }

    public function hasRootSection(): bool
    {
        return $this->sections()->where(['parent_id' => null])->exists();
    }

    public function getRootSection(): BookSection
    {
        /** @var BookSection $rootSection */
        $rootSection = $this->sections()->where(['parent_id' => null])->first();

        return $rootSection;
    }

    public function addSection(string $name, int $order, int $parentId = null): BookSection
    {

        $bookSection = new BookSection(compact('name', 'order'));
        $bookSection->book_user_id = $this->id;
        $bookSection->parent_id = $parentId ?: $this->getRootSection()->id;

        $this->sections()->get()->add($bookSection);
        $bookSection->save();

        return $bookSection;
    }

    /**
     * @throws Exception
     */
    public function makeReportAccessibleViaPublicLink(): void
    {
        if (!$this->report_public_key) {
            $this->report_public_key = Uuid::uuid4();
            $this->save();
        }
    }

    /**
     * @throws Exception
     */
    public function makeReportNotAccessibleViaPublicLink(): void
    {
        if ($this->report_public_key) {
            $this->report_public_key = null;
            $this->save();
        }
    }

    /**
     * @throws Exception
     */
    public function regenerateReportPublicKey(): void
    {
        $this->report_public_key = Uuid::uuid4();
        $this->save();
    }

    public function publishReport(): void
    {
        if (!$this->isReportPublished()) {
            $this->is_report_published = true;
            $this->report_published_at = new DateTimeImmutable();
            $this->save();
            $this->fireModelEvent('report_published');
        }
    }

    public function unpublishReport(): void
    {
        if ($this->isReportPublished()) {
            $this->is_report_published = false;
            $this->report_published_at = null;
            $this->save();
            $this->fireModelEvent('report_unpublished');
        }
    }

    public function isReportPublished(): bool
    {
        return (bool)$this->is_report_published;
    }
}
