<?php

namespace App\Http\Resources;

use App\Http\Utils\ShortNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomePostResource extends JsonResource
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
            'author' => $this->user->only(['id', 'full_name','profile_image']),
            'content' => $this->content,
            'image' => $this->image,
            'already_loved' => $this->love()->where('user_id', $request->user()->id)->exists(),
            'love_count' => ShortNumber::number_shorten($this->love->count()),
            'comment_count' => ShortNumber::number_shorten($this->comment->count()),
            'created_at' => $this->created_at->diffForHumans(),
            'novel' => collect(new NovelResource($this->postable))->only([
                'id',
                'title',
                'image',
                'genre',
                'progress',
                'total_chapters',
                'description',
            ])->toArray(),
        ];
    }
}
