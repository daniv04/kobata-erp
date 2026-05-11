<?php

namespace App\Services\Facturacion;

use App\Models\Client;
use App\Services\Facturacion\Builders\DetalleBuilder;
use App\Services\Facturacion\Builders\EmisorBuilder;
use App\Services\Facturacion\Builders\EncabezadoBuilder;
use App\Services\Facturacion\Builders\ReceptorBuilder;
use App\Services\Facturacion\Builders\ResumenBuilder;

class InvoicePayloadBuilder
{
    public function __construct(
        private EncabezadoBuilder $encabezado,
        private EmisorBuilder $emisor,
        private ReceptorBuilder $receptor,
        private DetalleBuilder $detalle,
        private ResumenBuilder $resumen,
    ) {}

    public function build(Client $client, array $items, string $currency, array $paymentMethods): array
    {
        return array_merge(
            $this->encabezado->build(),
            ['Emisor' => $this->emisor->build()],
            ['Receptor' => $this->receptor->build($client)],
            $this->detalle->build($items),
            $this->resumen->build($items, $currency, $paymentMethods),
            ['InformacionReferencia' => []],
        );
    }
}
