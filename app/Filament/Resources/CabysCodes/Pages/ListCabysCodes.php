<?php

namespace App\Filament\Resources\CabysCodes\Pages;

use App\Filament\Resources\CabysCodes\CabysCodesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCabysCodes extends ListRecords
{
    protected static string $resource = CabysCodesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
