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
 * @method getCoverUrl()
 */
class BookResource extends JsonResource
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
            'title' => $this->title,
            'year' => $this->year,
            'pages' => $this->pages,
            'isbn10' => $this->isbn10,
            'isbn13' => $this->isbn13,
            'lang' => $this->lang,
            'description' => $this->description,
            'author' => new AuthorResource($this->author),
            'cover' => $this->getCoverUrl(),
        ];
    }
}
