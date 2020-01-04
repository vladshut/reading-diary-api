<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @param string $type
     * @return array
     */
    public function redirectToProvider(string $type): array
    {
        return ['redirect_to' => Socialite::driver($type)->stateless()->redirect()->getTargetUrl()];
    }

    /**
     * Obtain the user information from third party.
     *
     * @param string $type
     * @return RedirectResponse|Redirector
     */
    public function handleProviderCallback(string $type)
    {
        $user = Socialite::driver($type)->stateless()->user();

        // check if they're an existing user
        $existingUser = User::where('email', $user->email)->first();

        if (!$existingUser) {
            $socialId = "{$type}_id";
            // create a new user
            $existingUser                  = new User;
            $existingUser->name            = $user->name;
            $existingUser->email           = $user->email;
            $existingUser->$socialId       = $user->id;
            $existingUser->avatar          = $user->avatar;
            $existingUser->avatar_original = $user->avatar_original;
            $existingUser->save();
        }

        $token = JWTAuth::fromUser($existingUser);

        return redirect(env('FRONTEND_APP_HOST') . '/login?token=' . $token);
    }
}
