<?php

namespace App\Modules\Auth\Requests\Register\Social;

use Illuminate\Foundation\Http\FormRequest;

class AuthTelegramRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hash' => 'required|string',
            'username' => 'required|string',
            'id' => 'required|integer',
        ];
    }
}
