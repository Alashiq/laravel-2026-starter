<?php

use App\Features\Admin\v1\Middleware\AuthAdminMiddleware;
use App\Features\Admin\v1\Middleware\CheckAdminRoleMiddleware;
use App\Features\App\v1\Middleware\AuthAppMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::namespace('AdminV1Routes')->name('admin.')->prefix('api/v1/admin')->group(base_path('app/Features/Admin/V1/Routes/api.php'));
            Route::namespace('AppV1Routes')->name('app.')->prefix('api/v1/app')->group(base_path('app/Features/App/V1/Routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'type.user' => AuthAppMiddleware::class,
            'type.admin' => AuthAdminMiddleware::class,
            'check.role' => CheckAdminRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (RouteNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'قم بعمل تسجيل دخول مجددا'
            ], 401);
        });
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت الجلسة الخاصة بك, أعد عمل تسجيل دخول'
            ], 401);
        }); //ThrottleRequestsException
        $exceptions->render(function (ThrottleRequestsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد ضغط حاليا على التطبيق الرجاء المحاولة بعد دقيقة'
            ], 400);
        });
    })->create();
