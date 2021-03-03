<?php

namespace Tests\Feature;

use App\Filepond;
use App\Notifications\UserVerifyEmailNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\Models\Media;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
{
    public function testResendVerificationEndpoint(): void
    {
        Notification::fake();

        $user = $this->login();

        $user->email_verified_at = null;
        $user->save();

        $this->jsonApi('GET', "users/{$user->id}/resend-verification");

        Notification::assertSentTo($user, UserVerifyEmailNotification::class);
    }


    public function testResendVerificationIfUserIsAlreadyVerifiedEndpoint(): void
    {
        $user = $this->login();

        Notification::fake();

        $user->email_verified_at = new Carbon();
        $user->save();

        $this->jsonApi('GET', "users/{$user->id}/resend-verification");

        Notification::assertNotSentTo($user, UserVerifyEmailNotification::class);
    }

    public function testCanConfirmEmail(): void
    {
        Notification::fake();

        $user = $this->login();

        $user->email_verified_at = null;
        $user->save();

        $notification = new UserVerifyEmailNotification();
        $mailMessage = $notification->toMail($user);
        $mailMessageArr = $mailMessage->toArray();
        $url = $mailMessageArr['actionUrl'];
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        $link = $query['link'];

        $this->withExceptionHandling();

        $response = $this->get($link);

        $user = $user->refresh();
        self::assertNotNull($user->email_verified_at);
    }
}
