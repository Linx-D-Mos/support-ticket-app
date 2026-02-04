<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\Status;
use App\Enums\Type;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTicketRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'title' => 'string',
            'priority' => [new Enum(Priority::class)],
            'labels' => 'array',
            'labels.*' => [new Enum(Type::class)],
            'files' => 'array',
            'files.*' => 'mimes:png,jpeg,jpg,pdf,docx,xlsx|max:10240',
        ];
    }
}
