<?php

namespace App\Providers\Filament;

use App\Enums\NavigationGroup;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('panel')
            ->path('panel')
            ->login()
            ->passwordReset()
            ->navigationGroups([
                FilamentNavigationGroup::make()
                    ->label(NavigationGroup::Catalogo->getLabel())
                    ->icon(NavigationGroup::Catalogo->getIcon()),
                FilamentNavigationGroup::make()
                    ->label(NavigationGroup::BodegasInventario->getLabel())
                    ->icon(NavigationGroup::BodegasInventario->getIcon()),
                FilamentNavigationGroup::make()
                    ->label(NavigationGroup::Clientes->getLabel())
                    ->icon(NavigationGroup::Clientes->getIcon()),
                FilamentNavigationGroup::make()
                    ->label('Usuarios y Roles'),
                FilamentNavigationGroup::make()
                    ->label(NavigationGroup::Configuracion->getLabel())
                    ->icon(NavigationGroup::Configuracion->getIcon()),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): HtmlString => new HtmlString(
                    '<script>document.addEventListener("livewire:initialized",()=>{document.querySelectorAll("form").forEach(f=>f.setAttribute("novalidate",""))})</script>'
                ),
            )

            ->userMenuItems([
                Action::make('settings')
                    ->label('Configuración')
                    ->url('/panel/general-settings-page')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('Usuarios y Roles'),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
