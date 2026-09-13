<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\OrderResource;

class ViewOrder extends ViewRecord
{
    use HasPageHeader;

    protected static string $resource = OrderResource::class;
}
