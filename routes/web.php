<?php

use App\Http\Controllers\Facturacion\FacturacionController;
use App\Services\HaciendaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/panel');
});

Route::get('/debug/hacienda/{id}', fn (string $id) => app(HaciendaService::class)->consultarContribuyente($id)
);

// Ruta de facturación — protegida por autenticación
Route::middleware(['auth', 'web'])
    ->prefix('panel')
    ->post('/facturacion', [FacturacionController::class, 'store'])
    ->name('facturacion.store');
