<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedResource;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return array|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $authorId = $request->get('author_id', null);
        $type = $request->get('type', null);
        $perPage = $request->get('per_page', 10);

        $feedQuery = Feed::query();

        if ($authorId) {
            $feedQuery->where('author_id', $authorId);
        }

        if ($type) {
            $feedQuery->where('type', $type);
        }

        return FeedResource::collection($feedQuery->paginate($perPage));
    }
}
