<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Order;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\CreateOrder;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\EditOrder;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\ListOrders;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\ViewOrder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('reference')->required()]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('reference')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
