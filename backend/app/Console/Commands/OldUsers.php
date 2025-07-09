<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use Str;

use App\Models\OldUser;

class OldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'old-users:send-form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send form to old users';

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
        self::sendForm();

        return 0;
    }

    private function sendForm() {

        $users = OldUser::all();
        $count = 0;

        foreach($users as $user){
            $this->info('sending mail #'. $user->first_name . ' ' . $user->last_name);
            
            $email = $user->email;
            $link = 'https://docs.google.com/forms/d/e/1FAIpQLSdlWHxJpLQRQa9koGYwjCpCM8U4c-gyaogPff6vHo226zAxKQ/viewform';
            $subject = '🎉 ¡Tu próxima fiesta empieza con un 10% de alegría!';

            $text = 'Hola <strong>'. $user->first_name . ' ' . $user->last_name .'</strong>🎈,<br>';
            $text .= 'En Party Max, celebrar tiene premio 🎁. <br> ';
            $text .= 'Solo falta un paso: completa este formulario y recibe un 10% de descuento en tu primera compra.<br>';
            $text .= '✨ Productos únicos, servicios increíbles y todo lo que necesitas para que tu evento sea inolvidable… ¡en un solo lugar!<br>';
    
            $text2 = '(Fácil, rápido y sin compromiso)<br><br>';
            $text2 .= 'Nos encanta ser parte de tu próxima celebración 🎊<br>';
            $text2 .= 'Party Max<br>';
            $text2 .= 'Donde la fiesta comienza';
    
            $buttonText = '✏️ Completar formulario';

            $data = [
                'link' => $link,
                'title' => '',
                'text' => $text,
                'text2' => $text2,
                'buttonText' => $buttonText
            ];
            
            try {
                \Mail::send(
                    'emails.clients.old_users'
                    , ['data' => $data]
                    , function ($message) use ($email, $subject) {
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email)->subject($subject);
                });

                $count++;

            } catch (\Exception $e){
                $message = 'Error e-mail evaluation: ';
                $responseMail = $e->getMessage();

                Log::info($message . ' ' . $responseMail);
            } 
            
        }

        $this->info('Emails sent ('.$count.')');
    }
}
