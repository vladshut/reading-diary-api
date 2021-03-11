<?php

namespace Tests\Feature;

use App\Services\Filepond;
use App\Notifications\UserVerifyEmailNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\Models\Media;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    public function testUpdateEndpoint(): void
    {
        Notification::fake();

        $bio = 'My name is John Doe!';
        $fileId = (string)uuid4();
        $avatarFilepath = __DIR__ . '/test.jpg';
        $this->createImage($avatarFilepath);

        $filepondMock = $this->mock(Filepond::class);
        $filepondMock->expects('findPathFromServerId')->times(2)
            ->andReturn($avatarFilepath, null);

        $this->app->bind(Filepond::class, static function () use ($filepondMock) {
            return $filepondMock;
        });

        $user = $this->login();

        $this->assertNotNull($user->email_verified_at);

        $postData = [
            'avatar' => $fileId,
            'bio' => $bio,
            'email' => 'example2367462345@mail.com'
        ];
        $this->jsonApi('PUT', "users/{$user->id}", $postData);

        $criteria = [
            'bio' => $bio,
            'id' => $user->id,
            'email_verified_at' => null,
        ];
        $this->assertDatabaseHas('users', $criteria);

        /** @var User $user */
        $user = User::query()->findOrFail($user->id);
        $mediaId = get_media_id_by_public_url($user->avatar);
        /** @var Media $media */
        $media = Media::query()->findOrFail($mediaId);

        self::assertFileExists($media->getPath());

        Notification::assertSentTo($user, UserVerifyEmailNotification::class);
    }

    public function testCantUpdateAnotherUser(): void
    {
        $this->withExceptionHandling();

        /** @var User $anotherUser */
        $anotherUser = User::query()->create(['name' => 'Josh', 'email' => 'josh@mail.com', 'password' => '123456']);
        $anotherUserId = (string) $anotherUser->id;

        $user = $this->login();

        $response = $this->put("api/users/{$anotherUserId}", ['avatar' => (string)uuid4()]);

        $response->assertStatus(403);
    }
}
