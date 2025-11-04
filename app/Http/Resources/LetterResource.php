<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LetterResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user_name' => $this->user->full_name,
            'user_image' => $this->user->profile_image,
            'novel_id' => $this->novel_id,
            'body' => $this->body,
            'reply' => $this->reply,
            'status' => $this->status,
            'ban' => $this->novel->ban()->where('user_id', $this->user_id)->exists(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
