<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed book
 * @property mixed user
 * @property mixed status
 * @property mixed start_reading_dt
 * @property mixed end_reading_dt
 * @property mixed created_at
 * @property mixed id
 */
class UserBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'book' => new BookResource($this->book),
            'user' => new UserResource($this->user),
            'id' => $this->id,
            'status' => $this->status,
            'start_reading_dt' => $this->start_reading_dt,
            'end_reading_dt' => $this->end_reading_dt,
            'created_at' => $this->created_at,
        ];
    }
}
