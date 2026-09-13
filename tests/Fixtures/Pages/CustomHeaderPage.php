<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages;

use Illuminate\Contracts\View\View;

class CustomHeaderPage extends ExamplePage
{
    public function getHeader(): ?View
    {
        return view('page-header-tests::custom-header');
    }
}
