<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;

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

    public static function updateSupplierAccount($request, $supplierAccount) {

        $supplierAccount->update([
            'type_account' => $request->type_account,
            'name_bank' => $request->name_bank,
            'bank_account' => $request->bank_account
        ]);

        if ($request->hasFile('file_account')) {
            $file = $request->file('file_account');

            $path = 'documents/';

            $file_data = uploadFile($file, $path, $supplierAccount->file_account);

            $supplierAccount->update([
                'file_account' => $file_data['filePath']
            ]);
        }

        return $supplierAccount;
    }

    public static function update_Balance($request, $supplierAccount)
    {
        if($request->type_commission == 2)
        {
            $supplierAccount->update([
                'balance' => $request->balance
            ]);      
        }

        else
        {
            $supplier = Supplier::find($supplierAccount->supplier_id);
            $total_sales = ($supplierAccount->retail_sales_amount ?? 0) + ($supplierAccount->wholesale_sales_amount ?? 0);
            $retail_commission = (($supplier->commission?? 0)/100) * ($supplierAccount->retail_sales_amount ?? 0);
            $wholesale_commission = (($supplier->wholesale_commission?? 0)/100) * ($supplierAccount->wholesale_sales_amount ?? 0);
            $new_balance = $total_sales - $retail_commission - $wholesale_commission;
            $supplierAccount->update([
                'balance' => $new_balance
            ]);      
        }
        return  $supplierAccount;
    }

    public static function update_Sales($user_id, $total)
    {
        $supplier = Supplier::where('user_id',$user_id)->first();

        if (!$supplier) 
        {
            return response()->json([
                'success' => false,
                'info' => "Proveedor no encontrado"
            ], 404);
        } 
        
        else 
        {
            $supplier_a = self::where('supplier_id', $supplier->id);

            if (!$supplier_a) {
                return response()->json([
                    'success' => false,
                    'info' => "No se ha encontrado una cuenta de facturación asociada a este proveedor"
                ], 404); 
            }

            else {
                $update_sales = ($supplier_a->retail_sales_amount ?? 0) + $total;
                $supplier_a->update([
                    'retail_sales_amount' => $update_sales
                ]);
            }
        }

        return $supplier_a;
    }

}
