<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed email
 * @property mixed avatar
 * @property Carbon created_at
 * @property mixed followers_count
 * @property mixed followees_count
 * @property mixed bio
 * @property Carbon email_verified_at
 * @property mixed avatar_original
 * @method hasPassword()
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'avatar_original' => $this->avatar_original,
            'bio' => $this->bio,
            'has_password' => $this->hasPassword(),
            'created_at' => $this->created_at->__toString(),
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->__toString() : null,
            'followers_count' => $this->followers_count,
            'followees_count' => $this->followees_count,
        ];
    }
}
