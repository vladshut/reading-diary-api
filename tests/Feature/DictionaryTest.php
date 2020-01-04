<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\Genre;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    protected $dictionaryStructure = [
        'alias',
        'name',
    ];

    public function testIndex(): void
    {
        factory(Genre::class, 5)->create();

        $data = $this->jsonApi('GET', 'dictionary');

        $this->assertStructure($data, ['genre' => ['*' => $this->dictionaryStructure]]);
        $this->assertCount(45, $data['genre']);
    }
}
