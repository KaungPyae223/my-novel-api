<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserChapterWithAuth extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user_id = Auth::guard('sanctum')->user()->id;

        $view_at = $this->view()->where('user_id', $user_id)->first() ;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'view_count' => $this->view->count(),
            'view_at' => $view_at ? $view_at->created_at : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
