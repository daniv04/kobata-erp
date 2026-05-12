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

    private function sendToHacienda(array $payload, int $clientId): array
    {
        try {
            $result = Facturacion::createAndSend('FE', $payload);
        } catch (\Throwable $e) {
            Log::error('Error inesperado al contactar Hacienda', [
                'client_id' => $clientId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw FacturacionException::haciendaNoDisponible($e);
        }

        if (! ($result['success'] ?? false)) {
            $statusCode = $result['status_code'] ?? 500;
            $errors = $result['errors'] ?? [];

            if ($statusCode === 422 || ! empty($errors)) {
                Log::warning('Errores de validación al enviar factura a Hacienda', [
                    'client_id' => $clientId,
                    'errors' => $errors,
                ]);

                throw FacturacionException::validacionFallida($errors);
            }

            Log::error('Error del paquete Hacienda', [
                'client_id' => $clientId,
                'message' => $result['message'] ?? 'Error desconocido',
                'status_code' => $statusCode,
            ]);

            throw FacturacionException::haciendaNoDisponible();
        }

        return $result;
    }
}
