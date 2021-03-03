<?php

namespace Tests\Feature;

use App\Filepond;
use App\Notifications\UserVerifyEmailNotification;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\Models\Media;
use Tests\TestCase;

class FindUserTest extends TestCase
{
    /**
     * @param array $queries
     * @param array $users
     * @dataProvider findUsersEndpointDataProvider
     */
    public function testFindUserEndpoint(array $queries, array $users): void
    {
        $expectedFoundUsers = [];

        $this->login();

        foreach ($users as $userData) {
            $result = Arr::pull($userData, '_result');
            /** @var User $user */
            $user = factory(User::class)->create($userData);

            if ($result) {
                $expectedFoundUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                ];
            }
        }

        foreach ($queries as $query) {
            $dataResponse = $this->jsonApiGet("users?query={$query}");

            self::assertCount(count($expectedFoundUsers), $dataResponse['data']);

            foreach ($expectedFoundUsers as $expectedFoundUser) {
                self::assertArrayHasArrayWithSubset($dataResponse['data'], $expectedFoundUser);
            }
        }
    }

    public function findUsersEndpointDataProvider(): array
    {
        return [
            [
                ['key', 'Key', 'KEY'],
                [
                    ['name' => 'Keyword Firstname', '_result' => true],
                    ['name' => 'Lastname Keyword', '_result' => true],
                    ['name' => 'Lastname Key', '_result' => true],
                    ['name' => 'Lastname Firstname', '_result' => false],
                ],
            ],
            [
                ['keyword@mail.com'],
                [
                    ['email' => 'keyword@mail.com', '_result' => true],
                    ['email' => 'key@mail.com', '_result' => false],
                    ['email' => 'example@mail.com', '_result' => false],
                ],
            ],
        ];
    }
}
