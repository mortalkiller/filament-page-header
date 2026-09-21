<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Enums;

enum HeaderActionsPosition: string
{
    case Start = 'start';
    case End = 'end';
    case Below = 'below';
}
