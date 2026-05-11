<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HaciendaSettings extends Settings
{
    public string $ruc;

    public string $company_name;

    public string $environment;

    public string $username;

    public string $password;

    public string $identification_type;

    public ?string $nombre_comercial;

    public ?string $registro_fiscal_8707;

    public ?int $province_id;

    public ?int $canton_id;

    public ?int $district_id;

    public ?int $neighborhood_id;

    public ?string $address;

    public ?string $economic_activity_code;

    public static function group(): string
    {
        return 'hacienda';
    }
}
