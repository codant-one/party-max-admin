<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Str;

use App\Models\Order;

class SendProductEvaluation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-evaluation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send product evaluation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        self::sendEvaluation();

        return 0;
    }

    private function sendEvaluation() {

        $orders = Order::where('payment_state_id', 4)->get();

        foreach($orders as $item){
            $order = 
                Order::with([
                    'billing', 
                    'details' => function ($query) {
                        $query->where('is_rating', 0);
                    },
                    'details.product_color.product', 
                    'details.service',
                    'client.user.userDetail'
                ])->find($item->id); 

            if($order->client) {
                $user = $order->client->user->name . ' ' . $order->client->user->last_name;
                $email = $order->client->user->email;
                $link = env('APP_DOMAIN').'/detail-purchases/'.$item->id;
                $subject = 'Tu opinión es importante. ¡Califica tu compra!';

                $text = 'Hola <strong>'.$user.'</strong>,<br>';
                $text .= 'Notamos que aún no has calificado los productos que compraste en Partymax. ';
                $text .= 'Tu opinión es clave para ayudarnos a mejorar y ofrecerte un mejor servicio. ';
                $text .= '<strong>Tómate un minuto y déjanos tu valoración</strong>. Además, al calificar, seguirás accediendo a promociones y beneficios exclusivos.';

                $text2 = '¡Gracias por ser parte de nuestra comunidad!<br>Saludos';
                $buttonText = 'Califica ahora';
            } else {
                $user = $order->billing->name . ' ' . $order->billing->last_name;
                $email = $order->billing->email;
                $link = env('APP_DOMAIN').'/register/form_client';
                $subject = '¡Queremos tu opinión!';

                $text = 'Hola <strong>'.$user.'</strong>,<br> Gracias por tu compra en Partymax.';
                $text .= 'Queremos saber tu opinión sobre los productos que adquiriste. ';
                $text .= 'Tu calificación nos ayuda a mejorar y brindarte un mejor servicio. ';
                $text .= 'Para dejar tu opinión, solo necesitas registrarte en nuestra plataforma y acceder a promociones exclusivas que pronto tendremos para ti.';

                $text2 = '¡Esperamos tu valoración!<br>Saludos';
                $buttonText = 'Regístrate y califica aquí';
            }

            $products = [];
            $services = [];
        
            foreach ($order->details as $detail) {
                if($detail->product_color) {
                    $productInfo = [
                        'product_id' => $detail->product_color->product->id,
                        'product_name' => $detail->product_color->product->name,
                        'product_image' => asset('storage/' . $detail->product_color->product->image),
                        'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                    ];
                    
                    array_push($products, $productInfo);
                } else {
                    $serviceInfo = [
                        'service_id' => $detail->service->id,
                        'service_name' => $detail->service->name,
                        'service_image' => asset('storage/' . $detail->service->image),
                        'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    ];
                    
                    array_push($services, $serviceInfo);
                }
            }

            if(count($order->details) > 0) {// si no ha hecho la evalacion, hay listado de items
                $this->info('sending order mail #'. $order->id);
                $data = [
                    'products' => $products,
                    'services' => $services,
                    'link' => $link,
                    'title' => '¿Qué te pareció tu producto? ',
                    'text' => $text,
                    'text2' => $text2,
                    'buttonText' => $buttonText
                ];

                try {
                    \Mail::send(
                        'emails.clients.send_evaluation'
                        , ['data' => $data]
                        , function ($message) use ($email, $subject) {
                            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                            $message->to($email)->subject($subject);
                    });
                } catch (\Exception $e){
                    $message = 'Error e-mail evaluation: ';
                    $responseMail = $e->getMessage();
        
                    Log::info($message . ' ' . $responseMail);
                } 
            }
        }

        $this->info('Emails sent');
    }
}
