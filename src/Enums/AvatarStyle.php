<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Enums;

enum AvatarStyle: string
{
    /** The colour as the background, with a contrasting text colour. */
    case Solid = 'solid';

    /** A light tint of the colour as the background, with the colour itself as the text. */
    case Soft = 'soft';
}
