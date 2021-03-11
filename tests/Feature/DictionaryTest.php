<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
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
