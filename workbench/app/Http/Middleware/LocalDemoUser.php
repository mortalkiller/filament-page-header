<?php

declare(strict_types=1);

namespace Workbench\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Workbench\App\Models\DemoUser;

final class LocalDemoUser
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        auth()->setUser(new DemoUser(['id' => 1, 'name' => 'Demo User', 'email' => 'demo@example.test']));

        return $next($request);
    }
}
