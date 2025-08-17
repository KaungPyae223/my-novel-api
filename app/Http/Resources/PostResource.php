<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'content' => $this->content,
            'image' => $this->image,
            'user' => collect(new UserResource($this->user))->only([
                'full_name',
                'profile_image',
            ])->toArray(),
            'created_at' => $this->created_at->diffForHumans(),
            'is_edited' => $this->created_at != $this->updated_at,
        ];
    }
}
