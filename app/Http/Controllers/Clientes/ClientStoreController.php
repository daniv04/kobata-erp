<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientStoreController
{
    public function __invoke(StoreClientRequest $request): JsonResponse
    {
        $data = $request->validated();

        $client = Client::create([...$data, 'is_active' => true]);
        $client->load(['province', 'canton', 'district']);

        return response()->json([
            'id' => $client->id,
            'hacienda_name' => $client->hacienda_name,
            'id_number_type' => $client->id_number_type,
            'id_number' => $client->id_number,
            'address' => $client->address,
            'province' => $client->province?->name,
            'canton' => $client->canton?->name,
            'district' => $client->district?->name,
        ], 201);
    }
}
