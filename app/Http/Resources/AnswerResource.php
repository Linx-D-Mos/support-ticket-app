<?php

namespace App\Http\Resources;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            'body' => $this->body,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name,
                'user rol' => $this->user->rol->name,
            ],
            'files' => FileResource::collection($this->files),
        ];
    }
}
