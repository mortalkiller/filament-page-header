<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages;

use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class EmptyPage extends NativePage
{
    use HasPageHeader;
}
