<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\Request;

/**
 * Invokable controller — un solo método: __invoke
 * GET /api/clientes/search?q=term
 *
 * Busca clientes por nombre o número de identificación.
 * Retorna solo los campos necesarios para el formulario.
 */
class ClienteSearchController
{
    public function __invoke(Request $request)
    {
        $term = $request->string('q')->trim();

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        return Client::query()
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query
                    ->where('hacienda_name', 'like', "%{$term}%")
                    ->orWhere('id_number', 'like', "%{$term}%");
            })
            ->select([
                'id',
                'hacienda_name',
                'id_number_type',
                'id_number',
                'email',
                'phone',
                'province_id',
                'canton_id',
                'district_id',
                'address',
            ])
            ->limit(10)
            ->get();
    }
}
