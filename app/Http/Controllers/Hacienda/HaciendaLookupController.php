<?php

namespace App\Http\Controllers\Hacienda;

use App\Services\HaciendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HaciendaLookupController
{
    public function __invoke(Request $request, HaciendaService $hacienda): JsonResponse
    {
        $request->validate([
            'identificacion' => ['required', 'string', 'max:20'],
        ]);

        $id = $request->string('identificacion')->toString();

        try {
            $data = Cache::remember(
                "hacienda_{$id}",
                now()->addDay(),
                fn () => $hacienda->consultarContribuyente($id)
            );

            $actividad = collect($data['actividades'] ?? [])
                ->firstWhere('tipo', 'P')
                ?? collect($data['actividades'] ?? [])->first();

            return response()->json([
                'nombre' => $data['nombre'] ?? '',
                'tipoIdentificacion' => $data['tipoIdentificacion'] ?? '',
                'actividad_codigo' => $actividad['codigo'] ?? '',
                'actividad_descripcion' => $actividad['descripcion'] ?? '',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
