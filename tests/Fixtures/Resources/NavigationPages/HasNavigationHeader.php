<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages;

use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

trait HasNavigationHeader
{
    use HasPageHeader;

    public bool $headerNavigation = true;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([Header::make()->subNavigation(fn () => $this->headerNavigation)]);
    }
}
