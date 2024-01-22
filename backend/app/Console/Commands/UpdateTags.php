<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Str;

use App\Models\Tag;

class UpdateTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tags';

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
        self::updateTags();

        return 0;
    }

    private function updateTags() {

        foreach(Tag::all() as $tag){
            Tag::where('name', $tag->name)->update(['slug' => Str::slug($tag->name)]);
        }
    }
}
