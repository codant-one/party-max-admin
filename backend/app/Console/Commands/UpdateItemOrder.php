<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Str;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductList;

class UpdateItemOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'item-order:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update items order';

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
        self::updateFaqs();
        self::updateBlogs();
        self::updateProducts();

        return 0;
    }

    private function updateFaqs() {

        $categories = FaqCategory::all()->pluck('id');
        
        foreach($categories as $id){
            $faqs = Faq::where('faq_category_id', $id)->get();

            foreach($faqs as $key => $faq) {
                Faq::where('id', $faq->id)->update(['order_id' => $key + 1]);
            }
        }
    }

    private function updateBlogs() {

        $categories = BlogCategory::all()->pluck('id');
        
        foreach($categories as $id){
            $blogs = Blog::where('blog_category_id', $id)->get();

            foreach($blogs as $key => $blog) {
                Blog::where('id', $blog->id)->update(['order_id' => $key + 1]);
            }
        }
    }

    private function updateProducts() {

        $products = Product::with('colors.categories')->get();
        
        $products->each(function ($product) {
            $cat = collect($product->colors)
                ->flatMap(function ($color) {
                    return $color->categories;
                })
                ->unique('category_id')
                ->values()
                ->toArray();
        
            $product->categories = $cat;
        });


        foreach($products as $product){
            foreach($product->categories as $key => $category) {
                ProductList::create([
                    'product_id' => $product->id,
                    'category_id' => $category['category_id']
                ]);
            }
        }

        $categories = Category::all()->pluck('id');
        
        foreach($categories as $id){
            $products = ProductList::where('category_id', $id)->get();

            foreach($products as $key => $product) {
                ProductList::where('id', $product->id)->update(['order_id' => $key + 1]);
            }
        }

    }
}
