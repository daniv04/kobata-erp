<?php

namespace App\Services\Facturacion\Builders;

use App\Settings\HaciendaSettings;

class EncabezadoBuilder
{
    public function __construct(private HaciendaSettings $hacienda) {}

    public function build(): array
    {
        return [
            'Encabezado' => [
                'CondicionVenta' => '01',
                'SituacionComprobante' => '1',
                'CodigoActividadEmisor' => $this->hacienda->economic_activity_code ?? '',
            ],
            'Clave' => [
                'Sucursal' => '001',
                'Terminal' => '00001',
                'TipoComprobante' => '01',
            ],
        ];
    }
}
