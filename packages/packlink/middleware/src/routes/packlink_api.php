<?php /** @noinspection PhpUndefinedMethodInspection */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packlink\Middleware\Http\Middleware\ApiAuthMiddleware;
use Packlink\Middleware\Http\Middleware\BrandDetection;
use Packlink\Middleware\Http\Middleware\CheckMaintenanceMode;
use Packlink\Middleware\Http\Middleware\InitApiLocale;

Route::group(
    [
        'prefix' => 'api',
        'as' => 'api',
    ],
    static function () {
        Route::group(
            ['prefix' => 'v1', 'as' => '.v1'],
            static function () {
                Route::group(
                    ['prefix' => 'state', 'as' => '.state'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.state.get', 'PRO');
                            }
                        )->name('.get');
                    }
                );

                Route::group(
                    ['prefix' => 'login', 'as' => '.login'],
                    static function () {
                        Route::post(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.login.submit', 'PRO');
                            }
                        )->name('.submit');
                    }
                );

                Route::group(
                    ['prefix' => 'region', 'as' => '.regions'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.regions.get', 'PRO');
                            }
                        )->name('.get');
                    }
                );

                Route::group(
                    ['prefix' => 'register', 'as' => '.register'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.register.get', 'PRO');
                            }
                        )->name('.get');
                        Route::post(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.register.submit', 'PRO');
                            }
                        )->name('.submit');
                    }
                );

                Route::group(
                    ['prefix' => 'onboarding', 'as' => '.onboarding'],
                    static function () {
                        Route::group(
                            ['prefix' => 'state', 'as' => '.state'],
                            static function () {
                                Route::get(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute(
                                            $request,
                                            'platform.api.v1.onboarding.state.get',
                                            'PRO'
                                        );
                                    }
                                )->name('.get');
                            }
                        );
                    }
                );

                Route::group(
                    ['prefix' => 'warehouse', 'as' => '.warehouse'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.warehouse.get', 'PRO');
                            }
                        )->name('.get');
                        Route::post(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.warehouse.submit', 'PRO');
                            }
                        )->name('.submit');
                        Route::get(
                            '/countries',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.warehouse.countries', 'PRO');
                            }
                        )->name('.countries');
                    }
                );

                Route::group(
                    ['prefix' => 'parcel', 'as' => '.parcel'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.parcel.get', 'PRO');
                            }
                        )->name('.get');
                        Route::post(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.parcel.submit', 'PRO');
                            }
                        )->name('.submit');
                    }
                );

                Route::group(
                    ['prefix' => 'location', 'as' => '.locations'],
                    static function () {
                        Route::group(['prefix' => 'search', 'as' => '.search'], static function () {
                            Route::post(
                                '',
                                static function (Request $request) {
                                    return Route::sendToRoute($request, 'platform.api.v1.locations.search.submit', 'PRO');
                                }
                            )->name('.submit');
                        });
                    }
                );

                Route::group(
                    ['prefix' => 'configuration', 'as' => '.configurations'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.configurations.get', 'PRO');
                            }
                        )->name('.get');
                    }
                );

                Route::group(
                    ['prefix' => 'systeminfo', 'as' => '.systeminfo'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.systeminfo.get', 'PRO');
                            }
                        )->name('.get');
                        Route::post(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.systeminfo.submit', 'PRO');
                            }
                        )->name('.submit');
                    }
                );

                Route::group(
                    ['prefix' => 'system', 'as' => '.system'],
                    static function () {
                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.system.get', 'PRO');
                            }
                        )->name('.get');
                    }
                );

                Route::group(
                    ['prefix' => 'service', 'as' => '.services'],
                    static function () {
                        Route::group(
                            ['prefix' => 'list', 'as' => '.list'],
                            static function () {
                                Route::get(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.services.list.get', 'PRO');
                                    }
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'active', 'as' => '.active'],
                            static function () {
                                Route::get(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.services.active.get', 'PRO');
                                    }
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'inactive', 'as' => '.inactive'],
                            static function () {
                                Route::get(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.services.inactive.get', 'PRO');
                                    }
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'activate', 'as' => '.activate'],
                            static function () {
                                Route::post(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.services.activate.submit', 'PRO');
                                    }
                                )->name('.submit');
                            }
                        );

                        Route::group(
                            ['prefix' => 'deactivate', 'as' => '.deactivate'],
                            static function () {
                                Route::post(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.services.deactivate.submit', 'PRO');
                                    }
                                )->name('.submit');
                            }
                        );

                        Route::group(
                            ['prefix' => 'task', 'as' => '.task'],
                            static function () {
                                Route::group(
                                    ['prefix' => 'status', 'as' => '.status'],
                                    static function () {
                                        Route::get(
                                            '',
                                            static function (Request $request) {
                                                return Route::sendToRoute($request, 'platform.api.v1.services.task.status.get', 'PRO');
                                            }
                                        )->name('.get');
                                    }
                                );
                            }
                        );

                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.services.get', 'PRO');
                            }
                        )->name('.get');
                        Route::post(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.services.submit', 'PRO');
                            }
                        )->name('.submit');
                    }
                );

                Route::group(['prefix' => 'support', 'as' => '.support'], static function () {
                    Route::get(
                        '',
                        static function (Request $request) {
                            return Route::sendToRoute($request, 'platform.api.v1.support.get', 'PRO');
                        }
                    )->name('.get');
                    Route::post(
                        '',
                        static function (Request $request) {
                            return Route::sendToRoute($request, 'platform.api.v1.support.submit', 'PRO');
                        }
                    )->name('.submit');
                    Route::get(
                        '/delete/account',
                        static function (Request $request) {
                            return Route::sendToRoute($request, 'platform.api.v1.support.delete.account', 'PRO');
                        }
                    )->name('.delete.account');
                });

                Route::group(
                    ['prefix' => 'order', 'as' => '.order'],
                    static function () {
                        Route::group(
                            ['prefix' => 'status', 'as' => '.status'],
                            static function () {
                                Route::group(
                                    ['prefix' => 'map', 'as' => '.map'],
                                    static function () {
                                        Route::get(
                                            '',
                                            static function (Request $request) {
                                                return Route::sendToRoute($request, 'platform.api.v1.order.status.map.get', 'PRO');
                                            }
                                        )->name('.get');
                                    }
                                );
                                Route::group(
                                    ['prefix' => 'notification', 'as' => '.notification'],
                                    static function () {
                                        Route::post(
                                            '',
                                            static function (Request $request) {
                                                return Route::sendToRoute(
                                                    $request,
                                                    'platform.api.v1.order.status.notification.submit',
                                                    'PRO'
                                                );
                                            }
                                        )->name('.submit');
                                    }
                                );
                            }
                        );

                        Route::group(
                            ['prefix' => 'draft', 'as' => '.draft'],
                            static function () {
                                Route::post(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.order.draft.submit', 'PRO');
                                    }
                                )->name('.submit');
                                Route::get(
                                    '',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.order.draft.get', 'PRO');
                                    }
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'label', 'as' => '.label'],
                            static function () {
                                Route::post(
                                    'print',
                                    static function (Request $request) {
                                        return Route::sendToRoute($request, 'platform.api.v1.order.label.print.submit', 'PRO');
                                    }
                                )->name('.print.submit');
                            }
                        );

                        Route::get(
                            '',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.order.list', 'PRO');
                            }
                        )->name('.list');
                        Route::get(
                            'count',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.order.count', 'PRO');
                            }
                        )->name('.count');
                        Route::get(
                            'active',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.order.active.get', 'PRO');
                            }
                        )->name('.active.get');
                        Route::get(
                            'active/count',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.order.active.count', 'PRO');
                            }
                        )->name('.active.count');
                        Route::get(
                            '/order',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.order.get', 'PRO');
                            }
                        )->name('.get');
                        Route::get(
                            'specific',
                            static function (Request $request) {
                                return Route::sendToRoute($request, 'platform.api.v1.order.specific.get', 'PRO');
                            }
                        )->name('.specific.get');
                    }
                );
            }
        );
    }
);

