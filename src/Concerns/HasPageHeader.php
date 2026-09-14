<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Concerns;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\HeaderOptions;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

trait HasPageHeader
{
    public function headerSchema(Schema $schema): Schema
    {
        $class = $this->getPageHeaderSchemaClass();

        return $class === null ? $schema : $class::configure($schema);
    }

    public function defaultHeaderSchema(Schema $schema): Schema
    {
        $operation = match (true) {
            $this instanceof CreateRecord => 'create',
            $this instanceof EditRecord => 'edit',
            $this instanceof ViewRecord => 'view',
            default => 'index',
        };

        return $schema->columns(1)->dense()->operation($operation)->record($this->getPageHeaderRecord());
    }

    /** @return Model|array<string, mixed>|null */
    public function getPageHeaderRecord(): Model|array|null
    {
        if (! isset($this->record)) {
            return null;
        }

        return method_exists($this, 'getRecord') ? $this->getRecord() : $this->record;
    }

    /** @return class-string|null */
    public function getPageHeaderSchemaClass(): ?string
    {
        if (! method_exists(static::class, 'getResource')) {
            return null;
        }

        $resource = static::getResource();

        do {
            $mapped = $this->pageHeaderIsEnabled() ? PageHeaderPlugin::get()->getSchemaFor($resource) : null;

            if ($mapped !== null) {
                return $mapped;
            }

            $class = substr($resource, 0, (int) strrpos($resource, '\\'))
                .'\\Schemas\\'.class_basename($resource::getModel()).'Header';

            if (class_exists($class) && is_callable([$class, 'configure'])) {
                return $class;
            }

            $resource = get_parent_class($resource);
        } while ($resource && is_subclass_of($resource, \Filament\Resources\Resource::class));

        return null;
    }

    public function pageHeaderOptions(HeaderOptions $defaults): HeaderOptions
    {
        return $defaults;
    }

    public function getPageHeaderOptions(): HeaderOptions
    {
        $defaults = $this->pageHeaderOptions(
            $this->pageHeaderIsEnabled() ? PageHeaderPlugin::get()->getOptions() : new HeaderOptions,
        );

        return $this->getPageHeaderComponent()?->resolveOptions($defaults) ?? $defaults;
    }

    public function getPageHeaderComponent(): ?Header
    {
        foreach ($this->getSchema('headerSchema')?->getComponents() ?? [] as $component) {
            if ($component instanceof Header) {
                return $component;
            }
        }

        return null;
    }

    public function pageHeaderIsEnabled(): bool
    {
        return Filament::getCurrentPanel()?->hasPlugin(PageHeaderPlugin::ID) === true;
    }

    public function getHeader(): ?View
    {
        if (! $this->pageHeaderIsEnabled()) {
            return parent::getHeader();
        }

        $schema = $this->getSchema('headerSchema');

        if ($schema === null || $schema->getComponents() === []) {
            return parent::getHeader();
        }

        $headerComponent = $this->getPageHeaderComponent()?->page($this);

        return view('filament-page-header::header', [
            'headerComponent' => $headerComponent,
            'page' => $this,
            'schema' => $schema,
            'options' => $this->getPageHeaderOptions()->toArray(),
            'actions' => $this->getCachedHeaderActions(),
            'actionsAlignment' => $this->getHeaderActionsAlignment(),
            'breadcrumbs' => Filament::hasBreadcrumbs() ? $this->getBreadcrumbs() : [],
        ]);
    }
}
