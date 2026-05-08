<?php

use App\Http\Controllers\Clientes\ClientSearchController;
use App\Http\Controllers\Clientes\ClientStoreController;
use App\Http\Controllers\Facturacion\FacturacionController;
use App\Http\Controllers\Hacienda\HaciendaLookupController;
use App\Http\Controllers\Productos\CategoriesController;
use App\Http\Controllers\Productos\ProductSearchController;
use App\Http\Controllers\Shared\LocationController;
use App\Services\HaciendaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/panel');
});

Route::get('/debug/hacienda/{id}', fn (string $id) => app(HaciendaService::class)->consultarContribuyente($id)
);

// Rutas de facturación — protegidas por autenticación
Route::middleware(['auth', 'web'])
    ->prefix('panel')
    ->group(function () {
        Route::get('/clientes/search', ClientSearchController::class)->name('clientes.search');
        Route::post('/clientes', ClientStoreController::class)->name('clientes.store');
        Route::get('/hacienda/lookup', HaciendaLookupController::class)->name('hacienda.lookup');
        Route::get('/productos/search', ProductSearchController::class)->name('productos.search');
        Route::get('/categorias', CategoriesController::class)->name('categorias.index');
        Route::get('/ubicacion/provincias', [LocationController::class, 'provinces'])->name('ubicacion.provincias');
        Route::get('/ubicacion/cantones', [LocationController::class, 'cantons'])->name('ubicacion.cantones');
        Route::get('/ubicacion/distritos', [LocationController::class, 'districts'])->name('ubicacion.distritos');
        Route::get('/ubicacion/barrios', [LocationController::class, 'neighborhoods'])->name('ubicacion.barrios');
        Route::post('/facturacion', [FacturacionController::class, 'store'])->name('facturacion.store');
    });
