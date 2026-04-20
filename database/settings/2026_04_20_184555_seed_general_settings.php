<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.company_name', '');
        $this->migrator->add('general.company_email', '');
        $this->migrator->add('general.company_phone', '');
        $this->migrator->add('general.company_address', '');
    }
};
