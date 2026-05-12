<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoDocumentoExoneracion: string implements HasLabel
{
    case Diplomaticos = '02';
    case LeyEspecial = '03';
    case AutorizacionLocalGenerica = '04';
    case ZonaFranca = '08';
    case ServiciosExportacion = '09';
    case CorporacionesMunicipales = '10';
    case AutorizacionImpuestoLocalConcreta = '11';
    case Otros = '99';

    public function getLabel(): string
    {
        return match ($this) {
            self::Diplomaticos => '02 - Ventas exentas a diplomáticos',
            self::LeyEspecial => '03 - Autorizado por Ley especial',
            self::AutorizacionLocalGenerica => '04 - Exenciones DGH Autorización Local Genérica',
            self::ZonaFranca => '08 - Exoneración a Zona Franca',
            self::ServiciosExportacion => '09 - Exoneración servicios complementarios exportación (Art. 11 RLIVA)',
            self::CorporacionesMunicipales => '10 - Órgano de las corporaciones municipales',
            self::AutorizacionImpuestoLocalConcreta => '11 - Exenciones DGH Autorización de Impuesto Local Concreta',
            self::Otros => '99 - Otros',
        };
    }

    public function requiresArticulo(): bool
    {
        return in_array($this, [self::Diplomaticos, self::LeyEspecial, self::ZonaFranca]);
    }

    public function requiresDescription(): bool
    {
        return $this === self::Otros;
    }
}
