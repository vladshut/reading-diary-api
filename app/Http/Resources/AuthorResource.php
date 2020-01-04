<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed name
 */
class AuthorResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'personal_name' => $this->personal_name,
            'title' => $this->title,
            'bio' => $this->bio,
            'location' => $this->location,
            'birth_date' => $this->birth_date,
            'death_date' => $this->death_date,
            'wikipedia_url' => $this->wikipedia_url,
        ];
    }
}
