<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages;

use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Resources\Pages\ViewRecord;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationOrderResource;

class ViewNavigationOrder extends ViewRecord
{
    use HasNavigationHeader;

    protected static string $resource = NavigationOrderResource::class;

    public bool $combined = false;

    public string $contentPosition = 'before';

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return $this->combined;
    }

    public function getContentTabPosition(): ?ContentTabPosition
    {
        return ContentTabPosition::from($this->contentPosition);
    }
}
