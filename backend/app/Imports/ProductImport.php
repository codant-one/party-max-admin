<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\ProductColor;
use App\Models\Referral;
use App\Models\ReferralDetail;

use Carbon\Carbon;

class ProductImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row[0]) || empty($row[0]) || !isset($row[1]) || empty($row[1])) {
            return null;
        }

        $productColor = ProductColor::with(['product'])->where('sku', $row[0])->first();

        if($productColor) {

            $exist = 
                Referral::where(
                    'user_id', $productColor->product->user_id
                )
                ->whereDate('date', Carbon::today())
                ->first();

            $referral = $exist ?? new Referral;
            $referral->user_id = $productColor->product->user_id;
            $referral->date = $referral->exists ? $referral->date : Carbon::today();

            $referral->save();

            ReferralDetail::updateOrCreate(
                [   
                    'referral_id' => $referral->id,
                    'product_color_id' => $productColor->id
                ],
                [ 
                    'quantity' => $row[1] 
                ]
            );   
        }

        return null;
    }
}
