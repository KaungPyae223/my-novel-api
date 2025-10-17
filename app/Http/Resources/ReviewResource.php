<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'review' => $this->review,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'image' => $this->user->profile_image,
            ],
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }




}
