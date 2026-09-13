<?php

declare(strict_types=1);
use MortalKiller\FilamentPageHeader\PageHeaderServiceProvider;
use Workbench\App\Providers\DemoPanelProvider;
use Workbench\App\Providers\WorkbenchServiceProvider;

return [
    PageHeaderServiceProvider::class,
    WorkbenchServiceProvider::class,
    DemoPanelProvider::class,
];
