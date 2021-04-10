<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 * @method static paginate()
 * @property int id
 */
class Author extends Model
{
    protected $fillable = [
        'name',
        'personal_name',
        'title',
        'bio',
        'location',
        'birth_date',
        'death_date',
        'wikipedia_url',
    ];
}
