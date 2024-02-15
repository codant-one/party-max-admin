<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupplierAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Public methods ****/
    public static function createSupplierAccount($request, $supplier_id) {

        $supplierAccount = self::create([
            'supplier_id' => $supplier_id,
            'type_account' => $request->type_account,
            'name_bank' => $request->name_bank,
            'bank_account' => $request->bank_account
        ]);

        if ($request->hasFile('file_account')) {
            $file = $request->file('file_account');

            $path = 'documents/';

            $file_data = uploadFile($file, $path);

            $supplierAccount->update([
                'file_account' => $file_data['filePath']
            ]);
        }

        return $supplierAccount;
    }

}
