<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case Crc = 'CRC';
    case Usd = 'USD';
    case Eur = 'EUR';

    public function getLabel(): string
    {
        return match ($this) {
            self::Crc => 'Colón costarricense (CRC)',
            self::Usd => 'Dólar estadounidense (USD)',
            self::Eur => 'Euro (EUR)',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Crc => '₡',
            self::Usd => '$',
            self::Eur => '€',
        };
    }

    public function exchangeRate(): float
    {
        return match ($this) {
            self::Crc => 1,
            self::Usd => 500,
            self::Eur => 600,
        };
    }
}
