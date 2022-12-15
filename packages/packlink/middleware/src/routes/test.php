<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/test',
    static function () {
        return view('packlink::test');
    }
);

Route::group(
    [
        'prefix' => 'platform/{platform}',
        'as' => 'platform.',
    ],
    static function () {
        Route::get(
            '/test',
            static function () {
                return view('packlink::test');
            }
        );
    }
);