<?php

namespace Packlink\Middleware\Exceptions;

use Exception;
use Google\Cloud\ErrorReporting\Bootstrap;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\JsonResponse;
use Packlink\BusinessLogic\Language\Translator;

class Handler extends BaseHandler
{
    /**
     * Report and log an exception.
     *
     * @param \Exception $e
     *
     * @throws \Exception
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        // log errors using configured logger interface
        parent::report($e);

        if (isset($_SERVER['GAE_SERVICE'])) {
            Bootstrap::init();
            Bootstrap::exceptionHandler($e);
        }
    }

    /**
     * Render an exception into a response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof MaintenanceModeException) {
            return $this->handleMaintenanceModeException();
        }

        return parent::render($request, $e);
    }

    /**
     * Returns either a 503 JSON response or a maintenance page, depending on the request type.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    protected function handleMaintenanceModeException()
    {
        if (request()->header('Accept') === 'application/json') {
            return new JsonResponse(['error' => Translator::translate('Application is in maintenance mode')], 503);
        }

        return response()->view('packlink::pages/maintenance', [], 503);
    }
}
