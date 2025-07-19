<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NovelResource extends JsonResource
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
            'unique_name' => $this->unique_name,
            'description' => $this->description,
            'synopsis' => $this->synopsis,
            'tags' => $this->tags,
            'image' => $this->image,
            'views' => $this->views,
            'status' => $this->status,
            'progress' => $this->progress,
            'genre_id' => $this->genre_id,
            'genre' => $this->genre->genre,
            'user_name' => $this->user->full_name,
            'love_count' => 100,
            'total_chapters' => 10,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
