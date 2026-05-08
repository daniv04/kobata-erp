<?php

namespace App\Filament\Pages\Settings;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class GeneralSettingsPage extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationLabel = 'General';

    protected static ?string $title = 'Configuración General';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 1;

    public function form(Schema $schema): Schema
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
                    ->required()
            ]);
    }
}
