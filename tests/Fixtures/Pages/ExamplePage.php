<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\HeaderLayout;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\HeaderOptions;

class ExamplePage extends NativePage
{
    use HasPageHeader;

    public string $headerText = 'Example header';

    public string $status = 'draft';

    public string $note = '';

    public int $actionCount = 0;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            HeaderLayout::make()
                ->heading(fn (self $livewire): string => $livewire->headerText)
                ->subheading('Supporting text')
                ->badges([
                    TextEntry::make('status')->state(fn (self $livewire): string => $livewire->status)->badge()->hiddenLabel(),
                ])
                ->metadata([
                    TextEntry::make('reference')->state('REF-100')->hiddenLabel(),
                ])
                ->trailing([
                    TextEntry::make('total')->state(120)->money('EUR')->hiddenLabel(),
                ])
                ->schema([
                    Action::make('approve')->label('Approve')->action(function (self $livewire): void {
                        $livewire->status = 'approved';
                        $livewire->actionCount++;
                    }),
                    Action::make('forbidden')->label('Forbidden action')->authorize(false)->action(function (self $livewire): void {
                        $livewire->actionCount++;
                    }),
                ]),
        ]);
    }

    public function pageHeaderOptions(HeaderOptions $defaults): HeaderOptions
    {
        return $defaults->mode(HeaderMode::Compact);
    }

    public function getBreadcrumbs(): array
    {
        return ['/' => 'Home', 'Example'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')->label('Confirm')->requiresConfirmation()->action(function (self $livewire): void {
                $livewire->actionCount++;
            }),
        ];
    }
}
