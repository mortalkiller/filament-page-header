<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Components;

use Closure;
use Filament\Infolists\Components\Entry;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Concerns\HasIcon;
use Illuminate\Support\HtmlString;

class Heading extends Entry implements HasEmbeddedView
{
    use HasIcon;

    protected bool|Closure $isHtml = false;

    public function html(bool|Closure $condition = true): static
    {
        $this->isHtml = $condition;

        return $this;
    }

    public function getContent(): HtmlString
    {
        $state = (string) ($this->getState() ?? $this->getPlaceholder() ?? '');

        return new HtmlString($this->evaluate($this->isHtml) ? $state : e($state));
    }

    public function wrapEmbeddedHtml(string $html): string
    {
        return $html;
    }

    public function toEmbeddedHtml(): string
    {
        return view('filament-page-header::components.heading', ['component' => $this])->render();
    }
}
