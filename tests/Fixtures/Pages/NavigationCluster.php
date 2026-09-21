<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages;

use Filament\Clusters\Cluster;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class NavigationCluster extends Cluster
{
    use HasPageHeader;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([Header::make()->subNavigation()]);
    }

    public function getSubNavigation(): array
    {
        return [NavigationItem::make('Overview')->url('/test/navigation/overview')];
    }
}
