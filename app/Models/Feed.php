<?php
declare(strict_types=1);


namespace App\Models;

use App\Builders\FeedBuilder;
use App\Events\FeedCreated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Feed
 * @package App\Models
 * @property string $id
 * @property string $author_id
 * @property string $title
 * @property Carbon $date
 * @property string $body
 * @property string $image
 * @property string $type
 * @property string|null $target_id
 * @property array $data
 * @property string author_name
 * @property string author_image
 */
class Feed extends Model
{
    protected $fillable = ['type', 'data'];

    protected $casts = ['data' => 'array'];

    protected $dates = ['date'];

    public const TYPE_REPORT_PUBLISHED = 'report_published';

    protected $dispatchesEvents = [
        'created' => FeedCreated::class,
    ];

    public static function query() : FeedBuilder
    {
        /** @var FeedBuilder $builder */
        $builder = parent::query();

        return $builder;
    }

    public function newEloquentBuilder($query): FeedBuilder
    {
        return new FeedBuilder($query);
    }

    protected static function boot()
    {
        parent::boot();

        // Order by name ASC
        static::addGlobalScope('order', static function (Builder $builder) {
            $builder->orderBy('date', 'desc');
        });
    }

    public function favoriteForUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'feed_user',
            'feed_id',
            'user_id')
            ->wherePivot('is_favorite', true);
    }

    public function favoriteForUser($userId): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'feed_user',
            'feed_id',
            'user_id')
            ->wherePivot('user_id', $userId);
    }
}
