<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_info = Storage::disk('local')->get('/json/countries_info.json');
        $countries_info = json_decode($json_info, true);

        if (!file_exists(storage_path('app/public/flags'))) {
            mkdir(storage_path('app/public/flags'), 0755,true);
        } //create a folder

        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/flags');

        $client = new \GuzzleHttp\Client();

        foreach($countries_info as $country){

            $timezone = '';

            $response =  $client->request('GET', 
                'https://restcountries.com/v3.1/alpha/'.strtolower($country['iso']), [
                    'verify' => false, 
                    'http_errors' => false
                ]);

            if($response->getStatusCode() === 200)
                $timezone = json_decode($response->getBody())[0]->timezones[0];
            
            Country::query()->updateOrCreate([
                'id' => $country['id'],
                'iso' => $country['iso'],
                'name' => ucfirst(mb_strtolower($country['name'])),
                'timezone' => $timezone,
                'nicename' => $country['nicename'],
                'iso3' => $country['iso3'],
                'numcode' => $country['numcode'],
                'phonecode' => $country['phonecode'],
                'phone_digits' => $country['phone_digits'],
                'flag' => ($country['flag'] === 'null') ? null : $country['flag']
            ]);

            if($country['flag'] !== 'null')
                copy(public_path('images/'.$country['flag']), storage_path('app/public/').$country['flag']);
        }
    }
}
