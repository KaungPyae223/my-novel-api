<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
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

        $previousChapterId = $chapterIndex > 0
        ? $orderedChapters[$chapterIndex - 1]->id
        : null;

        $nextChapterId = $chapterIndex < $orderedChapters->count() - 1
        ? $orderedChapters[$chapterIndex + 1]->id
        : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'already_love' => true,
            'chapter' => $chapterIndex+1,
            'novel_id' => $this->novel_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'previous_chapter_id' => $previousChapterId,
            'next_chapter_id' => $nextChapterId,
            'novel' => collect(new NovelResource($this->novel))->only([
                'id',
                'title',
                'love_count',
                'image',
                'total_chapters',
                'user_name',
                'genre',
            ])->toArray(),
        ];
    }
}
