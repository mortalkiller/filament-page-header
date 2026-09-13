<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\OrderResource;

class CreateOrder extends CreateRecord
{
    use HasPageHeader;

    protected static string $resource = OrderResource::class;
}
