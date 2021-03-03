<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    private $userEmail = 'some_email@dot.com';
    private $password = 'Newpassword2929!';
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser(['email' => $this->userEmail]);
    }

    public function testResetPasswordEndpoint(): void
    {
        $postData = [
            'email' => $this->userEmail,
            'password' => $this->password,
            'token' => $this->app->get(PasswordBrokerContract::class)->createToken($this->user),
        ];
        $this->jsonApiPost('auth/reset-password', $postData);

        $this->user->refresh();

        self::assertTrue(Hash::check($this->password, $this->user->password));
    }


    /**
     * @param array $postData
     * @dataProvider validationDataProvider
     */
    public function testResetPasswordEndpointValidation(array $postData): void
    {
        if (!isset($postData['token'])) {
            $postData['token'] = $this->app->get(PasswordBrokerContract::class)->createToken($this->user);
        }

        $this->assertValidationErrorsPost('auth/reset-password', $postData);
    }


    public function validationDataProvider(): array
    {
        $validData = [
            'email' => $this->userEmail,
            'password' => $this->password,
        ];

        $rules = [
            'email' => [
                null,
                1,
                'sdfsdgfhjhdjasd',
                'email@gmail.com',
            ],
            'password' => [
                null,
                1,
                'sdfsdgfhjhdjasd',
            ],
            'token' => [
                null,
                1,
                'sdfsdgfhjhdjasd',
            ],
        ];

        return $this->transformValidationRulesToInvalidDataSetsWithoutErrors($validData, $rules);
    }
}
