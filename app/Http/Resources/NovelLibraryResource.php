<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NovelLibraryResource extends JsonResource
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
            'author' => $this->user->full_name,
            'description' => $this->description,
            'genre' => $this->genre->genre,
            'synopsis' => $this->synopsis,
            'image' => $this->image,
            'chapters_count' => $this->chapters()->where('status', 'published')->where('deleted_at', null)->count(),
            'views_count' => $this->view->count(),
            'loved_count' => $this->love->count(),
            'tags' => $this->tags,
            'progress' => $this->progress,
            'created_at' => $this->created_at,
        ];
    }
}
