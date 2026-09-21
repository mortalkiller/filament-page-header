<?php

declare(strict_types=1);

use MortalKiller\FilamentPageHeader\Tests\TestCase;

uses(TestCase::class)->in('Feature');

function navigationDom(string $html): DOMXPath
{
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);

    return new DOMXPath($document);
}
