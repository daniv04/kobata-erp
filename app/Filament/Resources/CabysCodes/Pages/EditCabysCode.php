<?php

namespace App\Filament\Resources\CabysCodes\Pages;

use App\Filament\Resources\CabysCodes\CabysCodesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCabysCode extends EditRecord
{
    protected static string $resource = CabysCodesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
