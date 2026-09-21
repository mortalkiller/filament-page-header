<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

class NavigationPage extends ExamplePage
{
    public string $breadcrumbPosition = 'outside';

    public bool $headerNavigation = false;

    public bool $retainNavigation = false;

    public bool $emptyHeader = false;

    public bool $legacyCompact = false;

    public bool $customHeader = false;

    public bool $emptyNavigation = false;

    public bool $navigationBreadcrumbs = false;

    public bool $navigationCondition = false;

    protected bool $resolvingNavigationCondition = false;

    protected bool $resolvingBreadcrumbs = false;

    public function headerSchema(Schema $schema): Schema
    {
        if ($this->emptyHeader) {
            return $schema->components([]);
        }

        $header = Header::make()->compact()
            ->breadcrumbs(fn () => BreadcrumbPosition::from($this->breadcrumbPosition))
            ->subNavigation(function (): bool {
                if (! $this->navigationCondition) {
                    return $this->headerNavigation;
                }
                if ($this->resolvingNavigationCondition) {
                    throw new \LogicException('The navigation condition must not recurse.');
                }
                $this->resolvingNavigationCondition = true;
                try {
                    return count($this->getCachedSubNavigation()) > 1;
                } finally {
                    $this->resolvingNavigationCondition = false;
                }
            });

        if ($this->legacyCompact) {
            $header->hideWhenCompact([]);
        }

        if ($this->retainNavigation) {
            $header->whenCompact(fn (CompactHeader $compact) => $compact
                ->show(HeaderPart::Breadcrumbs, HeaderPart::SubNavigation));
        }

        return $schema->components([$header]);
    }

    public function getBreadcrumbs(): array
    {
        if (! $this->navigationBreadcrumbs) {
            return parent::getBreadcrumbs();
        }

        if ($this->resolvingBreadcrumbs) {
            throw new \LogicException('Navigation-based breadcrumbs must not render recursively.');
        }

        $this->resolvingBreadcrumbs = true;
        try {
            $item = $this->getCachedSubNavigation()[0]->getItems()[0];

            return [$item->getUrl() => $item->getLabel(), 'Current page'];
        } finally {
            $this->resolvingBreadcrumbs = false;
        }
    }

    public function getHeader(): ?View
    {
        return $this->customHeader ? view('page-header-tests::custom-header') : parent::getHeader();
    }

    public function getSubNavigation(): array
    {
        if ($this->emptyNavigation) {
            return [];
        }

        return [
            NavigationItem::make('Overview')->url('/test/navigation/overview')->isActiveWhen(fn () => true),
            NavigationItem::make('Details')->url('/test/navigation/details')->badge('3'),
            NavigationItem::make('Restricted')->url('/test/navigation/restricted')->visible(false),
            NavigationGroup::make('Reports')->items([
                NavigationItem::make('Export')->url('/test/navigation/export')->openUrlInNewTab(),
                NavigationItem::make('Secret export')->url('/test/navigation/secret')->hidden(),
            ]),
        ];
    }
}
