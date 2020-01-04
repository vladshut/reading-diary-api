<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed author
 * @property mixed id
 * @property mixed title
 * @property mixed year
 * @property mixed pages
 * @property mixed isbn10
 * @property mixed isbn13
 * @property mixed lang
 * @property mixed description
 * @property mixed name
 * @property mixed order
 * @property mixed parent_id
 * @method getCoverUrl()
 */
class BookSectionResource extends JsonResource
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
            'order' => $this->order,
            'parent_id' => $this->parent_id,
        ];
    }
}
