<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'full_name' => $this->full_name,
            'email' => $this->email,
            'username' => substr($this->username, 1),
            'profile_image' => $this->getFirstMediaUrl('profile_image'),
            'cover_image' => $this->getFirstMediaUrl('cover_image'),
            'about' => $this->about,
            'location' => $this->location,
            'phone' => $this->phone,
            'genres' => $this->novels->pluck('genre.genre')->unique()->toArray(),
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'created_at' => $this->created_at,

        ];
    }
}
