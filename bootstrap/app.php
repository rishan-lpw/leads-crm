<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\AssignLeadsCron;
use App\Console\Commands\AssignNewLeadsCron;
use App\Console\Commands\LeadStatusManageCron;
use App\Console\Commands\UpdateLeadStatuses;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        AssignLeadsCron::class,
        AssignNewLeadsCron::class,
        LeadStatusManageCron::class,
        UpdateLeadStatuses::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
