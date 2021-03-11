<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    protected $authorStructure = [
        'id',
        'name',
        'personal_name',
        'title',
        'bio',
        'location',
        'birth_date',
        'death_date',
        'wikipedia_url',
    ];

    public function testShow(): void
    {
        $this->login();
        $author = factory(Author::class)->create();

        $data = $this->jsonApi('GET', "authors/{$author->id}");

        $this->assertStructure($data, $this->authorStructure);
    }

    public function testIndex(): void
    {
        $authorsCount = 5;
        $this->login();
        factory(Author::class, $authorsCount)->create();

        $data = $this->jsonApi('GET', 'authors');

        $this->assertStructure($data, ['data' =>  ['*' => $this->authorStructure]]);
        $this->assertCount($authorsCount, $data['data']);
    }

    public function testSearch(): void
    {
        $this->login();

        $authorsData = [
            [
                'name' => 'John',
                'personal_name' => 'Bob',
                'title' => 'Alice',
            ],
            [
                'name' => 'Smith',
                'personal_name' => 'Johnny',
                'title' => 'Jason',
            ],
            [
                'name' => 'Face',
                'personal_name' => 'Donald',
                'title' => 'Junior Johnny',
            ],
            [
                'name' => 'Alice',
                'personal_name' => 'Bob',
                'title' => 'David',
            ],
        ];

        foreach ($authorsData as $authorData) {
            factory(Author::class)->create($authorData);
        }


        $data = $this->jsonApi('GET', 'authors/search?term=Joh');

        $this->assertStructure($data, ['data' =>  ['*' => $this->authorStructure]]);
        $this->assertCount(3, $data['data']);
    }

    public function testStore(): void
    {
        $this->login();

        $payload = factory(Author::class)->raw();

        $responseData = $this->jsonApi('POST', 'authors', $payload);

        $this->assertStructure($responseData, $this->authorStructure);

        $this->assertDatabaseHas('authors', $payload);
    }
}
