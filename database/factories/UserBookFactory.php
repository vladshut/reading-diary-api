<?php

/** @var Factory $factory */

use App\User;
use App\UserBook;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(UserBook::class, static function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create()->id,
        'book_id' => factory(Book::class)->create()->id,
        'status' => UserBook::STATUS_NOT_READ,
        'report_public_key' => $faker->uuid,
    ];
});
