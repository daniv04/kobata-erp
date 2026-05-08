<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('hacienda.identification_type', '02');
        $this->migrator->add('hacienda.nombre_comercial', null);
        $this->migrator->add('hacienda.registro_fiscal_8707', null);
        $this->migrator->add('hacienda.ubicacion_provincia', null);
        $this->migrator->add('hacienda.ubicacion_canton', null);
        $this->migrator->add('hacienda.ubicacion_distrito', null);
        $this->migrator->add('hacienda.ubicacion_barrio', null);
        $this->migrator->add('hacienda.ubicacion_otras_senas', null);
    }
};
