<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages;

use Filament\Resources\Pages\ListRecords;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationOrderResource;

class ListNavigationOrders extends ListRecords
{
    protected static string $resource = NavigationOrderResource::class;
}
