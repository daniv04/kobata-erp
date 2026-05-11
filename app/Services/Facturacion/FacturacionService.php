<?php

namespace App\Services\Facturacion;

use App\Exceptions\FacturacionException;
use App\Models\Client;
use Daniv04\HaciendaPackage\Facades\Facturacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class FacturacionService
{
    public function __construct(
        private InvoicePayloadBuilder $builder,
    ) {}

    public function enviar(int $clientId, array $items, string $currency, array $paymentMethods): mixed
    {
        $client = $this->resolveClient($clientId);
        $payload = $this->buildPayload($client, $items, $currency, $paymentMethods);
        dd($payload);

        return $this->sendToHacienda($payload, $clientId);
    }

    private function resolveClient(int $clientId): Client
    {
        try {
            return Client::findOrFail($clientId);
        } catch (ModelNotFoundException) {
            throw FacturacionException::clienteNoEncontrado($clientId);
        }
    }

    private function buildPayload(Client $client, array $items, string $currency, array $paymentMethods): array
    {
        try {
            return $this->builder->build($client, $items, $currency, $paymentMethods);
        } catch (\Throwable $e) {
            Log::error('Error al construir payload de factura', [
                'client_id' => $client->id,
                'exception' => $e->getMessage(),
            ]);

            throw FacturacionException::payloadInvalido($e->getMessage());
        }
    }

    private function sendToHacienda(array $payload, int $clientId): mixed
    {
        try {
            return Facturacion::createAndSend('FE', $payload);
        } catch (\Throwable $e) {
            Log::error('Error al enviar factura a Hacienda', [
                'client_id' => $clientId,
                'payload' => $payload,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw FacturacionException::haciendaNoDisponible($e);
        }
    }
}
