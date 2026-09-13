<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Components;

class Subheading extends Heading
{
    public function toEmbeddedHtml(): string
    {
        return view('filament-page-header::components.subheading', ['component' => $this])->render();
    }
}
