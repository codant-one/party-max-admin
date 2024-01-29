<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function signature(Request $request): JsonResponse
    {
        try {

            $ApiKey = env('PAYU_API_KEY');
            $merchantId = env('PAYU_MERCHANT_ID');
            $referenceCode = 'PARTYMAX-'.$request->client_id.'-'.$request->referenceCode;
            $amount = $request->amount;
            $currency = 'COP';
           
            $signature = md5($ApiKey.'~'.$merchantId.'~'.$referenceCode.'~'.$amount.'~'.$currency);

            return response()->json([
                'success' => true,
                'data' => [
                    'signature' => $signature,
                    'referenceCode' => $referenceCode,
                    'merchantId' => env('PAYU_MERCHANT_ID'),
                    'accountId' => env('PAYU_ACCOUNT_ID'),
                    'responseUrl' => env('APP_DOMAIN').'/cart',
                    'test' => env('PAYU_DEBUG')
                ]
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function redirectToPayU(Request $request)
    {
        return view('testing.payments');
    }

    public function response(Request $request)
    {
        return view('testing.response');
    }

    public function confirmation(Request $request)
    {
        return view('testing.confirmation');
    }
}
