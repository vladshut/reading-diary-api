<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed order
 * @property mixed parent_id
 * @property mixed reportItems
 * @method getCoverUrl()
 * @method visibleReportItems()
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
            'reportItems' => ReportItemResource::collection(auth()->user() ? $this->reportItems : $this->visibleReportItems()->get()),
        ];
    }
}
