<?php

namespace Tests\Feature;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendResetPasswordMailTest extends TestCase
{
    private $userEmail = 'some_email@dot.com';
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser(['email' => $this->userEmail]);
    }

    public function testSendResetPasswordMailEndpoint(): void
    {
        Notification::fake();

        $this->jsonApiPost('auth/send-reset-password-mail', $this->getValidPostData());

        Notification::assertSentTo($this->user, ResetPasswordNotification::class);
    }


    /**
     * @param array $postData
     * @dataProvider validationDataProvider
     */
    public function testSendResetPasswordMailEndpointValidation(array $postData): void
    {
        Notification::fake();

        $this->assertValidationErrorsPost('auth/send-reset-password-mail', $postData);

        Notification::assertNothingSent();
    }


    public function validationDataProvider(): array
    {
        $this->initFaker();
        $validData = $this->getValidPostData();

        $rules = [
            'email' => [
                null,
                1,
                'sdfsdgfhjhdjasd',
                'email@gmail.com',
            ],
        ];

        return $this->transformValidationRulesToInvalidDataSetsWithoutErrors($validData, $rules);
    }

    private function getValidPostData(): array
    {
        return [
            'email' => $this->userEmail,
        ];
    }
}
