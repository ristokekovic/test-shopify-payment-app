<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packlink\Middleware\Http\Middleware\BrandDetection;
use Packlink\Middleware\Http\Middleware\CheckMaintenanceMode;


Route::post(
    'packlink/async/asyncprocess/guid/{guid}',
    'Packlink\Middleware\Http\Controllers\AsyncProcessController@run'
)->name('async')->middleware([CheckMaintenanceMode::class]);

Route::post(
    'packlink/webhook',
    static function (Request $request) {
        /** @noinspection PhpUndefinedMethodInspection */
        return Route::sendToRoute($request, 'platform.webhook', 'PRO');
    }
)->name(
    'webhook'
);

Route::group(
    [
        'prefix' => 'platform/{platform}',
        'as' => 'platform.',
    ],
    static function () {
        Route::middleware([BrandDetection::class, CheckMaintenanceMode::class])->group(
            static function () {
                Route::post(
                    'packlink/webhook',
                    'Packlink\Middleware\Http\Controllers\WebhooksController@handle'
                )->name('webhook');
            }
        );
    }
);