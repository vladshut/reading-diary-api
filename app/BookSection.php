<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * @property mixed book_user_id
 * @property mixed parent_id
 * @property mixed id
 * @property string name
 * @property int order
 */
class BookSection extends Model
{
    public $timestamps = false;

    use HybridRelations;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'order',
    ];

    public function userBook(): BelongsTo
    {
        return $this->belongsTo(UserBook::class, 'book_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function delete():void
    {
        /** @var self[] $children */
        $children = $this->children()->get();

        foreach ($children as $child) {
            $child->delete();
        }

        parent::delete();
    }

    public function reportItems(): HasMany
    {
        return $this->hasMany(ReportItem::class)->orderBy('order');
    }
}
