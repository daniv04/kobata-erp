<?php

namespace App\Filament\Pages\Testing;

use App\Enums\NavigationGroup;
use Filament\Pages\Page;
use UnitEnum;

class DocumentosPage extends Page
{
    protected string $view = 'filament.pages.testing.documentos-page';

    protected static ?string $title = 'Documentos Enviados';

    protected static ?string $navigationLabel = 'Documentos (Testing)';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ventas;

    protected static ?int $navigationSort = 99;
}
