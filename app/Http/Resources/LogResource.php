<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
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
            'logable_type' => $this->logable_type,
            'logable_id' => $this->logable_id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'user' => $this->user->only(['id', 'full_name','email', 'profile_image']),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'title' => $this->title,
        ];
    }
}
