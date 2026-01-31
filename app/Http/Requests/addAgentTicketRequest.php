<?php

namespace App\Http\Requests;

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class addAgentTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function passedValidation()
    {
        $user = $this->user();
        if($user->rol()->where('name', RolEnum::AGENT)->exists()){
            $this->merge(
                ['agent_id' => $user->id]
            );
        }
    }
}