Route::group(
    [
        'prefix' => 'platform/{platform}',
        'as' => 'platform.',
    ],
    static function () {
        Route::group(
            [
                'prefix' => 'api',
                'as' => 'api',
                'middleware' => [
                    BrandDetection::class,
                    CheckMaintenanceMode::class,
                    ApiAuthMiddleware::class,
                    InitApiLocale::class,
                ],
            ],
            static function () {
                Route::group(
                    ['prefix' => 'v1', 'as' => '.v1'],
                    static function () {
                        Route::group(
                            ['prefix' => 'state', 'as' => '.state'],
                            static function () {
                                Route::get('', 'Packlink\Middleware\Http\Controllers\API\V1\StateController@getState')
                                    ->name(
                                        '.get'
                                    );
                            }
                        );

                        Route::group(
                            ['prefix' => 'login', 'as' => '.login'],
                            static function () {
                                Route::post('', 'Packlink\Middleware\Http\Controllers\API\V1\LoginController@login')
                                    ->name(
                                        '.submit'
                                    );
                            }
                        );

                        Route::group(
                            ['prefix' => 'region', 'as' => '.regions'],
                            static function () {
                                Route::get(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\RegionsController@getRegions'
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'register', 'as' => '.register'],
                            static function () {
                                Route::get('', 'Packlink\Middleware\Http\Controllers\API\V1\RegisterController@get')
                                    ->name('.get');
                                Route::post('', 'Packlink\Middleware\Http\Controllers\API\V1\RegisterController@submit')
                                    ->name('.submit');
                            }
                        );

                        Route::group(
                            ['prefix' => 'onboarding', 'as' => '.onboarding'],
                            static function () {
                                Route::group(
                                    ['prefix' => 'state', 'as' => '.state'],
                                    static function () {
                                        Route::get(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\OnboardingStateController@get'
                                        )->name('.get');
                                    }
                                );
                            }
                        );

                        Route::group(
                            ['prefix' => 'warehouse', 'as' => '.warehouse'],
                            static function () {
                                Route::get('', 'Packlink\Middleware\Http\Controllers\API\V1\WarehouseController@get')
                                    ->name('.get');
                                Route::post(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\WarehouseController@submit'
                                )->name('.submit');
                                Route::get(
                                    '/countries',
                                    'Packlink\Middleware\Http\Controllers\API\V1\WarehouseController@getSupportedCountries'
                                )->name('.countries');
                            }
                        );

                        Route::group(
                            ['prefix' => 'parcel', 'as' => '.parcel'],
                            static function () {
                                Route::get('', 'Packlink\Middleware\Http\Controllers\API\V1\ParcelController@get')
                                    ->name('.get');
                                Route::post('', 'Packlink\Middleware\Http\Controllers\API\V1\ParcelController@submit')
                                    ->name('.submit');
                            }
                        );

                        Route::group(
                            ['prefix' => 'location', 'as' => '.locations'],
                            static function () {
                                Route::group(
                                    ['prefix' => 'search', 'as' => '.search'],
                                    static function () {
                                        Route::post(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\LocationsController@search'
                                        )->name('.submit');
                                    }
                                );
                            }
                        );

                        Route::group(
                            ['prefix' => 'configuration', 'as' => '.configurations'],
                            static function () {
                                Route::get(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\ConfigurationController@get'
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'systeminfo', 'as' => '.systeminfo'],
                            static function () {
                                Route::get(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\SystemInfoController@getStatus'
                                )->name('.get');
                                Route::post(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\SystemInfoController@submitStatus'
                                )->name('.submit');
                            }
                        );

                        Route::group(
                            ['prefix' => 'system', 'as' => '.system'],
                            static function () {
                                Route::get(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\SystemController@get'
                                )->name('.get');
                            }
                        );

                        Route::group(
                            ['prefix' => 'service', 'as' => '.services'],
                            static function () {
                                Route::group(
                                    ['prefix' => 'list', 'as' => '.list'],
                                    static function () {
                                        Route::get(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@list'
                                        )->name('.get');
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'active', 'as' => '.active'],
                                    static function () {
                                        Route::get(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@getActive'
                                        )->name('.get');
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'inactive', 'as' => '.inactive'],
                                    static function () {
                                        Route::get(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@getInactive'
                                        )->name('.get');
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'activate', 'as' => '.activate'],
                                    static function () {
                                        Route::post(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@activate'
                                        )->name('.submit');
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'deactivate', 'as' => '.deactivate'],
                                    static function () {
                                        Route::post(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@deactivate'
                                        )->name('.submit');
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'task', 'as' => '.task'],
                                    static function () {
                                        Route::group(
                                            ['prefix' => 'status', 'as' => '.status'],
                                            static function () {
                                                Route::get(
                                                    '',
                                                    'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@getTaskStatus'
                                                )->name('.get');
                                            }
                                        );
                                    }
                                );

                                Route::get(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@get'
                                )->name('.get');
                                Route::post(
                                    '',
                                    'Packlink\Middleware\Http\Controllers\API\V1\ShippingServiceController@update'
                                )->name('.submit');
                            }
                        );

                        Route::group(
                            ['prefix' => 'support', 'as' => '.support'],
                            static function () {
                                Route::get('', 'Packlink\Middleware\Http\Controllers\API\V1\SupportController@get')
                                    ->name('.get');
                                Route::post('', 'Packlink\Middleware\Http\Controllers\API\V1\SupportController@post')
                                    ->name('.submit');
                                Route::get(
                                    '/delete/account',
                                    'Packlink\Middleware\Http\Controllers\API\V1\SupportController@deleteAccount'
                                )->name('.delete.account');
                            }
                        );

                        Route::group(
                            ['prefix' => 'order', 'as' => '.order'],
                            static function () {
                                Route::group(
                                    ['prefix' => 'status', 'as' => '.status'],
                                    static function () {
                                        Route::group(
                                            ['prefix' => 'map', 'as' => '.map'],
                                            static function () {
                                                Route::get(
                                                    '',
                                                    'Packlink\Middleware\Http\Controllers\API\V1\OrderStatusMapController@get'
                                                )->name('.get');
                                            }
                                        );
                                        Route::group(
                                            ['prefix' => 'notification', 'as' => '.notification'],
                                            static function () {
                                                Route::post(
                                                    '',
                                                    'Packlink\Middleware\Http\Controllers\API\V1\OrderStatusMapController@setNotifiedStatuses'
                                                )->name('.submit');
                                            }
                                        );
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'draft', 'as' => '.draft'],
                                    static function () {
                                        Route::post(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\DraftsController@create'
                                        )->name('.submit');
                                        Route::get(
                                            '',
                                            'Packlink\Middleware\Http\Controllers\API\V1\DraftsController@get'
                                        )->name('.get');
                                    }
                                );

                                Route::group(
                                    ['prefix' => 'label', 'as' => '.label'],
                                    static function () {
                                        Route::post(
                                            'print',
                                            'Packlink\Middleware\Http\Controllers\API\V1\LabelsController@print'
                                        )->name('.print.submit');
                                    }
                                );

                                Route::get('', 'Packlink\Middleware\Http\Controllers\API\V1\OrdersController@list')
                                    ->name('.list');
                                Route::get(
                                    'count',
                                    'Packlink\Middleware\Http\Controllers\API\V1\OrdersController@getCount'
                                )->name('.count');
                                Route::get(
                                    'active',
                                    'Packlink\Middleware\Http\Controllers\API\V1\OrdersController@active'
                                )->name('.active.get');
                                Route::get(
                                    'active/count',
                                    'Packlink\Middleware\Http\Controllers\API\V1\OrdersController@getActiveCount'
                                )->name('.active.count');
                                Route::get('/order', 'Packlink\Middleware\Http\Controllers\API\V1\OrdersController@get')
                                    ->name('.get');
                                Route::get(
                                    'specific',
                                    'Packlink\Middleware\Http\Controllers\API\V1\OrdersController@specific'
                                )->name('.specific.get');
                            }
                        );
                    }
                );
            }
        );
    }
);
