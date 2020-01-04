<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(): AnonymousResourceCollection
    {
        return BookResource::collection(Book::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return BookResource
     */
    public function store(Request $request): BookResource
    {
        $book = Book::create($request->all());

        return new BookResource($book);
    }


    /**
     * Display the specified resource.
     *
     * @param Book $book
     * @return BookResource
     */
    public function show(Book $book): BookResource
    {
        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Book $book
     * @return void
     */
    public function update(Request $request, Book $book): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Book $book
     * @return void
     */
    public function destroy(Book $book): void
    {
        //
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws ValidationException
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $this->validate($request, ['term' => 'required|min:3']);

        $term = $request->input('term');

        $whereFields = ['title', 'isbn10', 'isbn13'];

        $query = Book::query();

        foreach ($whereFields as $whereField) {
            $query->orWhere($whereField, 'like', "%{$term}%");
        }

        return BookResource::collection($query->paginate());
    }
}
