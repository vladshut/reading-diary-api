<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedResource;
use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return array|AnonymousResourceCollection
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $userId = $this->getUser()->id;
        $authorId = $request->get('author_id', null);
        $type = $request->get('type', null);
        $perPage = $request->get('per_page', 10);
        $query = $request->get('query', null);
        $isFavorite = $request->get('is_favorite', null);

        $validationRules = [
            'query' => 'string|min:3',
            'type' => ['in:' . implode([Feed::TYPE_REPORT_PUBLISHED])],
        ];

        if ($authorId !== $userId) {
            $validationRules['type'][] = 'required';
        }

        $this->validate($request, $validationRules);

        $feedQuery = Feed::query();

        if ($authorId) {
            $feedQuery->where('author_id', $authorId);
        }

        if ($type) {
            $feedQuery->where('type', $type);
        }

        if ($query) {
            $feedQuery->whereMatchQuery($query);
        }

        if ($isFavorite && $userId) {
            $feedQuery->whereInFavoriteList($userId);
        }

        $feedQuery->withFavoriteFlag($userId);

        return FeedResource::collection($feedQuery->paginate($perPage));
    }

    public function my(Request $request): AnonymousResourceCollection
    {
        $user = $this->getUser();
        $followeeIds = $user->followees()->pluck('id');
        $perPage = $request->get('per_page', 10);

        $feedQuery = Feed::query();
        $feedQuery->whereIn('author_id', $followeeIds);

        $feedQuery->withFavoriteFlag($user->id);

        return FeedResource::collection($feedQuery->paginate($perPage));
    }

    public function addToFavoriteList(Feed $feed): JsonResponse
    {
        $this->getUser()->addToFavoriteList($feed);

        return $this->empty();
    }

    public function removeFromFavoriteList(Feed $feed): JsonResponse
    {
        $this->getUser()->removeFromFavoriteList($feed);

        return $this->empty();
    }
}
