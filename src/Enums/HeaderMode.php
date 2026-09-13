<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Enums;

enum HeaderMode: string
{
    case Normal = 'normal';
    case Sticky = 'sticky';
    case Compact = 'compact';
}
