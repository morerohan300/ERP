<?php

use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SetCurrentCompany;
use App\Http\Middleware\SetLocaleAndTimezone;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company' => SetCurrentCompany::class,
            'module' => EnsureModuleEnabled::class,
            'active' => EnsureUserIsActive::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            SetLocaleAndTimezone::class,
        ]);

        // Company must be resolved before route-model binding so tenant
        // global scopes apply to bound models.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: SetCurrentCompany::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // RFC 7807-style problem details for API errors.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'type' => 'validation_error',
                    'title' => 'The given data was invalid.',
                    'status' => 422,
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return response()->json(['type' => 'forbidden', 'title' => 'This action is unauthorized.', 'status' => 403], 403);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json(['type' => 'not_found', 'title' => 'Resource not found.', 'status' => 404], 404);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['type' => 'unauthenticated', 'title' => 'Unauthenticated.', 'status' => 401], 401);
            }

            return null;
        });
    })->create();
