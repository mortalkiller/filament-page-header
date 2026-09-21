<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Enums;

enum HeaderPart: string
{
    case Image = 'leading';
    case Description = 'description';
    case Badges = 'badges';
    case Metadata = 'metadata';
    case Summary = 'summary';
    case Content = 'default';
    case Breadcrumbs = 'breadcrumbs';
    case SubNavigation = 'subNavigation';
}
