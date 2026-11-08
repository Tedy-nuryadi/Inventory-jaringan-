<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)

    ->withRouting(

        web: __DIR__.'/../routes/web.php',

        commands: __DIR__.'/../routes/console.php',

        health: '/up',

    )


    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | MIDDLEWARE ALIAS
        |--------------------------------------------------------------------------
        */

        $middleware->alias([

            'role' =>
                \App\Http\Middleware\RoleMiddleware::class,

        ]);


        /*
        |--------------------------------------------------------------------------
        | CEK STATUS USER AKTIF
        |--------------------------------------------------------------------------
        |
        | Middleware ini berjalan pada seluruh request WEB.
        |
        | Jika user sudah login lalu status akunnya diubah menjadi NONAKTIF,
        | user akan otomatis logout pada request berikutnya.
        |
        */

        $middleware->web(
            append: [
                \App\Http\Middleware\EnsureUserIsActive::class,
            ]
        );

    })


    ->withExceptions(function (Exceptions $exceptions): void {

        //

    })

    ->create();