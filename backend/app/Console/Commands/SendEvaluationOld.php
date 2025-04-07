<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use Str;

use App\Models\Order;

class SendEvaluationOld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-evaluation-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send product evaluation old';

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

        $orders = 
            Order::with([
                'billing', 
                'client.user.userDetail',
                'histories' => function($query) {
                    $query->where('shipping_state_id', 3);
                }
            ])
            ->where([
                ['shipping_state_id', 3],
                ['payment_state_id', 4]
            ])
            ->get();

        foreach($orders as $item){
            $this->info('sending order mail #'. $item->id);

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
            
            $link = env('APP_DOMAIN').'/detail-purchases/'.$order->id;
            $subject = '¡Haz que tu opinión cuente en PARTYMAX!';

            if($order->client) {
                $user = $order->client->user->name . ' ' . $order->client->user->last_name;
                $email = $order->client->user->email;
            } else {
                $user = $order->billing->name . ' ' . $order->billing->last_name;
                $email = $order->billing->email;
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

            $register = env('APP_DOMAIN').'/register/form_client';

            $text = 'Hola <strong>'.$user.'</strong>,<br>';
            $text .= 'Tus compras ayudaron a crear momentos increíbles. Y ahora puedes ayudarnos a crear aún más. ¡Tu opinión es clave!✨. <br> ';
            $text .= 'Al dejar tu calificación, no solo guías a otros para que elijan los mejores productos para sus fiestas, sino que también <strong>haces que la celebración sea aún más grande⭐. </strong>';
            $text .= 'Además, al calificarnos, <strong>seguirás disfrutando</strong> de promociones y beneficios exclusivos.<br><br>';
            $text .= '🔹 <strong>¿Aún no estás registrado?</strong><br>';
            $text .= 'Para dejar tu evaluación y acceder a estos beneficios, solo necesitas registrarte en nuestro sistema. ¡Es rápido y fácil!<br>';
            $text .= "<a href='$register' target='_blank'>Haz clic aquí para registrarte</a><br>";
            $text2 = '¡Gracias por ser parte de nuestra comunidad!<br>Saludos';

            $buttonText = 'Califica ahora';

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

                $order->update([ 'survey' => 1]);//enviada

            } catch (\Exception $e){
                $message = 'Error e-mail evaluation: ';
                $responseMail = $e->getMessage();

                Log::info($message . ' ' . $responseMail);
            } 
            
        }

        $this->info('Emails sent');
    }
}
