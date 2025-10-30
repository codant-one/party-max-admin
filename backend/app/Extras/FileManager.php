<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

    if (!function_exists('uploadFile')) {
        function uploadFile($file, $path, $image = '', $disk = 'public', $useOriginalName = false) {
            if($file) {

                $oldFilePath = storage_path('app/public/'.strtolower($image));
                    
                if(File::exists($oldFilePath) && !is_dir($oldFilePath))
                    deleteFile($image);

                // Usar nombre original si se especifica, sino usar random
                if ($useOriginalName) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $file_name = $originalName;
                } else {
                    $file_name = Str::random(25);
                }
                
                $file_type  = $file->getClientOriginalExtension();
                $filePath   = $path . $file_name . '.' .File::guessExtension($file) ;
                Storage::disk($disk)->put($filePath, File::get($file));

                return $file = [
                    'fileName' => $file_name,
                    'fileType' => $file_type,
                    'filePath' => $filePath,
                    'fileSize' => fileSize($file)
                ];
            }
        }
    }   

    if (!function_exists('deleteFile')) {
        function deleteFile($image, $disk = 'public'){
            Storage::disk($disk)->delete($image);
        }
    }

    if (!function_exists('fileSize')) {
        function fileSize($file, $precision = 2){
            $size = $file->getSize();

            if ( $size > 0 ) {
                $size = (int) $size;
                $base = log($size) / log(1024);
                $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
                return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
            }

            return $size;
        }
    }
