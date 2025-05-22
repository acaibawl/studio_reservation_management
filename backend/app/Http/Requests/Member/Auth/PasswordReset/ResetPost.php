<?php

declare(strict_types=1);

namespace App\Http\Requests\Member\Auth\PasswordReset;

use Illuminate\Foundation\Http\FormRequest;

class ResetPost extends FormRequest
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
            'email' => ['required', 'email:strict,dns,spoof'],
            'email_verified_token' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]+$/', 'between:8,32', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]+$/', 'between:8,32'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'メールアドレス',
            'email_verified_token' => 'メールアドレス検証トークン',
            'password' => 'パスワード',
            'password_confirmation' => 'パスワード確認',
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'パスワードには半角英数字及び-と_のみ入力できます。',
            'password_confirmation.regex' => 'パスワード確認には半角英数字及び-と_のみ入力できます。',
        ];
    }
}
