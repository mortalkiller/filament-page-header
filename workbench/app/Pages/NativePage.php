<?php

declare(strict_types=1);

namespace Workbench\App\Pages;

use Filament\Pages\Page;

class NativePage extends Page
{
    protected static ?string $slug = 'native';

    protected static ?string $title = 'Native header';

    protected string $view = 'workbench::native';
}
