<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\State;

class AddStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'states:add-states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new states to table';

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
        $states = [
            ['name' => 'Inactivo', 'label' =>  'inactivo' ],
            ['name' => 'Activo', 'label' =>  'activo' ],
            ['name' => 'Publicado', 'label' =>  'publicado' ],
            ['name' => 'Pendiente', 'label' =>  'pendiente' ],
            ['name' => 'Eliminado', 'label' =>  'eliminado' ],
            ['name' => 'Rechazado', 'label' =>  'rechazado' ],
            ['name' => 'Entregado', 'label' =>  'entregado' ],
            ['name' => 'En proceso', 'label' =>  'en proceso' ],
            ['name' => 'Pagado', 'label' =>  'pagado' ],
        ];  

        foreach($states as $state){
            State::firstOrCreate(
                $state
            );
        }

        $this->info("Add new states");
       
        return 0;
    }
}
