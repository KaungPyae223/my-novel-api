<?php

namespace App\Http\Resources;

use App\Http\Utils\ShortNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

use function App\Http\Utils\number_shorten;

class UserChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $view_at = null;

        if (Auth::guard('sanctum')->check()) {
            $user_id = Auth::guard('sanctum')->user()->id;
            $view_at = $this->view()->where('user_id', $user_id)->first() ;
        }

        $chapterIndex = $this->novel->chapters()->where('status', 'published')->orderBy('created_at')->pluck('id')->search($this->id);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'view_count' => ShortNumber::number_shorten($this->view->count()),
            'view_at' => $view_at ? $view_at->created_at : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'chapter_index' => $chapterIndex + 1,
        ];
    }
}
