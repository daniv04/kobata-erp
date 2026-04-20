<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Models\Canton;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\Province;
use App\Services\HaciendaService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('components.form-required-legend')->columnSpanFull(),
                Section::make('Datos Fiscales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('id_number_type')
                                    ->label('Tipo de identificación')
                                    ->options([
                                        '01' => 'Cédula Física',
                                        '02' => 'Cédula Jurídica',
                                        '03' => 'DIMEX',
                                        '04' => 'NITE',
                                    ])
                                    ->required(),
                                TextInput::make('id_number')
                                    ->label('Número de identificación')
                                    ->required()
                                    ->suffixAction(
                                        Action::make('consultarHacienda')
                                            ->label('Consultar Hacienda')
                                            ->icon(Heroicon::MagnifyingGlass)
                                            ->action(function ($get, $set) {
                                                $idNumber = $get('id_number');

                                                if (! $idNumber) {
                                                    Notification::make()
                                                        ->title('Ingrese el número de identificación primero.')
                                                        ->warning()
                                                        ->send();

                                                    return;
                                                }

                                                $set('hacienda_name', '');
                                                $set('economic_activity_code', '');
                                                $set('economic_activity_description', '');

                                                try {
                                                    $data = Cache::remember(
                                                        "hacienda_{$idNumber}",
                                                        now()->addDay(),
                                                        fn () => app(HaciendaService::class)->consultarContribuyente($idNumber)
                                                    );

                                                    $set('hacienda_name', $data['nombre'] ?? '');
                                                    $set('id_number_type', $data['tipoIdentificacion'] ?? '');

                                                    $actividad = collect($data['actividades'] ?? [])
                                                        ->firstWhere('tipo', 'P')
                                                        ?? collect($data['actividades'] ?? [])->first();

                                                    if ($actividad) {
                                                        $set('economic_activity_code', $actividad['codigo'] ?? '');
                                                        $set('economic_activity_description', $actividad['descripcion'] ?? '');
                                                    }

                                                    Notification::make()
                                                        ->title('Datos cargados desde Hacienda')
                                                        ->success()
                                                        ->send();
                                                } catch (\RuntimeException $e) {
                                                    Log::warning("Hacienda lookup failed for {$idNumber}: {$e->getMessage()}");

                                                    Notification::make()
                                                        ->title('No se pudo consultar Hacienda')
                                                        ->body('Verifique el número de identificación o ingrese los datos manualmente.')
                                                        ->warning()
                                                        ->send();
                                                }
                                            })
                                    ),
                            ]),
                        TextInput::make('hacienda_name')
                            ->label('Nombre registrado en Hacienda')
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('economic_activity_code')
                                    ->label('Código de actividad económica'),
                                TextInput::make('economic_activity_description')
                                    ->label('Descripción de actividad económica'),
                            ]),
                    ]),

                Section::make('Datos de Contacto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_name')
                                    ->label('Nombre de contacto'),
                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required(),
                            ]),
                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required(),
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

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
