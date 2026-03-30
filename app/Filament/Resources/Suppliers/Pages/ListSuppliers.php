<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SuppliersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SuppliersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
