<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function redirectToPayU(Request $request)
    {
        return view('testing.payments');
    }

    public function response(Request $request)
    {
        return view('testing.response');
    }
}
