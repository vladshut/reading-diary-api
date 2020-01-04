<?php

namespace App\Http\Resources;

use App\ReportItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed id
 * @property mixed term
 * @property mixed definition
 * @property mixed book_section_id
 * @property mixed type
 * @property mixed book_user_id
 * @property mixed _id
 * @method getCoverUrl()
 */
class ReportItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $fieldsTypeMap = ReportItem::fieldsTypeMap($this->type);

        $data = [];

        foreach ($fieldsTypeMap as $field) {
            $data[$field] = $this->$field;
        }

        $data['id'] = $this->_id;

        return $data;
    }
}
