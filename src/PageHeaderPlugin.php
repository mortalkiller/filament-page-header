<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
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
        /** @var self $plugin */
        $plugin = Filament::getCurrentPanel()->getPlugin(self::ID);

        return $plugin;
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}

    public function options(HeaderOptions $options): self
    {
        $this->options = $options;

        return $this;
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

    /** @param class-string $resource @param class-string $schema */
    public function schemaFor(string $resource, string $schema): self
    {
        $this->resourceSchemas[$resource] = $schema;

        return $this;
    }

    /** @param class-string $resource @return class-string|null */
    public function getSchemaFor(string $resource): ?string
    {
        return $this->resourceSchemas[$resource] ?? null;
    }
}
