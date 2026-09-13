<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\NativePage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\OrderResource;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('test')->path('test')
            ->plugin(PageHeaderPlugin::make())
            ->pages([ExamplePage::class, NativePage::class])
            ->resources([OrderResource::class]);
    }
}
