<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCredentialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules()
    {
        return [
            'platform_id' => 'required|exists:App\Models\Platform,id',
            'name' => 'required|string|max:255|min:3',
            'synchronized' => 'bool',
            'secret' => ['present', 'array']
        ];
    }
}
