<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Components;

use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use InvalidArgumentException;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;
use MortalKiller\FilamentPageHeader\HeaderOptions;

class Header extends Component
{
    protected ?CompactHeader $compactContent = null;

    /** @var array<string, bool> */
    protected array $hasCompactFields = [];

    protected mixed $avatar = null;

    protected bool $isImage = false;

    protected mixed $initialsName = null;

    protected string|BackedEnum|Closure|null $icon = null;

    /** @var string|array<int, string>|Closure|null */
    protected string|array|Closure|null $initialsColor = null;

    protected string|Closure|null $initialsTextColor = null;

    protected ?Page $page = null;

    protected ?HeaderMode $headerMode = null;

    protected ?int $compactBreakpoint = null;

    protected string $view = 'filament-page-header::components.layout';

    /** @var list<string> */
    protected array $compactHiddenSlots = ['description', 'metadata', 'summary', 'default'];

    public static function make(array|Closure $schema = []): static
    {
        $component = app(static::class);
        $component->configure();
        $component->schema($schema);

        return $component;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading(fn ($livewire) => $livewire->getHeading());
        $this->description(fn ($livewire) => $livewire->getSubheading());
    }

    /** @internal The page binds its native actions to its first Header component. */
    public function page(Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function avatar(mixed $url): static
    {
        $this->isImage = false;
        if ($url instanceof ImageEntry) {
            $this->avatar = null;

            return $this->leading([$url->hiddenLabel()->circular()->imageHeight(56)->imageWidth(56)]);
        }

        $this->childComponents([], 'leading');
        $this->avatar = $url;

        return $this;
    }

    public function image(mixed $url): static
    {
        $this->avatar($url);
        $this->isImage = true;

        if ($url instanceof ImageEntry) {
            $url->circular(false)->square()->imageHeight('var(--fph-image-size)')->imageWidth('var(--fph-image-size)');
        }

        return $this;
    }

    public function isImage(): bool
    {
        return $this->isImage;
    }

    public function initials(mixed $name): static
    {
        $this->initialsName = $name;

        return $this;
    }

    public function icon(string|BackedEnum|Closure|null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /** @param string|array<int, string>|Closure|null $color */
    public function initialsColor(string|array|Closure|null $color): static
    {
        $this->initialsColor = $color;

        return $this;
    }

    public function initialsTextColor(string|Closure|null $color): static
    {
        $this->initialsTextColor = $color;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        $url = trim((string) $this->evaluate($this->avatar));
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($url === '' || preg_match('/[\\x00-\\x20]/', $url) || $scheme === false
            || ($scheme !== null && ! in_array(strtolower($scheme), ['http', 'https'], true))) {
            return null;
        }

        return $url;
    }

    public function getAvatarName(): string
    {
        return trim((string) $this->evaluate($this->initialsName));
    }

    public function getInitials(): string
    {
        $words = preg_split('/\\s+/u', $this->getAvatarName(), flags: PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('', array_map(
            static fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)),
            array_slice($words, 0, 2),
        ));
    }

    public function getIcon(): string|BackedEnum|null
    {
        return $this->evaluate($this->icon);
    }

    /** @return array<string, string> */
    public function getInitialsAvatarStyles(): array
    {
        $palette = $this->getInitialsPalette();

        if ($palette === null) {
            return [];
        }

        $background = $palette[600] ?? $palette[500] ?? null;

        if (! is_string($background)) {
            return [];
        }

        $textColor = $this->evaluate($this->initialsTextColor);

        return [
            '--fph-avatar-background' => $background,
            '--fph-avatar-text' => is_string($textColor) ? $textColor : $this->getInitialsContrastColor($palette, $background),
        ];
    }

    /** @return array<int, string>|null */
    private function getInitialsPalette(): ?array
    {
        $color = $this->evaluate($this->initialsColor);

        if (is_string($color)) {
            $color = FilamentColor::getColor($color);
        }

        if (! is_array($color)) {
            return null;
        }

        return $color;
    }

    /** @param array<int, string> $palette */
    private function getInitialsContrastColor(array $palette, string $background): string
    {
        $light = $palette[50] ?? 'oklch(1 0 0)';
        $dark = $palette[950] ?? 'oklch(0 0 0)';

        return Color::calculateContrastRatio($background, $dark) >= Color::calculateContrastRatio($background, $light)
            ? $dark
            : $light;
    }

    public function normal(): static
    {
        return $this->mode(HeaderMode::Normal);
    }

    public function sticky(): static
    {
        return $this->mode(HeaderMode::Sticky);
    }

    public function compact(): static
    {
        return $this->mode(HeaderMode::Compact);
    }

    public function mode(HeaderMode $mode): static
    {
        $this->headerMode = $mode;
        $this->compactBreakpoint = null;

        return $this;
    }

    public function compactBelow(int $width): static
    {
        (new HeaderOptions)->compactBelow($width);
        $this->compactBreakpoint = $width;

        return $this;
    }

    public function resolveOptions(HeaderOptions $defaults): HeaderOptions
    {
        if ($this->headerMode !== null) {
            $defaults = $defaults->mode($this->headerMode);
        }

        return $this->compactBreakpoint === null ? $defaults : $defaults->compactBelow($this->compactBreakpoint);
    }

    /** @param Closure(CompactHeader): mixed $configure */
    public function whenCompact(Closure $configure): static
    {
        $compact = new CompactHeader;
        $configure($compact);
        $this->compactContent = clone $compact;
        $this->hasCompactFields = [];

        return $this;
    }

    /** @deprecated Use whenCompact() with HeaderPart::Summary. */
    public function retainSummaryWhenCompact(bool $condition = true): static
    {
        $this->compactContent = null;
        $this->compactHiddenSlots = array_values(array_diff($this->compactHiddenSlots, ['summary']));

        if (! $condition) {
            $this->compactHiddenSlots[] = 'summary';
        }

        return $this;
    }

    public function heading(mixed $state, bool $html = false): static
    {
        return $this->childComponents([Heading::make('page_heading')->state($state)->html($html)], 'heading');
    }

    public function description(mixed $state, bool $html = false): static
    {
        return $this->childComponents([Subheading::make('page_subheading')->state($state)->html($html)], 'description');
    }

    public function descriptionSchema(array|Closure $components): static
    {
        return $this->childComponents($components, 'description');
    }

    public function headingSchema(array|Closure $components): static
    {
        return $this->childComponents($components, 'heading');
    }

    public function badges(array|Closure $components): static
    {
        return $this->childComponents($components, 'badges');
    }

    public function leading(array|Closure $components): static
    {
        return $this->childComponents($components, 'leading');
    }

    public function metadata(array|Closure $components): static
    {
        return $this->childComponents($components, 'metadata');
    }

    public function summary(array|Closure $components): static
    {
        return $this->childComponents($components, 'summary');
    }

    /**
     * @deprecated Use whenCompact().
     *
     * @param  list<string>  $slots
     */
    public function hideWhenCompact(array $slots): static
    {
        $allowed = ['leading', 'description', 'metadata', 'default', 'summary', 'badges'];

        if (array_diff($slots, $allowed) !== []) {
            throw new InvalidArgumentException('Unknown compact slot. The heading cannot be hidden.');
        }

        $this->compactContent = null;
        $this->compactHiddenSlots = array_values(array_unique($slots));

        return $this;
    }

    public function isSlotHiddenWhenCompact(string $slot): bool
    {
        if ($this->compactContent !== null) {
            $part = HeaderPart::tryFrom($slot);

            return $part !== null && (! $this->compactContent->isVisible($part)
                || ($this->hasCompactFields[$slot] ?? true) === false);
        }

        return in_array($slot, $this->compactHiddenSlots, true);
    }

    protected function configureChildSchema(Schema $schema, string $key): Schema
    {
        if ($key === 'badges') {
            foreach ($schema->getComponents(withHidden: true) as $component) {
                if ($component instanceof Entry) {
                    $component->hiddenLabel();
                }
            }
        }

        return $schema->columns(1)->dense();
    }

    public function renderSlot(string $name): string
    {
        $schema = $this->getChildSchema($name);

        if ($schema === null || $schema->getComponents() === []) {
            return '';
        }

        $part = HeaderPart::tryFrom($name);
        $fields = $part === null ? null : $this->compactContent?->getFields($part);
        $this->hasCompactFields[$name] = $fields === null;

        foreach ($schema->getComponents(withHidden: true) as $component) {
            $field = match (true) {
                $component instanceof Entry, $component instanceof Action => $component->getName(),
                $component instanceof Component => $component->getKey(isAbsolute: false),
                default => null,
            };
            $selected = $fields === null || in_array($field, $fields, true);
            $component->extraAttributes(['data-fph-exclude-compact' => $selected ? 'false' : 'true'], merge: true);

            if ($selected && $component->isVisible()) {
                $this->hasCompactFields[$name] = true;
            }
        }

        return $schema->toHtml();
    }
}
