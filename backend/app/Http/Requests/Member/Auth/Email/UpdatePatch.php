<?php

declare(strict_types=1);

namespace App\Http\Requests\Member\Auth\Email;

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
            'email' => ['required', 'email:strict,dns,spoof'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'メールアドレス',
            'code' => '認証コード',
        ];
    }
}
