<?php

declare(strict_types=1);

namespace App\Http\Requests\Member\Reservation;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'studio_id' => ['required', 'integer', 'exists:studios,id'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'usage_hour' => ['required', 'integer', 'between:1,6'],
        ];
    }

    public function attributes(): array
    {
        return [
            'studio_id' => 'スタジオID',
            'start_at' => '利用開始時間',
            'usage_hour' => '利用時間',
        ];
    }
}
