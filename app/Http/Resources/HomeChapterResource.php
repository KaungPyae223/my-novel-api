<?php

namespace App\Http\Resources;

use App\Http\Utils\ShortNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $orderedChapters = $this->novel->chapters()->where('status', 'published')->orderBy('id')->get()->values();

        $chapterIndex = $orderedChapters->search(function ($chapter) {
            return $chapter->id === $this->id;
        });

        return [
            'id' => $this->id,
            'title' => 'Chapter ' . $chapterIndex+1 . ': ' . $this->title,
            'novel' => $this->novel->append('genre.genre')->only(['id', 'title', 'image', 'description', 'genre']),

            'user' => $this->novel->user->only(['id', 'full_name','image']),

            'already_loved' => $this->love()->where('user_id', $request->user()->id)->exists(),

            'view_count' => ShortNumber::number_shorten ($this->view->count()),
            'share_count' => ShortNumber::number_shorten($this->share_count),
            'love_count' => ShortNumber::number_shorten($this->love->count()),
            'preview' => substr($this->content, 0, 300),
            'created_at' => $this->created_at->diffForHumans(),

        ];
    }
}
