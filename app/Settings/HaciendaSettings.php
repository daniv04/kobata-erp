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

    public static function group(): string
    {
        return 'hacienda';
    }
}
