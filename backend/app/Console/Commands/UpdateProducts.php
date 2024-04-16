<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Product;

class UpdateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update prices in products';

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
        self::updateProducts();

        return 0;
    }

    private function updateProducts() {
        $products = Product::all();
    
        foreach ($products as $product) {
            $product->wholesale = is_null($product->wholesale_price) ? 0 : 1;
            $product->save();
        }
    }
}
