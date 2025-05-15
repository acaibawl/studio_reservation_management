<?php

declare(strict_types=1);

namespace App\Http\Requests\Owner\Studio;

use App\Enums\Studio\StartAt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePost extends FormRequest
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
            'name' => ['required', 'string', 'max:50', 'unique:studios,name'],
            'start_at' => ['required', new Enum(StartAt::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '名前',
            'start_at' => '開始時間',
        ];
    }
}
