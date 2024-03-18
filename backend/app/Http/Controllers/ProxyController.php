<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProxyController extends Controller
{
    public function getImage(Request $request) {

        $client = new \GuzzleHttp\Client();
    
        $response = $client->get($request->url, [
            'verify' => false
        ]);
        
        $imageData = $response->getBody()->getContents();

        return response($imageData, 200, [
            'Content-Type' => $response->getHeaderLine('Content-Type'),
            'Content-Length' => strlen($imageData),
        ]);        
    }

    public function getDocument(Request $request) {

        $client = new \GuzzleHttp\Client();
        $url = env('APP_URL').'/storage/'.strtolower($request->file);

        $response = $client->get($url, [
            'verify' => false
        ]);

        if ($response->getStatusCode() == 200) {
            $fileContents = $response->getBody()->getContents();
            $fileSize = strlen($fileContents); 

            $tempPath = 'temp/' . $request->file;
            Storage::disk('local')->put($tempPath, $fileContents);

            return response()->download(storage_path('app/' . $tempPath))->deleteFileAfterSend(true);
        } else {
            return response()->json(['error' => 'Unable to download the file'], 500);
        }
    }

}
