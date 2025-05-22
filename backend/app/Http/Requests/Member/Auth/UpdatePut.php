<?php

declare(strict_types=1);

namespace App\Http\Requests\Member\Auth;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:128'],
            'tel' => ['required', 'string', 'between:10,11', 'regex:/^[0-9]+$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '名前',
            'address' => '住所',
            'tel' => '電話番号',
        ];
    }

    public function messages(): array
    {
        return [
            'tel.regex' => '電話番号はハイフン抜きの数字のみ入力してください。',
        ];
    }
}
