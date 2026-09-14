<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Components;

use BackedEnum;
use Closure;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;

class MetadataEntry extends TextEntry
{
    protected string|BackedEnum|Closure|null $fieldIcon = null;

    protected IconPosition|Closure $fieldIconPosition = IconPosition::Before;

    protected int|Closure $fieldIconSize = 24;

    public function fieldIcon(string|BackedEnum|Closure|null $icon): static
    {
        $this->fieldIcon = $icon;

        return $this;
    }

    public function fieldIconPosition(IconPosition|Closure $position): static
    {
        $this->fieldIconPosition = $position;

        return $this;
    }

    public function fieldIconSize(int|Closure $size): static
    {
        if (is_int($size) && $size < 1) {
            throw new InvalidArgumentException('The field icon size must be a positive number of pixels.');
        }

        $this->fieldIconSize = $size;

        return $this;
    }

    public function getFieldIconSize(): int
    {
        $size = $this->evaluate($this->fieldIconSize);

        if (! is_int($size) || $size < 1) {
            throw new InvalidArgumentException('The field icon size must be a positive number of pixels.');
        }

        return $size;
    }

    public function getFieldIcon(): string|BackedEnum|null
    {
        return $this->evaluate($this->fieldIcon);
    }

    public function getFieldIconPosition(): IconPosition
    {
        return $this->evaluate($this->fieldIconPosition);
    }

    public function wrapEmbeddedHtml(string $html): string
    {
        $content = parent::wrapEmbeddedHtml($html);
        $icon = $this->getFieldIcon();

        if (blank($icon)) {
            return $content;
        }

        return view('filament-page-header::components.metadata-entry', [
            'content' => new HtmlString($content),
            'icon' => $icon,
            'position' => $this->getFieldIconPosition(),
            'size' => $this->getFieldIconSize(),
        ])->render();
    }
}
