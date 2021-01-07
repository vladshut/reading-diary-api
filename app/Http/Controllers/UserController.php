<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Resources\UserBookResource;
use App\User;
use App\UserBook;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param User $user
     * @return User
     * @throws ValidationException
     */
    public function update(Request $request, User $user): User
    {
        $rules = [
            'avatar' => 'string'
        ];
        $this->validate($request, $rules);

        if (auth()->user()->id !== $user->id) {
            abort(403);
        }

        $user->update($request->all());

        return $user;
    }
}
