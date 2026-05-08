<?php

namespace App\Services\Facturacion;

use App\Models\Canton;
use App\Models\Client;
use App\Models\District;
use App\Models\Province;
use App\Settings\GeneralSettings;
use App\Settings\HaciendaSettings;

class InvoicePayloadBuilder
{
    public function __construct(
        private HaciendaSettings $hacienda,
        private GeneralSettings $general,
    ) {}

    public function build(Client $client): array
    {
        return [
            'Emisor' => $this->buildEmisor(),
            'Receptor' => $this->buildReceptor($client),
        ];
    }

    private function buildEmisor(): array
    {
        $emisor = [
            'Nombre' => $this->hacienda->company_name,
            'Identificacion' => [
                'Tipo' => $this->hacienda->identification_type,
                'Numero' => $this->hacienda->ruc,
            ],
            'CorreoElectronico' => [$this->general->company_email],
        ];

        if ($this->hacienda->nombre_comercial) {
            $emisor['NombreComercial'] = $this->hacienda->nombre_comercial;
        }

        if ($this->hacienda->registro_fiscal_8707) {
            $emisor['RegistroFiscal8707'] = $this->hacienda->registro_fiscal_8707;
        }

        if ($this->hacienda->province_id) {
            $emisor['Ubicacion'] = $this->buildUbicacionFromSettings();
        }

        if ($this->general->company_phone) {
            $emisor['Telefono'] = [
                'CodigoPais' => 506,
                'NumTelefono' => $this->general->company_phone,
            ];
        }

        return $emisor;
    }

    private function buildReceptor(Client $client): array
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

    private function buildUbicacionFromSettings(): array
    {
        $hacienda = $this->hacienda;

        $province = Province::find($hacienda->province_id);
        $canton = Canton::find($hacienda->canton_id);
        $district = District::find($hacienda->district_id);

        return [
            'Provincia' => (string) ($province?->code ?? $hacienda->province_id),
            'Canton' => str_pad((string) ($canton?->code ?? $hacienda->canton_id), 2, '0', STR_PAD_LEFT),
            'Distrito' => str_pad((string) ($district?->code ?? $hacienda->district_id), 2, '0', STR_PAD_LEFT),
            'OtrasSenas' => $hacienda->address ?? '',
        ];
    }
}
