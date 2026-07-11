<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectAuthenticatedFromAuth;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AuthenticationPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('auth')
            ->path('auth')
            ->login()
            ->registration()
            ->colors([
                'primary' => Color::Teal,
            ])
            ->discoverPages(
                in: app_path('Filament/Auth/Pages'),
                for: 'App\\Filament\\Auth\\Pages',
            )
            ->middleware([
                            EncryptCookies::class,
                            AddQueuedCookiesToResponse::class,
                            StartSession::class,

                RedirectAuthenticatedFromAuth::class,
                
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
