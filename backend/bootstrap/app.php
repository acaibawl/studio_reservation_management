<?php

declare(strict_types=1);

use App\Exceptions\UserDisplayableException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (UserDisplayableException $exception, Request $request) {
            return response()->json([
                'message' => $exception::MESSAGE,
            ], $exception::HTTP_STATUS_CODE);
        });
    })->create();
