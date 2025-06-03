<?php

declare(strict_types=1);

namespace App\Http\Requests\Owner\BusinessDay;

use Carbon\WeekDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePut extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'regular_holidays' => ['nullable', 'array'],
            'regular_holidays.*' => ['nullable', Rule::enum(WeekDay::class)],
            'business_time' => ['required', 'array'],
            'business_time.open_time' => ['required', 'date_format:H:i'],
            'business_time.close_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'regular_holidays' => '定休日',
            'regular_holidays.*' => '定休日',
            'business_time' => '営業時間',
            'business_time.open_time' => '営業開始時間',
            'business_time.close_time' => '営業終了時間',
        ];
    }
}
