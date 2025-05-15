<?php

namespace App\Http\Requests\Owner\TemporaryClosingDay;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'unique:temporary_closing_days,date'],
        ];
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'date' => '日付'
        ];
    }
}
