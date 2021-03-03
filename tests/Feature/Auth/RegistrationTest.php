<?php

namespace Tests\Feature;

use App\Notifications\UserVerifyEmailNotification;
use App\User;
use App\ValueObjects\Email;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function testRegistration(): void
    {
        Notification::fake();

        $email = new Email($this->faker->email);
        $password = 'Qwe123123!';
        $postData = ['email' => $email->toString(), 'password' => $password];

        $responseData = $this->jsonApiPost('auth/register', $postData);

        $this->assertStructure($responseData, ['access_token', 'expires_in', 'token_type']);
        self::assertSame('bearer', $responseData['token_type']);

        $criteria = [
            'email' => $email->toString(),
            'name' => $email->getLocalPart(),
        ];
        $this->assertDatabaseHas('users', $criteria);

        /** @var User $user */
        $user = User::query()->where($criteria)->firstOrFail();

        self::assertTrue(Hash::check($password, $user->password));

        Notification::assertSentTo($user, UserVerifyEmailNotification::class);
    }

    /**
     * @param array $postData
     * @dataProvider validationDataProvider
     */
    public function testValidation(array $postData): void
    {
        $this->assertValidationErrorsPost('auth/register', $postData);

        $this->assertDatabaseCount('users', 0);
    }

    public function validationDataProvider(): array
    {
        $validData = [
            'email' => 'qweqwe@mail.com',
            'password' => 'Qwe123123!',
        ];

        $rules = [
            'password' => [
                null,
                1,
                'q',
                'qqqqqqqq',
                'qqqqqqq1',
                'qqqqqqq!',
                'qqqqqqq!1',
                'qqqqqqq!Q',
                'qqqqqqq1Q',
            ],
            'email' => [
                null,
                1,
                'sdfsdgfhjhdjasd',
            ]
        ];

        return $this->transformValidationRulesToInvalidDataSetsWithoutErrors($validData, $rules);
    }
}
