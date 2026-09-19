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

        if ($this->app->runningInConsole()) {
            $this->commands([MakePageHeaderCommand::class]);
        }

        FilamentAsset::register([
            // The plugin emits this stylesheet in the initial head, after the panel theme.
            Css::make('page-header', __DIR__.'/../resources/css/page-header.css')->loadedOnRequest(),
            AlpineComponent::make('page-header', __DIR__.'/../resources/js/page-header.js'),
        ], package: PageHeaderPlugin::PACKAGE);
    }
}
