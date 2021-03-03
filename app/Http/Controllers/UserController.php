<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Resources\UserBookResource;
use App\Http\Resources\UserResource;
use App\User;
use App\UserBook;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->get('query', null);

        $usersQuery = User::query();

        if ($query) {
            $usersQuery
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', '=', $query);
        }

        $usersQuery->withCount(['followers', 'followees']);

        return UserResource::collection($usersQuery->paginate());
    }

    /**
     * @param Request $request
     * @param User $user
     * @return UserResource
     * @throws ValidationException
     */
    public function update(Request $request, User $user): UserResource
    {
        $rules = [
            'avatar' => 'string'
        ];
        $this->validate($request, $rules);

        if (auth()->user()->id !== $user->id) {
            abort(403);
        }

        $user->update($request->all());
        $user->loadCount(['followers', 'followees']);

        return new UserResource($user);
    }

    /**
     * @param User $user
     * @return UserResource
     */
    public function get(User $user): UserResource
    {
        $user->loadCount(['followers', 'followees']);

        return new UserResource($user->loadReadBooksCount());
    }

    public function follow(User $followee): JsonResponse
    {
        $follower = $this->getUser();
        $follower->follow($followee);

        return $this->empty();
    }

    public function unfollow(User $followee): JsonResponse
    {
        $follower = $this->getUser();
        $follower->unfollow($followee);

        return $this->empty();
    }

    public function followersIds(User $user): JsonResponse
    {
        return new JsonResponse($user->followers()->pluck('id'));
    }

    public function followeesIds(User $user): JsonResponse
    {
        return new JsonResponse($user->followees()->pluck('id'));
    }

    public function followers(User $user): AnonymousResourceCollection
    {
        $followers = $user->followers()->withCount(['followees', 'followers'])->paginate();

        return UserResource::collection($followers);
    }

    public function followees(User $user): AnonymousResourceCollection
    {
        $followees = $user->followees()->withCount(['followees', 'followers'])->paginate();

        return UserResource::collection($followees);
    }
}
