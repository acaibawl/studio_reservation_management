<?php

declare(strict_types=1);

namespace App\Http\Requests\Owner\Studio;

use App\Models\Studio;
use Illuminate\Validation\Rule;

class UpdatePut extends StorePost
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        /** @var Studio $studio */
        $studio = $this->route('studio');

        $rules['name'] = ['required', 'string', 'max:50', Rule::unique('studios', 'name')->ignore($studio->id)];

        return $rules;
    }
}
