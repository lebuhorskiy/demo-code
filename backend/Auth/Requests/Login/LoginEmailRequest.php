<?php

namespace App\Modules\Auth\Requests\Login;

use App\Modules\Auth\Dto\Login\EmailLoginDto;
use App\Modules\Base\Requests\TransformableRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class LoginEmailRequest extends FormRequest implements TransformableRequestInterface
{
    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns',
            'password' => 'required|string|min:6|max:64',
        ];
    }

    public function transform(): EmailLoginDto
    {
        return EmailLoginDto::from($this->validated());
    }
}
