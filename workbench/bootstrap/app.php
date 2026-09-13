<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php')
    ->withMiddleware()
    ->withExceptions()
    ->create();

$app->useVendorPath(dirname(__DIR__, 2).'/vendor');

return $app;
