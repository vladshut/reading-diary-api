<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Author;

$factory->define(\App\Models\Book::class, static function (Faker $faker) {
    return [
        'title' => $faker->sentence(),
        'year' => $faker->year(date('Y')),
        'pages' => $faker->randomNumber(3),
        'isbn10' => $faker->isbn10,
        'isbn13' => $faker->isbn13,
        'lang' => $faker->languageCode,
        'description' => $faker->text(),
        'author_id' => factory(Author::class)->create()->id,
    ];
});
