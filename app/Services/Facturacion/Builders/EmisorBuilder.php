<?php

namespace App\Services\Facturacion\Builders;

use App\Models\Canton;
use App\Models\District;
use App\Models\Province;
use App\Settings\GeneralSettings;
use App\Settings\HaciendaSettings;

class EmisorBuilder
{
    public function __construct(
        private HaciendaSettings $hacienda,
        private GeneralSettings $general,
    ) {}

    public function build(): array
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
            $emisor['Ubicacion'] = $this->buildUbicacion();
        }

        if ($this->general->company_phone) {
            $emisor['Telefono'] = [
                'CodigoPais' => 506,
                'NumTelefono' => $this->general->company_phone,
            ];
        }

        return $emisor;
    }

    private function buildUbicacion(): array
    {
        $province = Province::find($this->hacienda->province_id);
        $canton = Canton::find($this->hacienda->canton_id);
        $district = District::find($this->hacienda->district_id);

        return [
            'Provincia' => (string) ($province?->code ?? $this->hacienda->province_id),
            'Canton' => str_pad((string) ($canton?->code ?? $this->hacienda->canton_id), 2, '0', STR_PAD_LEFT),
            'Distrito' => str_pad((string) ($district?->code ?? $this->hacienda->district_id), 2, '0', STR_PAD_LEFT),
            'OtrasSenas' => $this->hacienda->address ?? '',
        ];
    }
}
