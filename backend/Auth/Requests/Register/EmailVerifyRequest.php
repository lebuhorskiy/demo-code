<?php

namespace App\Modules\Auth\Requests\Register;

use App\Modules\Auth\Dto\Register\VerifyEmailDto;
use App\Modules\Base\Requests\TransformableRequestInterface;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class EmailVerifyRequest extends FormRequest implements TransformableRequestInterface
{
    public function rules(): array
    {
        return [
            'user_id' => 'exists:users,id|required|integer',
            'code' => 'required|min:6|max:6|string',
        ];
    }

    public function transform(): Data
    {
        return VerifyEmailDto::from([
            'user_id' => $this->get('user_id'),
            'code' => trim($this->get('code'))
        ]);
    }
}
