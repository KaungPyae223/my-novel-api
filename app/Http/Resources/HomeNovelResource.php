<?php

namespace App\Http\Resources;

use App\Http\Utils\ShortNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeNovelResource extends JsonResource
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
            'description' => $this->description,
            'author' => $this->user->only(['id', 'full_name','profile_image']),
            'image' => $this->image,
            'genre' => $this->genre->genre,
            'progress' => $this->progress,
            'share_count' => ShortNumber::number_shorten($this->share_count),
            'love_count' => ShortNumber::number_shorten($this->love->count()),
            'views_count' => ShortNumber::number_shorten($this->view->count()),
            'total_chapters' => $this->chapters()->where('status', 'published')->count(),
            'description' => $this->description,
            'synopsis' => $this->synopsis,
            'tags' => $this->tags,
            'created_at' => $this->created_at->diffForHumans(),
            'already_loved' => $this->love()->where('user_id', $request->user()->id)->exists(),
        ];
    }
}
