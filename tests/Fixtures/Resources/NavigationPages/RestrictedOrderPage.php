<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages;

class RestrictedOrderPage extends ViewNavigationOrder
{
    public static function getNavigationLabel(): string
    {
        return 'Restricted record page';
    }

    public static function canAccess(array $parameters = []): bool
    {
        return false;
    }
}
