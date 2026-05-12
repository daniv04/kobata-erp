<?php

namespace Tests\Unit;

use App\Enums\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function test_exchange_rate_crc_es_uno(): void
    {
        $this->assertSame(1.0, Currency::Crc->exchangeRate());
    }

    public function test_exchange_rate_usd_es_quinientos(): void
    {
        $this->assertSame(500.0, Currency::Usd->exchangeRate());
    }

    public function test_exchange_rate_eur_es_seiscientos(): void
    {
        $this->assertSame(600.0, Currency::Eur->exchangeRate());
    }

    public function test_simbolos_correctos(): void
    {
        $this->assertSame('₡', Currency::Crc->symbol());
        $this->assertSame('$', Currency::Usd->symbol());
        $this->assertSame('€', Currency::Eur->symbol());
    }

    public function test_valores_hacienda_correctos(): void
    {
        $this->assertSame('CRC', Currency::Crc->value);
        $this->assertSame('USD', Currency::Usd->value);
        $this->assertSame('EUR', Currency::Eur->value);
    }

    public function test_from_string_funciona(): void
    {
        $this->assertSame(Currency::Usd, Currency::from('USD'));
        $this->assertSame(500.0, Currency::from('USD')->exchangeRate());
    }
}
