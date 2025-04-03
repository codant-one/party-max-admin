<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use Str;

use App\Models\Order;

class SendSurvey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-survey';

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

        $date = '2025-04-01';
        $twoDatesBefore = Carbon::now()->subDays(2)->startOfDay();//hace dos dias

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
                ['payment_state_id', 4],
                ['survey', 0]
            ])
            ->whereDate('date', '>=', $date)
            ->whereHas('histories', function($query) use ($twoDatesBefore) {
                $query->where('shipping_state_id', 3)
                      ->where('created_at', '<=', $twoDatesBefore);
            })
            ->get();

        foreach($orders as $item){
            $this->info('sending order mail #'. $item->id);

            $order = 
                Order::with([
                    'billing',
                    'client.user.userDetail'
                ])->find($item->id); 
            
            $link = 'https://docs.google.com/forms/d/1m3TVPc3rD2ECSnx4B2A6ZQqkCxFwdN-NSFR-UNcEJ5A/edit';
            $subject = 'Tu opinión es importante para nosotros. Cuéntanos tu experiencia';

            if($order->client) {
                $user = $order->client->user->name . ' ' . $order->client->user->last_name;
                $email = $order->client->user->email;
            } else {
                $user = $order->billing->name . ' ' . $order->billing->last_name;
                $email = $order->billing->email;
            }

            $text = 'Hola <strong>'.$user.'</strong>,<br>';
            $text .= 'Gracias por confiar en PartyMax. Queremos asegurarnos de que tu experiencia de compra haya sido excelente y, para ello, tu opinión es clave. ';
            $text .= 'Te invitamos a completar nuestra encuesta de satisfacción. Solo te tomará menos de 2 minutos y nos ayudará a seguir mejorando para ofrecerte el mejor servicio posible.';

            $text2 = 'Valoramos mucho tu tiempo y tu opinión.<br> ¡Gracias por ayudarnos a mejorar!';

            $buttonText = '✏️ Responder encuesta';

            $data = [
                'link' => $link,
                'title' => '¿Nos ayudarías con tu opinión?',
                'text' => $text,
                'text2' => $text2,
                'buttonText' => $buttonText
            ];
            
            try {
                \Mail::send(
                    'emails.clients.send_survey'
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

        $this->info('Emails sent');
    }
}
