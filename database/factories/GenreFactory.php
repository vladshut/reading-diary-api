<?php

/** @var Factory $factory */

use App\Models\Genre;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Genre::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'alias' => $faker->unique()->word,
    ];
});
