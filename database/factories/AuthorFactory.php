<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(\App\Models\Author::class, static function (Faker $faker) {
    $deathDate = $faker->date('Y-m-d', '2010-01-01');

    return [
        'name' => $faker->name(),
        'personal_name' => $faker->name(),
        'title' => $faker->name(),
        'bio' => $faker->text(),
        'location' => $faker->address,
        'birth_date' => $faker->date('Y-m-d', $deathDate),
        'death_date' => $deathDate,
        'wikipedia_url' => $faker->url,
    ];
});
