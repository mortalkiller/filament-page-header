<?php

declare(strict_types=1);

namespace Workbench\App\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Components\Heading;
use MortalKiller\FilamentPageHeader\Components\MetadataEntry;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

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

    #[Url]
    public bool $selective = false;

    public string $note = '';

    public string $status = 'Draft';

    public int $saveCount = 0;

    public int $inlineCount = 0;

    public function getBreadcrumbs(): array
    {
        return $this->variant === 12
            ? ['/' => 'Products', 'Everyday Runner', 'Edit']
            : ['/' => 'Workbench', 'Header examples'];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        // A capped page wraps the toolbar early and masks the reported compression.
        return $this->variant === 11 ? Width::Full : parent::getMaxContentWidth();
    }

    public function headerSchema(Schema $schema): Schema
    {
        if ($this->variant === 12) {
            return $schema->components([
                Header::make()
                    ->heading('Everyday Runner')
                    ->description('RUN-042 · Cloud / Stone')
                    ->image('/product-runner.png')
                    ->initials('Everyday Runner')
                    ->badges([
                        TextEntry::make('status')->state('Active')->badge()->color('success'),
                        TextEntry::make('availability')->state('In stock')->badge()->color('gray'),
                    ])
                    ->metadata([
                        MetadataEntry::make('brand')->label('Brand')->state('Aster')->fieldIcon(Heroicon::OutlinedTag),
                        MetadataEntry::make('category')->label('Category')->state('Footwear')->fieldIcon(Heroicon::OutlinedSquares2x2),
                        MetadataEntry::make('stock')->label('Available stock')->state('128 units')->fieldIcon(Heroicon::OutlinedCube),
                    ])
                    ->mode(HeaderMode::tryFrom($this->mode) ?? HeaderMode::Normal)
                    ->whenCompact(fn (CompactHeader $compact) => $compact
                        ->show(HeaderPart::Image, HeaderPart::Badges)),
            ]);
        }

        $layout = Header::make()
            ->heading(match ($this->variant) {
                9 => 'Create document',
                1 => 'Customers',
                11 => 'Mariana Costa',
                default => 'Invoice · FT 2026/123',
            })
            ->description($this->variant === 11 ? 'mariana@example.com' : 'A reusable header with native Filament schemas.')
            ->mode(HeaderMode::tryFrom($this->mode) ?? HeaderMode::Normal);

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
        if (in_array($this->variant, [5, 10], true)) {
            $layout->leading([ImageEntry::make('avatar')->state('/avatar.svg')->defaultImageUrl('/avatar.svg')->circular()->imageHeight(56)->hiddenLabel()->extraImgAttributes(['alt' => 'Example company logo'])]);
        }
        if ($this->variant === 11) {
            $layout->initials('Mariana Costa');
        }
        if (in_array($this->variant, [6, 10], true)) {
            $layout->metadata([
                TextEntry::make('reference')->state('REF-123')->icon(Heroicon::OutlinedHashtag)->hiddenLabel(),
                TextEntry::make('customer')->state('Example customer')->url('/demo/native')->hiddenLabel(),
            ]);
        }
        if ($this->variant === 11) {
            $layout->metadata([
                MetadataEntry::make('customer_number')->fieldIcon(Heroicon::OutlinedHashtag)->label('Número de cliente')->state('C000042'),
                MetadataEntry::make('reference')->fieldIcon(Heroicon::OutlinedTag)->fieldIconPosition(IconPosition::After)->fieldIconSize(32)->label('Referência')->state('CLI-042'),
                MetadataEntry::make('vat_number')->fieldIcon(Heroicon::OutlinedIdentification)->label('NIF')->state('—'),
                MetadataEntry::make('email')->fieldIcon(Heroicon::OutlinedEnvelope)->label('Email')->state('mariana@example.com')->belowContent([Action::make('emailCustomer')->label('Enviar email')->link()->url('mailto:mariana@example.com')]),
                MetadataEntry::make('phone')->fieldIcon(Heroicon::OutlinedPhone)->label('Telefone')->state('—'),
                MetadataEntry::make('created_at')->fieldIcon(Heroicon::OutlinedCalendar)->label('Cliente desde')->state('21/05/2026'),
            ]);
        }
        if (in_array($this->variant, [7, 10], true)) {
            $layout->summary([TextEntry::make('total')->label('Total')->state(1250)->money('EUR')]);
        }
        if ($this->variant === 11) {
            $layout->summary([
                TextEntry::make('quotes')->label('Orçamentos')->state(12)->color('gray'),
                TextEntry::make('approved')->label('Aprovado')->state(8)->color('success'),
                TextEntry::make('conversion')->label('Conversão')->state('67%')->color('info'),
            ]);
        }
        if (in_array($this->variant, [8, 10], true)) {
            $layout->schema([
                Action::make('toggleStatus')->label('Toggle status')->icon(Heroicon::OutlinedStar)->action(function (): void {
                    $this->status = $this->status === 'Draft' ? 'Approved' : 'Draft';
                    $this->inlineCount++;
                }),
            ])->whenCompact(fn (CompactHeader $compact) => $compact->show(HeaderPart::Image, HeaderPart::Badges, HeaderPart::Summary, HeaderPart::Content));
        }

        if ($this->selective) {
            $layout->whenCompact(fn (CompactHeader $compact) => $compact
                ->show(HeaderPart::Image)
                ->only(HeaderPart::Badges, ['status'])
                ->only(HeaderPart::Metadata, ['reference', 'email'])
                ->only(HeaderPart::Summary, ['approved']));
        }

        return $schema->components([$layout]);
    }

    protected function getHeaderActions(): array
    {
        if ($this->variant === 12) {
            return [
                Action::make('preview')->label('Preview')->color('gray')
                    ->modalHeading('Everyday Runner')
                    ->modalDescription('Demo product preview. No catalog data is changed.')
                    ->modalSubmitAction(false)->modalCancelActionLabel('Close'),
                Action::make('save')->label('Save changes')->color('primary')
                    ->submit('save')->formId('demo-form'),
            ];
        }

        if ($this->variant === 11) {
            return [
                Action::make('saveChanges')->label('Guardar alterações')->action(fn (): null => null),
                Action::make('cancel')->label('Cancelar')->action(fn (): null => null),
                Action::make('createQuote')->label('Criar orçamento')->icon(Heroicon::OutlinedDocumentPlus)->action(fn (): null => null),
                ActionGroup::make([
                    Action::make('createDocument')->label('Criar documento')->action(fn (): null => null),
                    ActionGroup::make([
                        Action::make('createInvoice')->label('Criar fatura')->action(fn (): null => null),
                    ])->label('Outros documentos')->icon(Heroicon::ChevronDown)->button()->hiddenLabel(),
                ])->buttonGroup(),
                Action::make('syncCustomer')->label('Sincronizar cliente com Moloni')->action(fn (): null => null),
                ActionGroup::make([
                    Action::make('merge')->label('Unir cliente')->action(fn (): null => null),
                    Action::make('delete')->label('Eliminar cliente')->action(fn (): null => null),
                ])->label('Mais')->iconButton(),
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
