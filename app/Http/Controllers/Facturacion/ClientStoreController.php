<?php

namespace App\Http\Controllers\Facturacion;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientStoreController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_number_type' => ['required', 'string', Rule::in(['01', '02', '03', '04'])],
            'id_number' => ['required', 'string', 'max:20', 'unique:clients,id_number'],
            'hacienda_name' => ['required', 'string', 'min:5', 'max:100'],
            'economic_activity_code' => ['nullable', 'string', 'max:6'],
            'economic_activity_description' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'exists:provinces,id'],
            'canton_id' => ['required', 'exists:cantons,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'neighborhood_id' => ['nullable', 'exists:neighborhoods,id'],
            'address' => ['required', 'string', 'min:5', 'max:250'],
        ]);

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
