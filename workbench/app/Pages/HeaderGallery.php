<?php

declare(strict_types=1);

namespace Workbench\App\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
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

    public function getMaxContentWidth(): Width|string|null
    {
        // A capped page wraps the toolbar early and masks the reported compression.
        return $this->variant === 11 ? Width::Full : parent::getMaxContentWidth();
    }

    public function headerSchema(Schema $schema): Schema
    {
        $layout = HeaderLayout::make()
            ->heading(match ($this->variant) {
                9 => 'Create document',
                1 => 'Customers',
                11 => 'LAURA STRADER',
                default => 'Invoice · FT 2026/123',
            })
            ->subheading($this->variant === 11 ? 'Cliente desde 21/05/2026' : 'A reusable header with native Filament schemas.');

        if ($this->variant >= 2) {
            $badges = [TextEntry::make('status')->state(fn (): string => $this->variant === 11 ? 'Ativo' : $this->status)->badge()->color($this->variant === 11 ? 'success' : 'gray')->hiddenLabel()];
            if (in_array($this->variant, [3, 10], true)) {
                $badges[] = TextEntry::make('payment')->state('Awaiting payment')->badge()->color('warning')->hiddenLabel();
            }
            if ($this->variant === 11) {
                $badges[] = TextEntry::make('provider')->state('Não sincronizado')->badge()->color('gray')->hiddenLabel();
                $badges[] = TextEntry::make('locale')->state('PT')->badge()->color('gray')->hiddenLabel();
            }
            $layout->badges($badges);
        }
        if ($this->variant === 4) {
            $layout->headingSchema([Heading::make('title')->state('Invoices')->icon(Heroicon::OutlinedDocumentText)]);
        }
        if (in_array($this->variant, [5, 10, 11], true)) {
            $layout->leading([ImageEntry::make('avatar')->state('/avatar.svg')->defaultImageUrl('/avatar.svg')->circular()->imageHeight(56)->hiddenLabel()->extraImgAttributes(['alt' => 'Example company logo'])]);
        }
        if (in_array($this->variant, [6, 10], true)) {
            $layout->metadata([
                TextEntry::make('reference')->state('REF-123')->icon(Heroicon::OutlinedHashtag)->hiddenLabel(),
                TextEntry::make('customer')->state('Example customer')->url('/demo/native')->hiddenLabel(),
            ]);
        }
        if ($this->variant === 11) {
            $layout->metadata([
                Grid::make([
                    'default' => 1,
                    'md' => 3,
                    'xl' => 6,
                ])->schema([
                    TextEntry::make('customer_number')->label('Número de cliente')->state('C000007'),
                    TextEntry::make('reference')->label('Referência')->state('—'),
                    TextEntry::make('vat_number')->label('NIF')->state('—'),
                    TextEntry::make('email')->label('Email')->state('—'),
                    TextEntry::make('phone')->label('Telefone')->state('—'),
                    TextEntry::make('created_at')->label('Cliente desde')->state('21/05/2026'),
                ]),
            ]);
        }
        if (in_array($this->variant, [7, 10], true)) {
            $layout->trailing([TextEntry::make('total')->label('Total')->state(1250)->money('EUR')]);
        }
        if ($this->variant === 11) {
            $layout->trailing([
                Grid::make(3)->schema([
                    TextEntry::make('quotes')->label('Orçamentos')->state(0)->badge()->color('gray'),
                    TextEntry::make('approved')->label('Aprovado')->state(0)->badge()->color('success'),
                    TextEntry::make('conversion')->label('Conversão')->state('0%')->badge()->color('info'),
                ]),
            ]);
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
        if ($this->variant === 11) {
            return [
                Action::make('saveChanges')->label('Guardar alterações')->action(fn (): null => null),
                Action::make('cancel')->label('Cancelar')->action(fn (): null => null),
                Action::make('createQuote')->label('Criar orçamento')->icon(Heroicon::OutlinedDocumentPlus)->action(fn (): null => null),
                Action::make('createDocument')->label('Criar documento')->action(fn (): null => null),
                Action::make('syncCustomer')->label('Sincronizar cliente com Moloni')->action(fn (): null => null),
                ActionGroup::make([
                    Action::make('merge')->label('Unir cliente')->action(fn (): null => null),
                    Action::make('delete')->label('Eliminar cliente')->action(fn (): null => null),
                ])->label('Mais')->button(),
            ];
        }

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
