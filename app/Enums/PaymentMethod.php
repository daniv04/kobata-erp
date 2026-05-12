<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Efectivo = '01';
    case Tarjeta = '02';
    case Cheque = '03';
    case Transferencia = '04';
    case RecaudadoTerceros = '05';
    case SinpeMovil = '06';
    case PlataformaDigital = '07';
    case Otros = '99';

    public function getLabel(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Tarjeta => 'Tarjeta',
            self::Cheque => 'Cheque',
            self::Transferencia => 'Transferencia / depósito bancario',
            self::RecaudadoTerceros => 'Recaudado por terceros',
            self::SinpeMovil => 'SINPE Móvil',
            self::PlataformaDigital => 'Plataforma digital',
            self::Otros => 'Otros',
        };
    }

    public function requiresDescription(): bool
    {
        return $this === self::Otros;
    }
}
