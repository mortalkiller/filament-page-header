<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources;

use Filament\Resources\Pages\Page;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\EditNavigationOrder;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\ListNavigationOrders;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\ManageOrderNotes;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\RestrictedOrderPage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\ViewNavigationOrder;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\RelationManagers\NotesRelationManager;

class NavigationOrderResource extends OrderResource
{
    protected static ?string $slug = 'navigation-orders';

    public static function getPages(): array
    {
        return [
            'index' => ListNavigationOrders::route('/'),
            'view' => ViewNavigationOrder::route('/{record}'),
            'edit' => EditNavigationOrder::route('/{record}/edit'),
            'notes' => ManageOrderNotes::route('/{record}/notes'),
            'restricted' => RestrictedOrderPage::route('/{record}/restricted'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([ViewNavigationOrder::class, EditNavigationOrder::class, ManageOrderNotes::class, RestrictedOrderPage::class]);
    }

    public static function getRelations(): array
    {
        return [NotesRelationManager::class];
    }
}
