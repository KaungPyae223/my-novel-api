<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NovelChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $chapterIndex = $this->novel->chapters()->where('status', 'published')->orderBy('created_at')->pluck('id')->search($this->id);


        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'view_count' => $this->view->count(),
            'chapter_index' => $chapterIndex !== false ? $chapterIndex + 1 : "D",
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->diffForHumans() : null,
        ];
    }
}
