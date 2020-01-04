<?php

/** @var Factory $factory */

use App\BookSection;
use App\User;
use App\Book;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(BookSection::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(),
        'order' => $faker->unique()->numberBetween(1, 9999),
        'book_user_id' => factory(User::class)->create()->addBook(factory(Book::class)->create())->id,
    ];
});
