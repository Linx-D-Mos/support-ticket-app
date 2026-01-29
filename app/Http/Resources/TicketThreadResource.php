<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketThreadResource extends JsonResource
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
            'title' => $this->title,
            'priority' => $this->priority,
            'status' => $this->status,
            'customer' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? 'Usuario Eliminado',
            ],
            'agent' => $this->agent ? [
                'id' => $this->agent->id,
                'name' => $this->agent->name,
            ] : null,
            'last_reply_at' => $this->last_reply_at,

            'files' => FileResource::collection($this->whenLoaded('files')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'labels' => LabelsResource::collection($this->whenLoaded('labels')),
        ];
    }
}
