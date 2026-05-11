<?php

namespace App\Filament\Pages\Settings;

use App\Models\Canton;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\Province;
use App\Settings\HaciendaSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HaciendaSettingsPage extends SettingsPage
{
    protected static string $settings = HaciendaSettings::class;

    protected static ?string $navigationLabel = 'Hacienda';

    protected static ?string $title = 'Configuración de Hacienda';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Credenciales ATV')
                    ->columns(2)
                    ->schema([
                        Select::make('environment')
                            ->label('Ambiente')
                            ->options([
                                'stag' => 'Pruebas (Sandbox)',
                                'prod' => 'Producción',
                            ])
                            ->required(),
                        TextInput::make('username')
                            ->label('Usuario ATV')
                            ->required(),
                        TextInput::make('password')
                            ->label('Contraseña ATV')
                            ->password()
                            ->revealable(),
                    ]),

                Section::make('Datos del Emisor')
                    ->columns(2)
                    ->schema([
                        Select::make('identification_type')
                            ->label('Tipo de identificación')
                            ->options([
                                '01' => '01 — Cédula Física',
                                '02' => '02 — Cédula Jurídica',
                                '03' => '03 — DIMEX',
                                '04' => '04 — NITE',
                            ])
                            ->required(),
                        TextInput::make('ruc')
                            ->label('Número de identificación')
                            ->required(),
                        TextInput::make('company_name')
                            ->label('Nombre o razón social')
                            ->required(),
                        TextInput::make('nombre_comercial')
                            ->label('Nombre comercial')
                            ->nullable(),
                        TextInput::make('economic_activity_code')
                            ->label('Código de actividad económica')
                            ->maxLength(6)
                            ->nullable(),
                        TextInput::make('registro_fiscal_8707')
                            ->label('Registro Fiscal Bebidas Alcohólicas (Ley 8707)')
                            ->maxLength(12)
                            ->nullable(),
                    ]),

                Section::make('Ubicación')->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('province_id')
                                    ->label('Provincia')
                                    ->options(Province::orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('canton_id', null);
                                        $set('district_id', null);
                                        $set('neighborhood_id', null);
                                    }),
                                Select::make('canton_id')
                                    ->label('Cantón')
                                    ->options(fn (Get $get) => Canton::where('province_id', $get('province_id'))
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->disabled(fn (Get $get) => ! $get('province_id'))
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('district_id', null);
                                        $set('neighborhood_id', null);
                                    }),
                                Select::make('district_id')
                                    ->label('Distrito')
                                    ->options(fn (Get $get) => District::where('canton_id', $get('canton_id'))
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->disabled(fn (Get $get) => ! $get('canton_id'))
                                    ->afterStateUpdated(fn (Set $set) => $set('neighborhood_id', null)),
                                Select::make('neighborhood_id')
                                    ->label('Barrio')
                                    ->options(fn (Get $get) => Neighborhood::where('district_id', $get('district_id'))
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->disabled(fn (Get $get) => ! $get('district_id')),
                            ]),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }
}
