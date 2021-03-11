<?php

namespace App\Http\Resources;

use App\Models\ReportItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property string id
 * @property string term
 * @property string definition
 * @property string book_section_id
 * @property string type
 * @property string book_user_id
 * @property string _id
 * @property bool visibility
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

        $data['visibility'] = $this->visibility === null ? true : $this->visibility;
        $data['id'] = $this->_id;

        return $data;
    }
}
