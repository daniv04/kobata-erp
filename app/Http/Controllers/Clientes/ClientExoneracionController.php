<?php

namespace App\Http\Controllers\Clientes;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ClientExoneracionController
{
    public function __invoke(Client $client): JsonResponse|Response
    {
        $exoneracion = $client->activeExoneracion;

        if (! $exoneracion) {
            return response()->noContent();
        }

        return response()->json([
            'tipo_documento' => $exoneracion->tipo_documento->value,
            'tipo_documento_otro' => $exoneracion->tipo_documento_otro,
            'numero_documento' => $exoneracion->numero_documento,
            'articulo' => $exoneracion->articulo,
            'inciso' => $exoneracion->inciso,
            'nombre_institucion' => $exoneracion->nombre_institucion->value,
            'nombre_institucion_otros' => $exoneracion->nombre_institucion_otros,
            'fecha_emision' => $exoneracion->fecha_emision->toIso8601String(),
            'tarifa_exonerada' => (float) $exoneracion->tarifa_exonerada,
        ]);
    }
}
