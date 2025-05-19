<?php

declare(strict_types=1);

namespace App\Http\Requests\Owner\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatch extends FormRequest
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
            'usage_hour' => ['required', 'integer', 'between:1,6'],
            'memo' => ['nullable', 'string', 'max:512'],
        ];
    }

    public function attributes(): array
    {
        return [
            'usage_hour' => '利用時間',
            'memo' => 'メモ',
        ];
    }
}
