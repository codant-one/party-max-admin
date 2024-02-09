<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;

use GuzzleHttp\Exception\GuzzleException;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Billing;
use App\Models\ShoppingCart;

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
                    'confirmationUrl' => env('APP_URL').'/api/payments/confirmation',
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

    public function redirectToPayU(Request $request): JsonResponse
    {

        try {
            $client = new \GuzzleHttp\Client();

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/x-www-form-urlencoded'
            ];

            $options = [
                'headers' => $headers,
                'body' => http_build_query($request->all()),
                'allow_redirects' => [
                    'track_redirects' => true
                ],
            ];

            $response = $client->post(env('PAYU_BASE_URI'), $options);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER)[0]
                ]
            ]);

        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody(), true);
                
                return response()->json([
                    'success' => false,
                    'message' => 'error',
                    'exception' => $errorResponse
                ], 500);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'error',
                    'exception' => 'Ha ocurrido un error'
                ], 500);
           
            }
        }
    }

    public function redirectToPayUTesting(Request $request)
    {
        return view('testing.payments');
    }

    public function response(Request $request)
    {
        return view('testing.response');
    }

    public function confirmation(Request $request): JsonResponse
    {

        $request->request->add(['client_id' => $order->client_id ]);
        $order = Order::where('reference_code', $request->reference_sale)->first();

        if (!$order)
            return response()->json([
                'sucess' => false,
                'feedback' => 'not_found',
                'message' => 'Orden no encontrada'
            ], 404);

        switch ($request->response_code_pol) {
            case '1':
                $payment_state_id = 4;
                ShoppingCart::deleteAll($request);
                break;
            case '4':
                $payment_state_id = 3;
                break;
            case '20':
                $payment_state_id = 2;
                break;
            default:
                $payment_state_id = 3;  
            }

        $order->update([
            'payment_state_id' => $payment_state_id
        ]);

        $pse = is_null($request->pse_bank) ? 0 : 1;

        Billing::where('order_id', $order->id)
               ->update([
                    'pse' => $pse,
                    'card_number' => ($pse === 0) ? $request->cc_number : null,
                    'card_name' => ($pse === 0) ? $request->cc_holder : null,
                    'payment_method_name' => ($pse === 0) ? $request->payment_method_name : null
                ]);

        return response()->json([
            'success' => true
        ], 200);
    }
}
