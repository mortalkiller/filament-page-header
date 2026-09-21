<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use LogicException;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;

final class PageHeaderPlugin implements Plugin
{
    public const ID = 'mortalkiller-page-header';

    public const PACKAGE = 'mortalkiller/filament-page-header';

    private HeaderOptions $options;

    /** @var array<class-string, class-string> */
    private array $resourceSchemas = [];

    public function __construct()
    {
        $this->options = new HeaderOptions;
    }

    public static function make(): self
    {
        return app(self::class);
    }

    public static function get(): self
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null || ! $panel->hasPlugin(self::ID)) {
            throw new LogicException('The page header plugin is not registered for the current panel.');
        }

        $plugin = $panel->getPlugin(self::ID);

        if (! $plugin instanceof self) {
            throw new LogicException('The page header plugin identifier is registered by a different plugin.');
        }

        return $plugin;
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        if ($panel->hasPlugin(self::ID)) {
            // Panel::plugin() runs register() before adding the plugin to its
            // map, so reaching this branch means another instance is already
            // registered for this panel and the render hook is already in place.
            return;
        }

        $panel->renderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): View|string => Filament::getCurrentPanel() === $panel
                ? view('filament-page-header::styles')
                : '',
        );
    }

    public function boot(Panel $panel): void {}

    public function options(HeaderOptions $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function normal(): self
    {
        return $this->mode(HeaderMode::Normal);
    }

    public function sticky(): self
    {
        return $this->mode(HeaderMode::Sticky);
    }

    public function compact(): self
    {
        return $this->mode(HeaderMode::Compact);
    }

    public function compactBelow(int $width): self
    {
        return $this->options($this->options->compactBelow($width));
    }

    public function mode(HeaderMode $mode): self
    {
        return $this->options($this->options->mode($mode));
    }

    /** @param array<int, HeaderMode> $breakpoints */
    public function responsive(array $breakpoints): self
    {
        return $this->options($this->options->responsive($breakpoints));
    }

    public function offset(?int $pixels): self
    {
        return $this->options($this->options->offset($pixels));
    }

    public function topbarSelector(?string $selector): self
    {
        return $this->options($this->options->topbarSelector($selector));
    }

    public function getOptions(): HeaderOptions
    {
        return $this->options;
    }

    /**
     * @param  class-string<resource>  $resource
     * @param  class-string  $schema
     */
    public function schemaFor(string $resource, string $schema): self
    {
        if (! is_subclass_of($resource, Resource::class)) {
            throw new InvalidArgumentException('Header schemas must be mapped to a Filament resource class.');
        }

        if (! is_callable([$schema, 'configure'])) {
            throw new InvalidArgumentException('A header schema class must expose a public static configure method.');
        }

        $this->resourceSchemas[$resource] = $schema;

        return $this;
    }

    /**
     * @param  class-string  $resource
     * @return class-string|null
     */
    public function getSchemaFor(string $resource): ?string
    {
        return $this->resourceSchemas[$resource] ?? null;
    }
}
