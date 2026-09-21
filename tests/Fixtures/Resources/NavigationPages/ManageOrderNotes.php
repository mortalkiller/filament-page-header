<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationOrderResource;

class ManageOrderNotes extends ManageRelatedRecords
{
    use HasNavigationHeader;

    protected static string $resource = NavigationOrderResource::class;

    protected static string $relationship = 'notes';

    public static function getNavigationLabel(): string
    {
        return 'Record notes';
    }

    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('body')]);
    }
}
