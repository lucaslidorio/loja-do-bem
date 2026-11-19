<?php

namespace App\Providers\Filament;

use App\Models\Edicao;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('painel')
            ->login()
            ->brandName('Loja do Bem · Painel')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::Pink,
                'gray' => Color::Stone,
            ])
            ->navigationGroups(['Leilão', 'No dia do leilão', 'Cadastros', 'Sistema'])
            ->navigationItems([
                NavigationItem::make('Etiquetas com QR Code')
                    ->group('No dia do leilão')
                    ->icon(Heroicon::OutlinedQrCode)
                    ->url(fn () => route('organizacao.etiquetas'), shouldOpenInNewTab: true)
                    ->sort(1),
                NavigationItem::make('Controle do telão')
                    ->group('No dia do leilão')
                    ->icon(Heroicon::OutlinedDevicePhoneMobile)
                    ->url(fn () => route('organizacao.telao'), shouldOpenInNewTab: true)
                    ->sort(2),
                NavigationItem::make('Abrir o telão')
                    ->group('No dia do leilão')
                    ->icon(Heroicon::OutlinedTv)
                    ->url(fn () => ($e = Edicao::atual()) ? route('telao', $e) : null, shouldOpenInNewTab: true)
                    ->visible(fn () => Edicao::atual() !== null)
                    ->sort(3),
                NavigationItem::make('Página de resultado')
                    ->group('No dia do leilão')
                    ->icon(Heroicon::OutlinedTrophy)
                    ->url(fn () => ($e = Edicao::atual()) ? route('resultado', $e) : null, shouldOpenInNewTab: true)
                    ->visible(fn () => Edicao::atual() !== null)
                    ->sort(4),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
