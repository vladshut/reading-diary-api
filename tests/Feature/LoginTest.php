<?php

namespace Tests\Feature;

use App\Notifications\UserVerifyEmailNotification;
use App\User;
use App\ValueObjects\Email;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function testLogin(): void
    {
        $password = 'Qwe123123!';
        $passwordHashed = Hash::make($password);
        $email = $this->faker->email;
        $user = factory(User::class)->create(['password' => $passwordHashed, 'email' => $email]);

        $postData = ['email' => $email, 'password' => $password];

        $responseData = $this->jsonApiPost('auth/login', $postData);

        $this->assertStructure($responseData, ['access_token', 'expires_in', 'token_type']);
        self::assertSame('bearer', $responseData['token_type']);
    }
}
