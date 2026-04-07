<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la bodega')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Select::make('responsible_user_id')
                            ->label('Responsable')
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Ubicación')
                    ->schema([
                        TextInput::make('address')
                            ->label('Dirección')
                            ->nullable()
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('province')
                                    ->label('Provincia')
                                    ->nullable(),
                                TextInput::make('canton')
                                    ->label('Cantón')
                                    ->nullable(),
                                TextInput::make('district')
                                    ->label('Distrito')
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Estado')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }
}
