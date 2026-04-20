<?php

namespace Database\Seeders;

use App\Models\Canton;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(base_path('data/ubicaciones.json'));
        $provinces = json_decode($json, true);

        DB::transaction(function () use ($provinces) {
            foreach ($provinces as $provinceData) {
                $province = Province::updateOrCreate(
                    ['code' => $provinceData['codigo']],
                    ['name' => $provinceData['nombre']],
                );

                foreach ($provinceData['cantones'] ?? [] as $cantonData) {
                    $canton = Canton::updateOrCreate(
                        ['province_id' => $province->id, 'code' => $cantonData['codigo']],
                        ['name' => $cantonData['nombre']],
                    );

                    foreach ($cantonData['distritos'] ?? [] as $districtData) {
                        $district = District::updateOrCreate(
                            ['canton_id' => $canton->id, 'code' => $districtData['codigo']],
                            ['name' => $districtData['nombre']],
                        );

                        $neighborhoods = collect($districtData['barrios'] ?? [])->map(fn ($b) => [
                            'district_id' => $district->id,
                            'code' => $b['codigo'],
                            'name' => $b['nombre'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])->all();

                        foreach (array_chunk($neighborhoods, 500) as $chunk) {
                            Neighborhood::upsert($chunk, ['district_id', 'code'], ['name']);
                        }
                    }
                }
            }
        });
    }
}

