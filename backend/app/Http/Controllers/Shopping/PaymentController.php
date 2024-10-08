<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;

use GuzzleHttp\Exception\GuzzleException;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Order;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Event;

class PaymentController extends Controller
{
    public function signature(Request $request): JsonResponse
    {
        try {

            $ApiKey = env('PAYU_API_KEY');
            $merchantId = env('PAYU_MERCHANT_ID');
            $amount = $request->amount;
            $currency = 'COP';
           
            $signature = md5($ApiKey.'~'.$merchantId.'~'.$request->referenceCode.'~'.$amount.'~'.$currency);

            return response()->json([
                'success' => true,
                'data' => [
                    'signature' => $signature,
                    'referenceCode' => $request->referenceCode,
                    'merchantId' => env('PAYU_MERCHANT_ID'),
                    'accountId' => env('PAYU_ACCOUNT_ID'),
                    'responseUrl' => env('APP_DOMAIN').'/cart',
                    'confirmationUrl' => env('APP_URL').'/api/payments/confirmation',
                    'test' => env('PAYU_TEST_MODE')
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
        $order = Order::where('reference_code', $request->reference_sale)->first();

        if (!$order)
            return response()->json([
                'sucess' => false,
                'feedback' => 'not_found',
                'message' => 'Pedido no encontrada'
            ], 404);
 
        $event_state_id = 4;
        $payment_state_id = 1;
        $payment_method_type = 2;
        $payment_method = '';
        $pseReference1 = $request->pseReference1;
        $response_message_pol = $request->response_message_pol;
        $message = '';

        switch ($request->response_code_pol) {//4: pagado, 3: fallido, 2: cancelado , 1: pendiente
            case '1':
                $payment_state_id = 4;
                $message = 'Transacción aprobada.';
                $request->request->add(['client_id' => $order->client_id ]);
                Order::updateInventary($order);
                break;
            case '4':
                $payment_state_id = 3;
                $message = 'Transacción rechazada por entidad financiera.';
                break;
            case '5':
                $payment_state_id = 3;
                $message = 'Transacción rechazada por el banco.';
                break;
            case '6':
                $payment_state_id = 3;
                $message = 'Fondos insuficientes.';
                break;
            case '7':
                $payment_state_id = 3;
                $message = 'Tarjeta inválida.';
                break;
            case '8':
                $payment_state_id = 3;
                $message = ($response_message_pol === 'CONTACT_THE_ENTITY') ? 'Contactar a la entidad financiera.' : 'Débito automático no permitido.';
                break;
            case '9':
                $payment_state_id = 3;
                $message = 'Tarjeta vencida.';
                break;
            case '10':
                $payment_state_id = 3;
                $message = 'Tarjeta restringida.';
                break;
            case '12':
                $payment_state_id = 3;
                $message = 'La fecha de expiración o el código de seguridad son inválidos.';
                break;
            case '13':
                $payment_state_id = 3;
                $message = 'Reintentar pago.';
                break;
            case '14':
                $payment_state_id = 3;
                $message = 'Transacción inválida.';
                break;
            case '17':
                $payment_state_id = 3;
                $message = 'El valor excede el máximo permitido por la entidad.';
                break;
            case '19':
                $payment_state_id = 3;
                $message = 'Transacción abandonada por el pagador.';
                break;
            case '22':
                $payment_state_id = 3;
                $message = 'Tarjeta no autorizada para comprar por internet.';
                break;
            case '23':
                $payment_state_id = 3;
                $message = ($response_message_pol === 'ANTIFRAUD_REJECTED') ? 'Transacción rechazada por sospecha de fraude.' : 'Transacción rechazada debido a sospecha de fraude en la entidad financiera.';
                break;
            case '9995':
                $payment_state_id = 3;
                $message = 'Certificado digital no encontrado.';
                break;
            case '9996':
                $payment_state_id = 3;
                $message = ($response_message_pol === 'BANK_UNREACHABLE') ? 'Error tratando de comunicarse con el banco.' : 'No se recibió respuesta de la entidad financiera.';
                break;
            case '9997':
                $payment_state_id = 3;
                $message = 'Error comunicándose con la entidad financiera.';
                break;
            case '9998':
                $payment_state_id = 3;
                $message = 'Transacción no permitida.';
                break;
            case '9999':
                $payment_state_id = 3;
                $message = 'Error interno.';
                break;
            case '20':
                $payment_state_id = 2;
                $message = 'Transacción expirada.';
                break;
            default:
                $payment_state_id = 1;  
                $message = 'Transaccion pendiente.';
        }

        switch ($request->payment_method_type) {
            case '2': //CREDIT_CARD
                $payment_method_type = 2;
                $payment_method = 'Tarjetas de Crédito.';
                break;
            case '4': //PSE
                $payment_method_type = 4;
                $payment_method = 'Transferencias bancarias PSE.';
                break;
            case '5': //ACH
                $payment_method_type = 5;
                $payment_method = 'Débitos ACH.';
                break;
            case '6': //DEBIT_CARD
                $payment_method_type = 6;
                $payment_method = 'Tarjetas débito.';
                break;
            case '7': //CASH
                $payment_method_type = 7;
                $payment_method = 'Efectivo.';
                break;
            case '8': //REFERENCED
                $payment_method_type = 8;
                $payment_method = 'Referencia de pago.';
                break;
            case '10': //BANK_REFERENCED
                $payment_method_type = 10;
                $payment_method = 'Pago en bancos.';
                break;
            case '14': //SPEI
                $payment_method_type = 14;
                $payment_method = 'Transferencias bancarias SPEI.';
                break;
            default:
                $payment_method_type = 2;
                $payment_method = 'Tarjetas de Crédito';
        }

        $order->update([
            'payment_state_id' => $payment_state_id
        ]);
        
        $nequi = $request->payment_method_name === 'NEQUI' ? 1 : 0;
        $pse = is_null($request->pse_bank) ? 0 : 1;

        Billing::where('order_id', $order->id)
               ->update([
                    'reference_pol' => $request->reference_pol,
                    'nequi' => $nequi,
                    'pse' => $pse,
                    'pse_bank' => $request->pse_bank,
                    'pse_reference1' => $request->pse_reference1,
                    'card_number' => ($pse === 0) ? $request->cc_number : null,
                    'card_name' => ($pse === 0) ? $request->cc_holder : null,
                    'payment_method_name' => ($pse === 0) ? $request->payment_method_name : null
                ]);

        switch ($payment_state_id) { //4: pagado, 3: fallido, 2: cancelado , 1: pendiente
            case 2: 
                Client::sendMailError($order->id, 2, $message);  
                Event::updateState(6,$order->id);
                break;
            case 3:
                Client::sendMailError($order->id, 3, $message);
                Event::updateState(6,$order->id);  
                break;
            case 4: 
                Client::sendMail($order->id);
                Client::sendInfo($order->id);
                Supplier::sendInfo($order->id);
                break;           
        }          
                
        return response()->json([
            'success' => true
        ], 200);
    }
}
