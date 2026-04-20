<?php

namespace App\Filament\Resources\CabysCodes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CabysCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                TextInput::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('tax_percentage')
                    ->label('Porcentaje de impuesto (%)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
            ]);
    }
}
