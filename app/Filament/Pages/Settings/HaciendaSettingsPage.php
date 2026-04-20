<?php

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\HaciendaSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class HaciendaSettingsPage extends SettingsPage
{
    protected static string $settings = HaciendaSettings::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Hacienda';

    protected static ?string $title = 'Configuración de Hacienda';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Configuracion;

    protected static ?int $navigationSort = 2;

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ruc')
                    ->label('RUC / Cédula Jurídica')
                    ->required(),
                TextInput::make('company_name')
                    ->label('Nombre o razón social')
                    ->required(),
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
            ]);
    }
}
