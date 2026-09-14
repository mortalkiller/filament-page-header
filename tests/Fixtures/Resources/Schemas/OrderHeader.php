<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Order;

class OrderHeader
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Header::make()
                ->heading(fn (?Order $record, $livewire): string => $record?->reference ?? $livewire->getHeading())
                ->badges([TextEntry::make('status')->badge()->hiddenLabel()->visible(fn (?Order $record): bool => $record !== null)]),
        ]);
    }
}
