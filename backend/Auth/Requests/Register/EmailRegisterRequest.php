<?php

namespace App\Modules\Auth\Requests\Register;

use App\Modules\Auth\Dto\Register\EmailRegisterDto;
use App\Modules\Base\Requests\TransformableRequestInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;

class EmailRegisterRequest extends FormRequest implements TransformableRequestInterface
{
    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns',
            'password' => 'required|string|min:6|max:64',
            'currency_id' => 'required|exists:currencies,id',
            'referral_id' => 'integer',
            'promo' => 'nullable|string|min:2|max:20',
        ];
    }

    public function transform(): Data
    {
        $data = $this->validated();
        return EmailRegisterDto::from([
            'email' => Str::lower(trim($data['email'])),
            'password' => $data['password'],
            'currency_id' => $data['currency_id'],
            'referral_id' => $this->get('referral_id'),
            'promo' => $this->get('promo'),
        ]);
    }
}
