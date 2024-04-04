<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Str;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Blog;
use App\Models\BlogCategory;

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
}
