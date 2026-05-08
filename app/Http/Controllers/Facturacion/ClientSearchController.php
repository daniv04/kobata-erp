<?php

namespace App\Http\Controllers\Facturacion;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientSearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::with(['province', 'canton', 'district'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('hacienda_name', 'like', "%{$query}%")
                    ->orWhere('id_number', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'hacienda_name' => $client->hacienda_name,
                'id_number_type' => $client->id_number_type,
                'id_number' => $client->id_number,
                'code' => $client->code,
                'address' => $client->address,
                'province' => $client->province?->name,
                'canton' => $client->canton?->name,
                'district' => $client->district?->name,
            ]);

        return response()->json($clients);
    }
}
