<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $genres = [
            'Art',
            'Biography',
            'Business',
            'Chick Lit',
            'Children\'s',
            'Christian',
            'Classics',
            'Comics',
            'Contemporary',
            'Cookbooks',
            'Crime',
            'Ebooks',
            'Fantasy',
            'Fiction',
            'Gay and Lesbian',
            'Graphic Novels',
            'Historical Fiction',
            'History',
            'Horror',
            'Humor and Comedy',
            'Manga',
            'Memoir',
            'Music',
            'Mystery',
            'Nonfiction',
            'Paranormal',
            'Philosophy',
            'Poetry',
            'Psychology',
            'Religion',
            'Romance',
            'Science',
            'Science Fiction',
            'Self Help',
            'Suspense',
            'Spirituality',
            'Sports',
            'Thriller',
            'Travel',
            'Young Adult',
        ];

        foreach ($genres as $genre) {
            DB::table('genres')->insert([
                'name' => $genre,
                'alias' => str_replace(' ', '_', preg_replace('/[^a-z0-9 ]/', '', strtolower($genre))),
            ]);
        }

    }
}
