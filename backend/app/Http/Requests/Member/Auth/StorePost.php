<?php

declare(strict_types=1);

namespace App\Http\Requests\Member\Auth;

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
            'email' => ['required', 'email:strict,dns,spoof'],
            'code' => ['required', 'string', 'size:6'],
            'name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:128'],
            'tel' => ['required', 'string', 'between:10,11', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]+$/', 'between:8,32', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'メールアドレス',
            'code' => '認証コード',
            'name' => '名前',
            'address' => '住所',
            'tel' => '電話番号',
            'password' => 'パスワード',
        ];
    }

    public function messages(): array
    {
        return [
            'tel.regex' => '電話番号はハイフン抜きの数字のみ入力してください。',
            'password.regex' => 'パスワードには半角英数字及び-と_のみ入力できます。',
        ];
    }
}
