<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;

use GuzzleHttp\Exception\GuzzleException;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

use App\Models\Order;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Event;
use App\Models\ClientIp;
use App\Models\ClientRegistration;
use App\Models\Country;
use App\Models\Coupon;

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
                'message' => 'Pedido no encontrado'
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
                $request->request->add(['client_id' => $order->client_id]);
                Order::updateInventary($order);
                Coupon::updateState($order);
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
            'coupon_id' => $payment_state_id !== 4 ? null : $order->coupon_id,
            'payment_state_id' => $payment_state_id,
            'response_code_pol' => $request->response_code_pol
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
                Event::updateState(6,$order->id);

                if($request->response_code_pol !== '23')
                    Client::sendMailError($order->id, 2, $message);
                break;
            case 3:
                Event::updateState(6,$order->id);

                if($request->response_code_pol !== '23')
                    Client::sendMailError($order->id, 3, $message);
                break;
            case 4: 
                Client::sendMail($order->id);
                Client::sendInfo($order->id);
                Supplier::sendInfo($order->id);
                break;           
        }          
               
        $responseContent = $request->getContent();
        parse_str($responseContent, $responseArray);

        $this->generateLog($order, $responseArray);
        $this->generateIpInfo($order, $request, $message);

        return response()->json([
            'success' => true
        ], 200);
    }

    private function generateLog($order, $response){
        
        if (!file_exists(storage_path('logs/payments'))) {
            mkdir(storage_path('logs/payments'), 0755, true);
        }

        $logPath = storage_path("logs/payments/{$order->reference_code}.log");

        $log = Log::build([
            'driver' => 'single',
            'path' => $logPath,
            'level' => 'debug',
        ]);

        $log->info('Date:'. now());
        $log->info('PayU response: ' . json_encode($response, JSON_PRETTY_PRINT));
    }

    private function generateIpInfo($order, $request, $message){
        
        $agent = new Agent();

        if (!is_null($order->user_agent)) {
            $agent->setUserAgent($order->user_agent);
        }

        if ($agent->isPhone()) {
            $deviceType = "Phone";
        } elseif ($agent->isTablet()) {
            $deviceType = "Tablet";
        } elseif ($agent->isMobile()) {
            $deviceType = "Mobile";
        } elseif ($agent->isDesktop()) {
            $deviceType = "Desktop";
        } else {
            $deviceType = "Unknown";
        }

        $device = $deviceType . ($agent->device() ? ': '. $agent->device() : '');
        $plataform = $agent->platform();
        $plataform.= ' ' . $agent->version($plataform);
        $browser = $agent->browser();
        $browser.= ' ' . $agent->version($browser);
        $is_bot = $agent->isRobot();
        $ip = ($request->has("ip") && !empty($request->ip)) ? $request->ip : $request->ip();
        $location = file_get_contents('http://ipinfo.io/'.$ip.'/geo');

        if (!empty($location)){
            $json = json_decode($location, true);

            if(isset($json['country'])) {
                $country = Country::where('iso', $json['country'])->first()->name;
            }

            $location = isset($json['country']) ? $country.' - '.$json['region'].' - '.$json['city'] : 'Local';
            $postal_code = $json['postal'] ?? '';
            $coordinates = $json['loc'] ?? '';
            $timezone = $json['timezone'] ?? '';
        }

        $registration_number = ClientIp::where('ip', $ip)->first()->registration_number ?? 0;

        ClientIp::updateOrCreate(
            [  'ip' => $ip ],
            [ 
                'client_id' => $order->client_id, 
                'device' => $device, 
                'plataform' => $plataform, 
                'browser' => $browser, 
                'is_bot' => $is_bot, 
                'location' => $location, 
                'postal_code' => $postal_code, 
                'coordinates' => $coordinates, 
                'timezone' => $timezone,
                'registration_number' => $registration_number + 1
            ]
        );

        ClientRegistration::create([
            'ip_id' =>  ClientIp::where('ip', $ip)->first()->id,
            'response_code_pol' => $request->response_code_pol,
            'reference_code' => $order->reference_code,
            'message' => $message,
            'date' => now(),
        ]);

        $count = ClientRegistration::where([
            ['ip_id', ClientIp::where('ip', $ip)->first()->id],
            ['response_code_pol', '23'] //fraude
        ])->count();

        if($count >= 3) {// block ip automatically
            $client_ip = ClientIp::where('ip', $ip)->first();
            $client_ip->is_blocked = 1;
            $client_ip->save();
        }

    }
}
