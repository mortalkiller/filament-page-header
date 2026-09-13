<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\OrderResource;

class EditOrder extends EditRecord
{
    use HasPageHeader;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->getSaveFormAction()->formId('form')];
    }
}
