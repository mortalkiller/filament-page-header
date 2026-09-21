<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'notes';

    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('body')]);
    }
}
