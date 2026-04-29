<?php

namespace App\Filament\Pages\Facturacion;

use App\Enums\NavigationGroup;
use Filament\Pages\Page;
use UnitEnum;

class FacturacionPage extends Page
{
    protected string $view = 'filament.pages.facturacion.facturacion-page';

    protected static ?string $title = 'Nueva Factura';

    protected static ?string $navigationLabel = 'Facturación';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ventas;

    protected static ?int $navigationSort = 1;
}
