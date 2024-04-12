<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Color;

class UpdateColors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'colors:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update colors';

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
        self::updateColors();

        return 0;
    }

    private function updateColors() {

        $colors = [
            [
                'name' => 'Azul',
                'color' => '#0000FF',
                'is_gradient' => 0
            ],
            [
                'name' => 'Amarillo',
                'color' => '#FFFF00',
                'is_gradient' => 0
            ],
            [
                'name' => 'Rojo',
                'color' => '#FF0000',
                'is_gradient' => 0
            ],
            [
                'name' => 'Verde',
                'color' => '#008000',
                'is_gradient' => 0
            ],
            [
                'name' => 'Gris',
                'color' => '#808080',
                'is_gradient' => 0
            ],
            [
                'name' => 'Negro',
                'color' => '#000000',
                'is_gradient' => 0
            ],
            [
                'name' => 'Blanco',
                'color' => '#FFFFFF',
                'is_gradient' => 0
            ],
            [
                'name' => 'Morado',
                'color' => '#800080',
                'is_gradient' => 0
            ],
            [
                'name' => 'Rosado',
                'color' => '#FFC0CB',
                'is_gradient' => 0
            ],
            [
                'name' => 'Naranja',
                'color' => '#FFA500',
                'is_gradient' => 0
            ],
            [
                'name' => 'Multicolor',
                'color' => 'linear-gradient(to right, #FF0000, #0000FF, #FFA500, #FFFF00, #008000)',
                'is_gradient' => 1
            ],
            [
                'name' => 'Plateado',
                'color' => 'linear-gradient(to right, #C0C0C0, #FFFFFF, #C0C0C0)',
                'is_gradient' => 1
            ],
            [
                'name' => 'Dorado',
                'color' => 'linear-gradient(to right, #C0C0C0, #FFD700, #FFD700, #C0C0C0)',
                'is_gradient' => 1
            ],
            [
                'name' => 'Oro rosa',
                'color' => 'linear-gradient(to right, #F3D2D4, #FDF6F6, #DE8489, #EDBCBF)',
                'is_gradient' => 1
            ],
            [
                'name' => 'Neón',
                'color' => 'linear-gradient(to right, #00FF1E, #FFFFFF, #00FF1E)',
                'is_gradient' => 1
            ],
            [
                'name' => 'Metalizado',
                'color' => 'linear-gradient(to right, #C0C0C0, #FFFFFF, #FFFFFF, #C0C0C0)',
                'is_gradient' => 1
            ],
            [
                'name' => 'Ninguno',
                'color' => null,
                'is_gradient' => 0
            ],
            [
                'name' => 'Lila',
                'color' => '#C8A2C8',
                'is_gradient' => 0
            ],
            [
                'name' => 'Fucsia',
                'color' => '#FF00FF',
                'is_gradient' => 0
            ],
            [
                'name' => 'Azul Celeste',
                'color' => '#87CEEB',
                'is_gradient' => 0
            ],
            [
                'name' => 'Azul Caribe',
                'color' => '#1E90FF',
                'is_gradient' => 0
            ],
            [
                'name' => 'Verde lima',
                'color' => '#00FF00',
                'is_gradient' => 0
            ]
        ];

        foreach(Color::all() as $key => $color){
            Color::where('name', $colors[$key]['name'])
                ->update([
                    'color' => $colors[$key]['color'],
                    'is_gradient' => $colors[$key]['is_gradient']
                ]);
        }
    }
}
