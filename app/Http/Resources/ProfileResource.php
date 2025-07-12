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
            'username' => $this->username,
            'profile_image' => $this->profile_image,
            'cover_image' => $this->cover_image,
            'about' => $this->about,
            'location' => $this->location,
            'phone' => $this->phone,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'created_at' => $this->created_at,
            
        ];
    }
}
