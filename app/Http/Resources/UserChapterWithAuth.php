<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserChapterWithAuth extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'view_count' => 100,
            'view_at' => now(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
