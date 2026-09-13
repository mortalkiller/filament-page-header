<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages;

use Filament\Pages\Page;

class NativePage extends Page
{
    protected static ?string $title = 'Native title';

    protected string $view = 'page-header-tests::page';
}
