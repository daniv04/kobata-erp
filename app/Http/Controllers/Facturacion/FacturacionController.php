<?php

namespace App\Http\Controllers\Facturacion;

use App\Exceptions\FacturacionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\Facturacion\FacturacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FacturacionController extends Controller
{
    public function store(StoreInvoiceRequest $request, FacturacionService $service): JsonResponse
    {
        try {
            $response = $service->enviar(
                $request->integer('client_id'),
                $request->validated('items'),
                $request->validated('currency'),
                $request->validated('payment_methods'),
            );

            return response()->json($response);

        } catch (FacturacionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            Log::critical('Error inesperado en FacturacionController@store', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado. Contacte soporte técnico.',
            ], 500);
        }
    }
}
