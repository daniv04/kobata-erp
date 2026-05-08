<?php

namespace App\Services\Facturacion;

use App\Models\Client;
use Daniv04\HaciendaPackage\Facades\Facturacion;

class FacturacionService
{
    public function __construct(
        private InvoicePayloadBuilder $builder,
    ) {}

    public function enviar(int $clientId): mixed
    {
        $client = Client::findOrFail($clientId);
        
        $payload = $this->builder->build($client);

        return Facturacion::createAndSend('FE', $payload);
    }
}
