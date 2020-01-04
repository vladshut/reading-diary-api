<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string status
 * @property DateTime start_reading_dt
 * @property mixed id
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
        $bookSection->name = 'Book';
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
}
