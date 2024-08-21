<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Seeder;

use App\Models\ServiceImage;
use App\Models\Service;
use App\Models\ServiceCategory;
use Spatie\Permission\Models\Permission;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/services/gallery');

        if (!file_exists(storage_path('app/public/services/gallery'))) {
            mkdir(storage_path('app/public/services/gallery'), 0755,true);
        } //create a folder

        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/services/main');

        if (!file_exists(storage_path('app/public/services/main'))) {
            mkdir(storage_path('app/public/services/main'), 0755,true);
        } //create a folder

        Service::factory(10)->create();

        $services = Service::all();
        
        foreach ($services as $service) {
            ServiceImage::factory(['service_id' => $service->id])->create();

            for($i = 0; $i < 4; $i++){
                ServiceCategory::factory(['service_id' => $service->id])->create();
            }
        }
    }
}