<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Http\Requests\AuthorCreate;
use App\Http\Resources\AuthorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(): AnonymousResourceCollection
    {
        return AuthorResource::collection(Author::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AuthorCreate $request
     * @return AuthorResource
     */
    public function store(AuthorCreate $request): AuthorResource
    {
        $author = Author::create($request->all());

        return new AuthorResource($author);
    }

    /**
     * Display the specified resource.
     *
     * @param Author $author
     * @return AuthorResource
     */
    public function show(Author $author): AuthorResource
    {
        return new AuthorResource($author);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Author $author
     * @return Response
     */
    public function edit(Author $author): ?Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Author $author
     * @return Response
     */
    public function update(Request $request, Author $author): ?Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Author $author
     * @return Response
     */
    public function destroy(Author $author): ?Response
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

        $whereFields = ['name', 'title', 'personal_name'];

        $query = Author::query();

        foreach ($whereFields as $whereField) {
            $query->orWhere($whereField, 'like', "%{$term}%");
        }

        return AuthorResource::collection($query->paginate());
    }
}
