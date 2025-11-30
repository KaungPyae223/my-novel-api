<?php

namespace App\Http\Resources;

use App\Http\Utils\ShortNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $user_id = null;

        if (Auth::guard('sanctum')->check()) {
            $user_id = Auth::guard('sanctum')->user()->id;
        }

        $already_loved = false;

        if($user_id){
            $already_loved = $this->love()->where('user_id', $user_id)->exists();
        }

        return [
            'id' => $this->id,
            'relative_id' => $this->postable_id,
            'content' => $this->content,
            'image' => $this->getFirstMediaUrl('post_images'),
            'love_count' => ShortNumber::number_shorten($this->love->count()),
            'comment_count' => ShortNumber::number_shorten($this->comment->count()),
            'already_loved' => $already_loved,
            'user' => collect(new UserResource($this->user))->only([
                'full_name',
                'profile_image',
            ])->toArray(),
            'created_at' => $this->created_at->diffForHumans(),
            'is_edited' => $this->created_at != $this->updated_at,
        ];
    }
}
