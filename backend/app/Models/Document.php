<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Public methods ****/
    public static function createDocument($request) {
        $document = self::create([
            'document_type_id' => $request->document_type_id,
            'main_document' => $request->main_document,
            'ncc' => $request->ncc
        ]);

        if ($request->hasFile('file_nit')) {
            $file = $request->file('file_nit');

            $path = 'documents/';

            $file_data = uploadFile($file, $path);

            $document->update([
                'file_nit' => $file_data['filePath']
            ]);
        }

        if ($request->hasFile('file_rut')) {
            $file = $request->file('file_rut');

            $path = 'documents/';

            $file_data = uploadFile($file, $path);

            $document->update([
                'file_rut' => $file_data['filePath']
            ]);
        }

        return $document;
    }

}
