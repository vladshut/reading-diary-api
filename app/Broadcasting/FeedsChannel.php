<?php
declare(strict_types=1);


namespace App\Broadcasting;


use App\Models\User;

final class FeedsChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $user
     * @return array|bool
     */
    public function join(User $user)
    {
        return auth()->user()->id === $user->id;
    }
}
