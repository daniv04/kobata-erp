<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    public function test_valores_hacienda_correctos(): void
    {
        $this->assertSame('01', PaymentMethod::Efectivo->value);
        $this->assertSame('02', PaymentMethod::Tarjeta->value);
        $this->assertSame('03', PaymentMethod::Cheque->value);
        $this->assertSame('04', PaymentMethod::Transferencia->value);
        $this->assertSame('05', PaymentMethod::RecaudadoTerceros->value);
        $this->assertSame('06', PaymentMethod::SinpeMovil->value);
        $this->assertSame('07', PaymentMethod::PlataformaDigital->value);
        $this->assertSame('99', PaymentMethod::Otros->value);
    }

    public function test_requires_description_solo_para_otros(): void
    {
        $this->assertTrue(PaymentMethod::Otros->requiresDescription());

        foreach (PaymentMethod::cases() as $method) {
            if ($method !== PaymentMethod::Otros) {
                $this->assertFalse($method->requiresDescription(), "{$method->name} no debe requerir descripción");
            }
        }
    }

    public function test_labels_no_estan_vacios(): void
    {
        foreach (PaymentMethod::cases() as $method) {
            $this->assertNotEmpty($method->getLabel(), "{$method->name} debe tener un label");
        }
    }

    public function test_from_string_funciona(): void
    {
        $this->assertSame(PaymentMethod::SinpeMovil, PaymentMethod::from('06'));
        $this->assertSame(PaymentMethod::Otros, PaymentMethod::from('99'));
    }
}
