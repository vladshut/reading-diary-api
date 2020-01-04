<?php
declare(strict_types=1);


namespace Tests;


final class DataStructures
{
    public const USER = [
        'id',
        'name',
        'email',
    ];

    public const AUTHOR = [
        'id',
        'name',
        'personal_name',
        'title',
        'bio',
        'location',
        'birth_date',
        'death_date',
        'wikipedia_url',
    ];

    public const BOOK = [
        'id',
        'title',
        'year',
        'pages',
        'isbn10',
        'isbn13',
        'lang',
        'description',
        'author' => self::AUTHOR,
    ];

    public const USER_BOOK = [
        'id',
        'status',
        'start_reading_dt',
        'end_reading_dt',
        'created_at',
        'book' => self::BOOK,
        'user' => self::USER,
    ];

    public const SECTION = [
        'id',
        'name',
        'order',
        'parent_id',
    ];
}
