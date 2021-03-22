<?php
declare(strict_types=1);


namespace App\Models;

use App\Events\FeedCreated;
use App\Utils\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
}
