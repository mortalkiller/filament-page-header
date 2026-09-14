<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use Workbench\App\Http\Middleware\LocalDemoUser;
use Workbench\App\Pages\HeaderGallery;
use Workbench\App\Pages\NativePage;

final class DemoPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('demo')->path('demo')->brandName('Page Header')
            ->spa()->sidebarCollapsibleOnDesktop()->userMenu(false)->font('sans-serif', provider: LocalFontProvider::class)
            ->colors(['primary' => Color::Indigo])
            ->plugin(PageHeaderPlugin::make())
            ->pages([HeaderGallery::class, NativePage::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                LocalDemoUser::class,
            ], isPersistent: true);
    }
}
