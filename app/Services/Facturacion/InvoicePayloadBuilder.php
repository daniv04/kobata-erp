<?php

namespace App\Services\Facturacion;

use App\Models\Client;
use App\Models\Products;
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
        $enrichedItems = $this->enrichWithCabysCode($items);

        return array_merge(
            $this->encabezado->build(),
            ['Emisor' => $this->emisor->build()],
            ['Receptor' => $this->receptor->build($client)],
            $this->detalle->build($enrichedItems),
            $this->resumen->build($enrichedItems, $currency, $paymentMethods),
            ['InformacionReferencia' => []],
        );
    }

    private function enrichWithCabysCode(array $items): array
    {
        $cabysMap = Products::whereIn('id', array_column($items, 'product_id'))
            ->pluck('cabys_code', 'id');

        return array_map(
            fn (array $item) => array_merge($item, ['cabys_code' => $cabysMap[$item['product_id']] ?? '']),
            $items,
        );
    }
}
