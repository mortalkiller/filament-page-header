<?php

declare(strict_types=1);

namespace Workbench\App\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use MortalKiller\FilamentPageHeader\Components\HeaderLayout;
use MortalKiller\FilamentPageHeader\Components\Heading;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\HeaderOptions;

class HeaderGallery extends Page
{
    use HasPageHeader;

    protected static ?string $slug = 'headers';

    protected static ?string $title = 'Header examples';

    protected string $view = 'workbench::gallery';

    #[Url]
    public int $variant = 10;

    #[Url]
    public string $mode = 'compact';

    public string $note = '';

    public string $status = 'Draft';

    public int $saveCount = 0;

    public int $inlineCount = 0;

    public function getBreadcrumbs(): array
    {
        return ['/' => 'Workbench', 'Header examples'];
    }

    public function headerSchema(Schema $schema): Schema
    {
        $layout = HeaderLayout::make()
            ->heading($this->variant === 9 ? 'Create document' : ($this->variant === 1 ? 'Customers' : 'Invoice · FT 2026/123'))
            ->subheading('A reusable header with native Filament schemas.');

        if ($this->variant >= 2) {
            $badges = [TextEntry::make('status')->state(fn (): string => $this->status)->badge()->color('gray')->hiddenLabel()];
            if (in_array($this->variant, [3, 10], true)) {
                $badges[] = TextEntry::make('payment')->state('Awaiting payment')->badge()->color('warning')->hiddenLabel();
            }
            $layout->badges($badges);
        }
        if ($this->variant === 4) {
            $layout->headingSchema([Heading::make('title')->state('Invoices')->icon(Heroicon::OutlinedDocumentText)]);
        }
        if (in_array($this->variant, [5, 10], true)) {
            $layout->leading([ImageEntry::make('avatar')->state('/avatar.svg')->defaultImageUrl('/avatar.svg')->circular()->imageHeight(56)->hiddenLabel()->extraImgAttributes(['alt' => 'Example company logo'])]);
        }
        if (in_array($this->variant, [6, 10], true)) {
            $layout->metadata([
                TextEntry::make('reference')->state('REF-123')->icon(Heroicon::OutlinedHashtag)->hiddenLabel(),
                TextEntry::make('customer')->state('Example customer')->url('/demo/native')->hiddenLabel(),
            ]);
        }
        if (in_array($this->variant, [7, 10], true)) {
            $layout->trailing([TextEntry::make('total')->label('Total')->state(1250)->money('EUR')]);
        }
        if (in_array($this->variant, [8, 10], true)) {
            $layout->schema([
                Action::make('toggleStatus')->label('Toggle status')->icon(Heroicon::OutlinedStar)->action(function (): void {
                    $this->status = $this->status === 'Draft' ? 'Approved' : 'Draft';
                    $this->inlineCount++;
                }),
            ])->hideWhenCompact(['metadata', 'subheading']);
        }

        return $schema->components([$layout]);
    }

    public function pageHeaderOptions(HeaderOptions $defaults): HeaderOptions
    {
        return $defaults->mode(HeaderMode::tryFrom($this->mode) ?? HeaderMode::Normal);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')->label('Save draft')->icon(Heroicon::OutlinedCheck)->submit('save')->formId('demo-form'),
            ActionGroup::make([
                Action::make('confirm')->label('Confirm action')->requiresConfirmation()->action(function (): void {
                    $this->status = 'Confirmed';
                }),
                Action::make('native')->label('Native page')->url('/demo/native'),
            ])->label('More')->button(),
        ];
    }

    public function save(): void
    {
        $this->validate(['note' => ['required', 'min:3']]);
        $this->saveCount++;
    }
}
