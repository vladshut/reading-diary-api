<?php

/** @var Factory $factory */

use App\Models\Feed;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Author;

$factory->define(Feed::class, static function (Faker $faker) {
    return [
        'author_id' => factory(User::class)->create()->id,
        'title' => $faker->sentence(4),
        'date' => new Carbon\Carbon(),
        'body' => $faker->sentence(20),
        'image' => $faker->url,
        'type' => $faker->randomElement([Feed::TYPE_REPORT_PUBLISHED]),
        'target_id' => factory(User::class)->create()->id,
        'data' => [],
        'author_name' => $faker->name,
        'author_image' => $faker->url,
    ];
});
