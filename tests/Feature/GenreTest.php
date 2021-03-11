<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Tests\TestCase;

class GenreTest extends TestCase
{
    protected $genreStructure = [
        'alias',
        'name',
    ];

    public function testIndex(): void
    {
        $this->login();

        factory(Genre::class, 5)->create();

        $data = $this->jsonApi('GET', 'dictionary');

        $this->assertStructure($data, ['genre' => ['*' => $this->genreStructure]]);
        $this->assertCount(45, $data['genre']);
    }
}
