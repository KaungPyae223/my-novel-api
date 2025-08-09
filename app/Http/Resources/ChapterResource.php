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



        return [
            'id' => $this->id,
            'summary' => $this->summary,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'already_love' => true,
            'novel_id' => $this->novel_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'novel' => $this->whenLoaded('novel', function () {
                return collect(new NovelResource($this->novel))->only([
                    'title',
                    'love_count',
                    'cover_image',
                    'total_chapters',
                    'user_name',
                    'genre',
                ])->toArray();
            }),
        ];
    }
}
