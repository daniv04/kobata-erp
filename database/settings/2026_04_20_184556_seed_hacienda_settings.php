<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('hacienda.ruc', '');
        $this->migrator->add('hacienda.company_name', '');
        $this->migrator->add('hacienda.environment', 'stag');
        $this->migrator->add('hacienda.username', '');
        $this->migrator->add('hacienda.password', '');
    }
};
