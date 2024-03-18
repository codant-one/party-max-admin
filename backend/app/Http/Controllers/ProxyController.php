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
    
        $response = $client->get($url, ['stream' => true, 'verify' => false]);
    
        if ($response->getStatusCode() == 200) {
            $headers = [
                'Content-Type' => $response->getHeaderLine('Content-Type'),
                'Content-Length' => $response->getHeaderLine('Content-Length'),
                'Content-Disposition' => 'attachment; filename="' . basename($request->file) . '"'
            ];
    
            return response()->stream(function () use ($response) {
                echo $response->getBody();
            }, 200, $headers);
        } else {
            return response()->json(['error' => 'Unable to download the file'], 500);
        }
    }

}
