<?php

namespace App\Http\Requests;

use App\Enums\SpiderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RunSpiderRequest extends FormRequest
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
            'spider_type' => ['required', 'string', new Enum(SpiderType::class)],
            'start_url' => ['nullable', 'string', 'url'],
        ];
    }
}
