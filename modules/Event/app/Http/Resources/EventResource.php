<?php

namespace Modules\Event\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'title' => $this->title,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'color_flag' => $this->color_flag,
            'filter_flag' => $this->filter_flag,
            'management_flag' => $this->management_flag,
            'available' => (bool) $this->available,
            'featured' => (bool) $this->featured,
            'amazing' => (bool) $this->amazing,
            'completed' => (bool) $this->completed,
            'iva' => $this->iva,
            'processing' => (bool) $this->processing,
            'processed' => (bool) $this->processed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
