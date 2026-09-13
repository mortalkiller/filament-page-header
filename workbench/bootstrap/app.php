<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

// Testbench supplies its own isolated base path when it discovers this file.
$builder = Application::configure(basePath: $APP_BASE_PATH ?? dirname(__DIR__))
    ->withMiddleware()
    ->withExceptions();

if (! isset($APP_BASE_PATH)) {
    $builder->withRouting(web: __DIR__.'/../routes/web.php');
}

return $builder->create();
