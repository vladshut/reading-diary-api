<?php
declare(strict_types=1);


namespace App\Notifications;


use Illuminate\Auth\Notifications\ResetPassword;
final class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $mailMessage = parent::toMail($notifiable);

        $actionText = __('Reset Password');

        $replaceMap = [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ];

        $search = array_map(static function (string $key) { return "{{$key}}"; }, array_keys($replaceMap));
        $replace = array_map(static function (string $value) { return urlencode($value); }, $replaceMap);

        $actionUrl = str_replace($search, $replace, config('app.web_client.routes.reset_password'));

        $mailMessage->action($actionText, $actionUrl);

        return $mailMessage;
    }
}
