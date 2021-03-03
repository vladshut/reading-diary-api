<?php

namespace Tests\Feature;

use App\Filepond;
use App\Notifications\UserVerifyEmailNotification;
use App\User;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\Models\Media;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    /**
     * @throws \JsonException
     */
    public function testGetUpdateEndpoint(): void
    {
        $user = $this->login();

        $responseData = $this->jsonApi('GET', "users/{$user->id}");

        $user->loadReadBooksCount();

        $expectedResponseData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at->format('Y-m-d H:i:s'),
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            'bio' => $user->bio,
            'avatar' => $user->avatar,
            'avatar_original' => $user->avatar_original,
            'facebook_id' => $user->facebook_id,
            'google_id' => $user->google_id,
            'has_password' => $user->hasPassword(),
            'read_books_count' => $user->read_books_count,
        ];

        self::assertEmpty(array_diff($expectedResponseData, $responseData));
    }
}
