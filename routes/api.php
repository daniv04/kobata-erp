<?php

use App\Http\Controllers\Api\ClienteSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/clientes/search', ClienteSearchController::class);
});
