<?php

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class GeneralSettingsPage extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'General';

    protected static ?string $title = 'Configuración General';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Configuracion;

    protected static ?int $navigationSort = 1;

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Nombre de la empresa')
                    ->required(),
                TextInput::make('company_email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                TextInput::make('company_phone')
                    ->label('Teléfono')
                    ->tel()
                    ->required(),
                TextInput::make('company_address')
                    ->label('Dirección')
                    ->required(),
            ]);
    }
}
