<?php

use App\Services\HaciendaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/panel');
});

Route::get('/debug/hacienda/{id}', fn (string $id) => app(HaciendaService::class)->consultarContribuyente($id)
);
