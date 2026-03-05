<?php

namespace App\Modules\Auth\Dto\Register;

use App\Modules\Currency\Models\Currency;
use App\Modules\Currency\Repositories\CurrencyRepository;
use Spatie\LaravelData\Data;

class EmailRegisterDto extends Data
{
    public function __construct(
        public string $email,
        public string $password,
        public int $currency_id,
        public ?string $promo = null,
        public ?int $referral_id,
    ) {}

    public function getCurrency(): ?Currency
    {
        /**
         * @var CurrencyRepository $repository
         */
        $repository = app(CurrencyRepository::class);

        return $repository->findById($this->currency_id);
    }

    public function getPromo()
    {
        return '';
    }
}
