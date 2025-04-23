<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\ProductColor;

class UpdateProductsStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stock in products';

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
        $products = Product::with(['colors'])->withTrashed()->get();
    
        foreach ($products as $product) {
            foreach($product->colors as $item) {
                $color = ProductColor::find($item->id);
                $color->stock = intval($product->stock) < 0 ? 0 : $product->stock;
                $color->in_stock = $product->in_stock;
                $color->save();
            }
        }

        $this->info('update colors product');
    }
}
