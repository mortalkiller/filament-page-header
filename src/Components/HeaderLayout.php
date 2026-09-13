<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Components;

use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use InvalidArgumentException;

class HeaderLayout extends Component
{
    protected string $view = 'filament-page-header::components.layout';

    /** @var list<string> */
    protected array $compactHiddenSlots = ['subheading', 'metadata', 'default'];

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
        $this->subheading(fn ($livewire) => $livewire->getSubheading());
    }

    public function heading(mixed $state, bool $html = false): static
    {
        return $this->childComponents([Heading::make('page_heading')->state($state)->html($html)], 'heading');
    }

    public function subheading(mixed $state, bool $html = false): static
    {
        return $this->childComponents([Subheading::make('page_subheading')->state($state)->html($html)], 'subheading');
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

    public function trailing(array|Closure $components): static
    {
        return $this->childComponents($components, 'trailing');
    }

    /** @param list<string> $slots */
    public function hideWhenCompact(array $slots): static
    {
        $allowed = ['leading', 'subheading', 'metadata', 'default', 'trailing', 'badges'];

        if (array_diff($slots, $allowed) !== []) {
            throw new InvalidArgumentException('Unknown compact slot. The heading cannot be hidden.');
        }

        $this->compactHiddenSlots = array_values(array_unique($slots));

        return $this;
    }

    public function isSlotHiddenWhenCompact(string $slot): bool
    {
        return in_array($slot, $this->compactHiddenSlots, true);
    }

    protected function configureChildSchema(Schema $schema, string $key): Schema
    {
        return $schema->columns(1)->dense();
    }

    public function renderSlot(string $name): string
    {
        $schema = $this->getChildSchema($name);

        if ($schema === null || $schema->getComponents() === []) {
            return '';
        }

        return $schema->toHtml();
    }
}
