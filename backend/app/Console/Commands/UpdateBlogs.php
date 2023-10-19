<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Str;

use App\Models\Blog;

class UpdateBlogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blogs:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update blogs';

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
        self::updateBlogs();

        return 0;
    }

    private function updateBlogs() {

        foreach(Blog::all() as $blog){
            Blog::where('title', $blog->title)
                ->update([
                    'user_id' => 1,
                    'slug' => Str::slug($blog->title)
                ]);
        }
    }
}
