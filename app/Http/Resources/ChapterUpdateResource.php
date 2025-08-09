<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterUpdateResource extends JsonResource
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
            'novel_id' => $this->novel_id,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => Carbon::parse($this->scheduled_date)->format('H:i'),
        ];
    }
}
