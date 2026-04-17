<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HaciendaService
{
    private const ENDPOINT = 'https://api.hacienda.go.cr/fe/ae';

    /**
     * Consulta un contribuyente en la API de Hacienda por número de cédula.
     *
     * @return array{
     *     nombre: string,
     *     tipoIdentificacion: string,
     *     regimen: array{codigo: int, descripcion: string},
     *     situacion: array{moroso: string, omiso: string, estado: string, administracionTributaria: string},
     *     actividades: array<int, array{estado: string, tipo: string, codigo: string, descripcion: string}>
     * }
     */
    public function consultarContribuyente(string $identificacion): array
    {
        try {
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->acceptJson()
                ->get(self::ENDPOINT, ['identificacion' => $identificacion]);
        } catch (ConnectionException $e) {
            throw new RuntimeException("No se pudo conectar con Hacienda: {$e->getMessage()}", previous: $e);
        }

        if ($response->notFound()) {
            throw new RuntimeException("No se encontró información para la identificación {$identificacion}.");
        }

        if ($response->failed()) {
            throw new RuntimeException("Error al consultar Hacienda: HTTP {$response->status()}");
        }

        return $response->json();
    }
}
