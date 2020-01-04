<?php

namespace App\Http\Controllers;

use App\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Genre[]|array|Collection
     */
    public function index(Request $request)
    {
        return [
            'genre' => Genre::all(),
        ];
    }
}
