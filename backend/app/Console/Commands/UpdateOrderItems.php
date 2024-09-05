<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Product;

class UpdateOrderItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-order-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update order in items';

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
        self::updateProductList();

        return 0;
    }

    private function updateProducts() {

        $order_id = Product::latest('order_id')
                           ->first()
                           ->order_id ?? 0;

        $products = Product::withTrashed()->whereNull('order_id')->get();

        foreach ($products as $item) {
            $product = Product::withTrashed()->find($item->id);
            $product->order_id = $order_id + 1;
            $product->save();
            $order_id++;
        }
    }

    private function updateProductList() {

        $products = 
            Product::with(['colors.categories'])
                   ->withTrashed()
                   ->get()
                   ->map(function ($item) {
                            
                        $categories = $item->colors->flatMap(function ($color) {
                            return $color->categories->pluck('category_id');
                        })->unique()->values();

                        $product = [];
                        $product['id'] = $item->id;
                        $product['categories'] = $categories;
                            
                        return $product;
                   })
                   ->values()
                   ->all();


        // $this->info(json_encode($products, JSON_PRETTY_PRINT));
    
        foreach ($products as $item) {
            $this->info($item['id']);
            // $product = Product::withTrashed()->find($item->id);
            // $product->order_id = $order_id + 1;
            // $product->save();
            // $order_id++;
        }
    }
}
