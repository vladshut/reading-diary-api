<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Models\User;
use App\ValueObjects\Email;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'sendResetPasswordMail', 'resetPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|app_password',
        ]);

        $data = $request->only(['email', 'password']);
        $data['name'] = (new Email($data['email']))->getLocalPart();
        $data['password'] = Hash::make($data['password']);

        /** @var User $user */
        $user = User::query()->create($data);

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json($this->getUser());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $this->getUser();

        $rules = [
            'new_password' => 'required|app_password',
            'confirm_password' => 'required|same:new_password',
        ];

        if ($user->password) {
            $rules['old_password'] = 'required';
        }

        $this->validate($request, $rules);

        $data = $request->all();

        if ($user->password && !Hash::check($data['old_password'], $user->password)) {
            throw new ValidationException(['old_password' => [__('You have entered wrong password.')]]);
        }

        $this->setUserPassword($user, $request->get('new_password'));

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendResetPasswordMail(Request $request): JsonResponse
    {
        $validationRules = [
            'email' => 'required|exists:users,email',
        ];
        $this->validate($request, $validationRules);

        $credentials = $request->only('email');

        $status = Password::sendResetLink($credentials);

        $isSuccess = $status === Password::RESET_LINK_SENT;

        if (!$isSuccess) {
            throw new ValidationException(['email' => [__($status)]]);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validationRules = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|app_password',
        ];
        $this->validate($request, $validationRules);

        $credentials = $request->only('email', 'password', 'token');

        $resetPasswordCallback = function ($user, $password) { $this->setUserPassword($user, $password); };
        $status = Password::broker()->reset($credentials, $resetPasswordCallback);

        $isSuccess = $status === Password::PASSWORD_RESET;

        if (!$isSuccess) {
            throw new ValidationException(['email' => [__($status)]]);
        }

        return new JsonResponse();
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Reset the given user's password.
     *
     * @param  User  $user
     * @param  string  $password
     * @return void
     */
    protected function setUserPassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }
}
