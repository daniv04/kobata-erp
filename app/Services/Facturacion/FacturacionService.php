<?php

namespace App\Services\Facturacion;

use App\Settings\GeneralSettings;
use App\Settings\HaciendaSettings;
use Daniv04\HaciendaPackage\Facades\Facturacion;

/**
 * FacturacionService — Orquesta el envío de facturas a Hacienda
 *
 * RESPONSABILIDADES:
 * 1. Inyectar datos del Emisor desde Settings (no vienen del formulario)
 * 2. Validaciones de negocio (opcionales)
 * 3. Delegar al paquete hacienda-package para enviar a Hacienda CR
 * 4. Retornar la respuesta
 *
 * VENTAJA: Separación de responsabilidades
 * El controller NO toca la lógica de facturación — solo recibe request
 * y delega al servicio. Si mañana necesitamos guardar en BD, logs, etc.,
 * lo hacemos aquí sin tocar el controller.
 */
class FacturacionService
{
    public function __construct(
        private HaciendaSettings $hacienda,
        private GeneralSettings $general,
    ) {}

    /**
     * Envía una factura a Hacienda CR
     *
     * @param array $payload — datos del formulario (SIN Emisor)
     * @return mixed — respuesta del paquete hacienda-package
     */
    public function enviar(array $payload): mixed
    {
        // Inyecta el Emisor desde las Settings
        // El frontend NO lo envía — viene de la configuración del servidor
        $payload['Emisor'] = $this->buildEmisor();

        // Delega al paquete para enviar a Hacienda
        // El paquete retorna un objeto con: success, clave, response, message, etc.
        return Facturacion::createAndSend('FE', $payload);
    }

    /**
     * Construye la estructura de Emisor desde las Settings
     */
    private function buildEmisor(): array
    {
        return [
            'Nombre' => $this->hacienda->company_name,
            'Identificacion' => [
                'Tipo' => '02', // Jurídica — podría ser configurable después
                'Numero' => $this->hacienda->ruc,
            ],
            'CorreoElectronico' => [$this->general->company_email],
            'Telefono' => [
                'CodigoPais' => '506', // Costa Rica
                'NumTelefono' => $this->general->company_phone,
            ],
        ];
    }
}
