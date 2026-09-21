<?php

declare(strict_types=1);

namespace Workbench\App\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

class NavigationOverview extends Page
{
    use HasPageHeader;

    protected static ?string $slug = 'navigation';

    protected static ?string $title = 'Navigation overview';

    protected string $view = 'workbench::navigation';

    #[Url]
    public string $mode = 'compact';

    #[Url]
    public string $breadcrumbs = 'inside';

    #[Url]
    public bool $retain = false;

    #[Url]
    public bool $navigation = true;

    public string $note = '';

    public function headerSchema(Schema $schema): Schema
    {
        $header = Header::make()->description('Native page navigation inside a configurable header.')
            ->breadcrumbs(fn () => BreadcrumbPosition::tryFrom($this->breadcrumbs) ?? BreadcrumbPosition::Outside)
            ->subNavigation(fn () => $this->navigation)
            ->mode(HeaderMode::tryFrom($this->mode) ?? HeaderMode::Normal);
        if ($this->retain) {
            $header->whenCompact(fn (CompactHeader $compact) => $compact->show(HeaderPart::Breadcrumbs, HeaderPart::SubNavigation));
        }

        return $schema->components([$header]);
    }

    public function getBreadcrumbs(): array
    {
        return [HeaderGallery::getUrl() => 'Header examples', static::getTitle()];
    }

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([self::class, NavigationDetails::class]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['mode' => $this->mode, 'breadcrumbs' => $this->breadcrumbs, 'retain' => $this->retain, 'navigation' => $this->navigation];
    }
}
