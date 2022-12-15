<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;

class PaymentController
{
    /**
     * Payment request action.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Application|Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function handle(Request $request)
    {
        Log::debug($request->getContent());

        return response();
    }
}
