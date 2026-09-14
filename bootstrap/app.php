<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        // health-check pillar: Laravel's own built-in health route mechanism.
        // Exactly one health endpoint; it performs no domain/business logic
        // and belongs to no backlog item.
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Foundation-only: no domain-specific middleware is registered here.
        // Route-level access control (the session/auth gate) is a future
        // backlog item's job, per SQ-004 - this shell registers no route to
        // protect yet.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // error-handling pillar: conventions only. This shapes *how* an
        // unhandled exception is logged and rendered for a JSON-expecting
        // caller - it decides no business rule and belongs to no backlog
        // item or domain rule.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            return response()->json([
                'error' => [
                    'message' => app()->isProduction() && $status === 500
                        ? 'Server Error'
                        : $e->getMessage(),
                    'status' => $status,
                ],
            ], $status);
        });
    })->create();
