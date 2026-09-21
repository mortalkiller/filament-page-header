<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages;

use Filament\Resources\Pages\EditRecord;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationOrderResource;

class EditNavigationOrder extends EditRecord
{
    use HasNavigationHeader;

    protected static string $resource = NavigationOrderResource::class;
}
