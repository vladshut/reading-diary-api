<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookSectionResource;
use App\Http\Resources\UserBookResource;
use App\Models\UserBook;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicUserBookController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param string $publicKey
     * @return UserBookResource
     */
    public function show(string $publicKey): UserBookResource
    {
        $ub = UserBook::where(['report_public_key' => $publicKey])->get()->first();

        return new UserBookResource($ub);
    }

    /**
     * @param string $publicKey
     * @return AnonymousResourceCollection
     */
    public function sections(string $publicKey): AnonymousResourceCollection
    {
        $ub = UserBook::where(['report_public_key' => $publicKey])->get()->first();

        return BookSectionResource::collection($ub->sections()->get());
    }
}
