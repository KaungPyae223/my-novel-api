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
        $orderedChapters = $this->novel?->chapters()
            ->where('status', 'published')
            ->orderBy('id')
            ->get()
            ->values();

        $chapterIndex = $orderedChapters
            ? $orderedChapters->search(function($chapter) {
                return $chapter->id === $this->id;
            })
            : false;

        $previousChapterId = null;
        $nextChapterId = null;

        if ($chapterIndex !== false) {
            if ($chapterIndex > 0 && isset($orderedChapters[$chapterIndex - 1])) {
                $previousChapterId = $orderedChapters[$chapterIndex - 1]->id;
            }

            if ($chapterIndex < $orderedChapters->count() - 1 && isset($orderedChapters[$chapterIndex + 1])) {
                $nextChapterId = $orderedChapters[$chapterIndex + 1]->id;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->content,
            'status' => $this->status,
            'already_love' => $this->already_loved ?? false,
            'chapter' => $chapterIndex !== false ? $chapterIndex + 1 : null,
            'novel_id' => $this->novel_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'previous_chapter_id' => $previousChapterId,
            'next_chapter_id' => $nextChapterId,
            'novel' => $this->novel
                ? collect(new NovelResource($this->novel))->only([
                    'id',
                    'title',
                    'love_count',
                    'image',
                    'total_chapters',
                    'user_name',
                    'genre',
                ])->toArray()
                : null,
        ];
    }

}
