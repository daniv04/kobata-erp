<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $company_name;

    public string $company_email;

    public string $company_phone;


    public static function group(): string
    {
        return 'general';
    }
}
