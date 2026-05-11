<?php

namespace App\Services\Facturacion\Builders;

use App\Models\Client;

class ReceptorBuilder
{
    public function build(Client $client): array
    {
        $receptor = [
            'Nombre' => $client->hacienda_name,
            'Identificacion' => [
                'Tipo' => $client->id_number_type,
                'Numero' => $client->id_number,
            ],
        ];

        if ($client->email) {
            $receptor['CorreoElectronico'] = $client->email;
        }

        if ($client->phone) {
            $receptor['Telefono'] = [
                'CodigoPais' => 506,
                'NumTelefono' => $client->phone,
            ];
        }

        $client->loadMissing(['province', 'canton', 'district', 'neighborhood']);

        if ($client->province) {
            $receptor['Ubicacion'] = [
                'Provincia' => (string) $client->province->code,
                'Canton' => str_pad((string) $client->canton->code, 2, '0', STR_PAD_LEFT),
                'Distrito' => str_pad((string) $client->district->code, 2, '0', STR_PAD_LEFT),
                'OtrasSenas' => $client->address ?? '',
            ];

            if ($client->neighborhood) {
                $receptor['Ubicacion']['Barrio'] = $client->neighborhood->name;
            }
        }

        return $receptor;
    }
}
