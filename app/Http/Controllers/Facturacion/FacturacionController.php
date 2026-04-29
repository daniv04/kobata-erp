<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Services\Facturacion\FacturacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacturacionController extends Controller
{
    /**
     * POST /panel/facturacion
     *
     * Recibe el JSON del formulario React y lo procesa
     * Laravel inyecta FacturacionService automáticamente (dependency injection)
     */
    public function store(Request $request, FacturacionService $service): JsonResponse
    {
        try {
            // El servicio se encarga de inyectar el Emisor y enviar a Hacienda
            $response = $service->enviar($request->all());

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
