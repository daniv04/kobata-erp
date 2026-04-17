<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum NavigationGroup implements HasIcon, HasLabel
{
    case Catalogo;
    case BodegasInventario;
    case Clientes;

    public function getLabel(): string
    {
        return match ($this) {
            self::Catalogo => 'Catálogo de Productos',
            self::BodegasInventario => 'Bodegas e Inventario',
            self::Clientes => 'Clientes',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Catalogo => Heroicon::OutlinedArchiveBox,
            self::BodegasInventario => Heroicon::OutlinedBuildingStorefront,
            self::Clientes => Heroicon::OutlinedUsers,
        };
    }
}
