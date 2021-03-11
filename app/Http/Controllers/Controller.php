<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @return User
     */
    protected function getUser(): User
    {
        return auth()->user();
    }

    protected function empty(): JsonResponse
    {
        return new JsonResponse([], 201);
    }

    protected function abortIfNotUser($userId): void
    {
        if ($userId instanceof User) {
            $userId = $userId->id;
        }

        abort_if($userId != $this->getUser()->id, 403);
    }
}
