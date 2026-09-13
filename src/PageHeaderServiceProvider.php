<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

final class PageHeaderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-page-header');

        FilamentAsset::register([
            Css::make('page-header', __DIR__.'/../resources/css/page-header.css')->loadedOnRequest(),
            AlpineComponent::make('page-header', __DIR__.'/../resources/js/page-header.js'),
        ], package: PageHeaderPlugin::PACKAGE);
    }
}
