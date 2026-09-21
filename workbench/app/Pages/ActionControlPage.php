<?php

declare(strict_types=1);

namespace Workbench\App\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;

class ActionControlPage extends HeaderGallery
{
    protected static ?string $slug = 'action-control';

    protected static ?string $title = 'Action control';

    #[Url]
    public string $position = 'end';

    #[Url]
    public string $selection = 'include';

    #[Url]
    public bool $teleport = false;

    #[Url]
    public bool $simple = false;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Header::make()
                ->heading('Example order')
                ->description('Native action presentation')
                ->metadata([TextEntry::make('reference')->state('ORD-1042')])
                ->badges([TextEntry::make('status')->state(fn () => $this->status)->badge()])
                ->actionsPosition(fn () => HeaderActionsPosition::tryFrom($this->position) ?? HeaderActionsPosition::End)
                ->mode(HeaderMode::tryFrom($this->mode) ?? HeaderMode::Normal)
                ->whenCompact(fn (CompactHeader $compact) => match ($this->selection) {
                    'include' => $compact->actions(['save', 'approve', 'archive', 'form', 'disabled']),
                    'exclude' => $compact->hideActions(['delete', 'duplicate', 'export', 'print', 'ungrouped']),
                    'none' => $compact->actions([]),
                    'approve' => $compact->actions(['approve']),
                    'save' => $compact->actions(['save']),
                    default => $compact,
                }),
        ]);
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('save')->label('Save')->submit('save')->formId('demo-form'),
            Action::make('delete')->label('Delete')->requiresConfirmation()->action(fn () => $this->status = 'Deleted'),
        ];

        if ($this->simple) {
            return $actions;
        }

        return [
            ...$actions,
            ActionGroup::make([
                Action::make('approve')->label('Approve')->visible(fn () => $this->status !== 'Approved')
                    ->requiresConfirmation()->action(fn () => $this->status = 'Approved'),
                Action::make('duplicate')->label('Duplicate')->action(fn () => $this->status = 'Duplicated'),
                ActionGroup::make([
                    Action::make('archive')->label('Archive')->action(fn () => $this->status = 'Archived'),
                ])->label('Nested')->dropdownTeleport($this->teleport),
            ])->label('More')->button()->dropdownTeleport($this->teleport),
            ActionGroup::make([
                Action::make('export')->label('Export')->action(fn () => $this->status = 'Exported'),
                ActionGroup::make([
                    Action::make('print')->label('Print')->action(fn () => $this->status = 'Printed'),
                ])->label('Formats')->button()->dropdownTeleport($this->teleport),
            ])->buttonGroup(),
            ActionGroup::make([
                Action::make('ungrouped')->label('Ungrouped')->action(fn () => $this->status = 'Ungrouped'),
            ])->dropdown(false),
            Action::make('form')->label('Edit reason')->schema([TextInput::make('reason')->required()])
                ->action(fn (array $data) => $this->status = $data['reason']),
            Action::make('disabled')->label('Disabled')->disabled(),
            Action::make('hidden')->label('Hidden action')->hidden(),
            Action::make('denied')->label('Denied action')->authorize(false),
        ];
    }
}
