<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class FeedResource
 * @package App\Http\Resources
 *
 * @property string $id
 * @property string $author_id
 * @property string $title
 * @property Carbon $date
 * @property string $body
 * @property string $image
 * @property string $type
 * @property string|null $target_id
 * @property array $data
 * @property string author_name
 * @property string author_image
 */
class FeedResource extends JsonResource
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
            'author_id' => $this->author_id,
            'title' => $this->title,
            'date' => $this->date ? $this->date->format('Y-m-d H:i:s') : null,
            'body' => $this->body,
            'image' => $this->image,
            'type' => $this->type,
            'target_id' => $this->target_id,
            'data' => $this->data,
            'author_name' => $this->author_name,
            'author_image' => $this->author_image,
            'is_favorite' => $this->is_favorite
        ];
    }
}
