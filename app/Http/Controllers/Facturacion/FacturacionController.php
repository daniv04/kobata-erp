<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\Facturacion\FacturacionService;
use Illuminate\Http\JsonResponse;

class FacturacionController extends Controller
{
    /**
     * POST /panel/facturacion
     *
     * Recibe el JSON del formulario React y lo procesa
     * Laravel inyecta FacturacionService automáticamente (dependency injection)
     */
    public function store(StoreInvoiceRequest $request, FacturacionService $service): JsonResponse
    {
        try {
            $response = $service->enviar($request->integer('client_id'));

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
