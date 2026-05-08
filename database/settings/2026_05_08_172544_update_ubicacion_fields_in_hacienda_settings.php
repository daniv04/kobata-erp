<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->delete('hacienda.ubicacion_provincia');
        $this->migrator->delete('hacienda.ubicacion_canton');
        $this->migrator->delete('hacienda.ubicacion_distrito');
        $this->migrator->delete('hacienda.ubicacion_barrio');
        $this->migrator->delete('hacienda.ubicacion_otras_senas');

        $this->migrator->add('hacienda.province_id', null);
        $this->migrator->add('hacienda.canton_id', null);
        $this->migrator->add('hacienda.district_id', null);
        $this->migrator->add('hacienda.neighborhood_id', null);
        $this->migrator->add('hacienda.address', null);
    }
};
