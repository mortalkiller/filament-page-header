<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Enums;

enum BreadcrumbPosition: string
{
    case Outside = 'outside';
    case Inside = 'inside';
    case Hidden = 'hidden';
}
