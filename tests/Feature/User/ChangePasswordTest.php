<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    public function testChangePasswordEndpoint(): void
    {
        Notification::fake();

        $oldPassword = '!qweqwe123';
        $newPassword = '!qweqwe123NEW';

        $user = $this->login();
        $user->password = Hash::make($oldPassword);
        $user->save();

        $data = [
            'old_password' => $oldPassword,
            'new_password' => $newPassword,
            'confirm_password' => $newPassword,
        ];

        $this->jsonApi('POST', 'auth/change-password', $data);

        $user = $user->refresh();

        self::assertTrue(Hash::check($data['new_password'], $user->password));
    }

    public function testWrongOldPassword(): void
    {
        Notification::fake();

        $oldPassword = '!qweqwe123';
        $newPassword = '!qweqwe123NEW';

        $user = $this->login();
        $user->password = Hash::make($oldPassword);
        $user->save();

        $data = [
            'old_password' => random_string(),
            'new_password' => $newPassword,
            'confirm_password' => $newPassword,
        ];

        $this->assertValidationErrorsPost('auth/change-password', $data);

        $user = $user->refresh();

        self::assertTrue(Hash::check($oldPassword, $user->password));
    }

    public function testPasswordsMismatch(): void
    {
        Notification::fake();

        $newPassword = '!qweqwe123NEW';
        $oldPassword = '!qweqwe123';

        $user = $this->login();
        $user->password = Hash::make($oldPassword);
        $user->save();

        $data = [
            'old_password' => $oldPassword,
            'new_password' => $newPassword,
            'confirm_password' => random_string(),
        ];

        $this->assertValidationErrorsPost('auth/change-password', $data);

        $user = $user->refresh();

        self::assertTrue(Hash::check($oldPassword, $user->password));
    }

    public function testChangePasswordForUserWithoutPasswordEndpoint(): void
    {
        Notification::fake();

        $newPassword = '!qweqwe123NEW';

        $user = $this->login();
        $user->password = null;
        $user->save();

        $data = [
            'new_password' => $newPassword,
            'confirm_password' => $newPassword,
        ];

        $this->jsonApi('POST', 'auth/change-password', $data);

        $user = $user->refresh();

        self::assertTrue(Hash::check($data['new_password'], $user->password));
    }

    /**
     * @param string $newPassword
     * @dataProvider validationDataProvider
     */
    public function testValidation(string $newPassword): void
    {
        Notification::fake();

        $oldPassword = '!qweqwe123';

        $user = $this->login();
        $user->password = Hash::make($oldPassword);
        $user->save();

        $data = [
            'old_password' => $oldPassword,
            'new_password' => $newPassword,
            'confirm_password' => $newPassword,
        ];

        $this->assertValidationErrorsPost('auth/change-password', $data);

        $user = $user->refresh();

        self::assertTrue(Hash::check($oldPassword, $user->password));
    }

    public function validationDataProvider(): array
    {
        return [
            ['q'],
            ['qqqqqqqq'],
            ['qqqqqqq1'],
            ['qqqqqqq!'],
            ['qqqqqqq!1'],
            ['qqqqqqq!Q'],
            ['qqqqqqq1Q'],
        ];
    }
}
