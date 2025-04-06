<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\QuoteDetail;

use Carbon\Carbon;
use PDF;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function details() {
        return $this->hasMany(QuoteDetail::class, 'quote_id', 'id');
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    /**** Public methods ****/
    public static function createQuote($request) {
        $quote = self::create([
            'document_type_id' => $request->document_type_id,
            'name' => $request->name,
            'company' => $request->company === 'null' ? null : $request->company,
            'document' => $request->document,
            'phone' => $request->phone,
            'email' => $request->email,
            'start_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d'),
            'due_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d'),
            'sub_total' => $request->sub_total,
            'tax' => $request->tax,
            'total' => $request->total,
            'type' => $request->type
        ]);

        foreach ($request->product_color_id as $index => $productColorId) {
            $detail = QuoteDetail::create([
                'quote_id' => $quote->id,
                'product_color_id' => $productColorId,
                'price' => $request->price_product[$index],
                'quantity' => $request->quantity_product[$index],
                'total' => $request->price_product[$index] * $request->quantity_product[$index],
            ]);
        }

        foreach ($request->service_id as $index => $serviceId) {
            $detail = QuoteDetail::create([
                'quote_id' => $quote->id,
                'service_id' => $serviceId,
                'cake_size_id' => $request->cake_size_id[$index] === null ? null : $request->cake_size_id[$index],
                'flavor_id' => $request->flavor_id[$index] === null ? null : $request->flavor_id[$index],
                'filling_id' => $request->filling_id[$index] === null ? null : $request->filling_id[$index],
                'price' => $request->price_service[$index],
                'quantity' => $request->quantity_service[$index],
                'total' => $request->price_service[$index] * $request->quantity_service[$index],
            ]); 
        }

        if (!file_exists(storage_path('app/public/pdfs'))) {
            mkdir(storage_path('app/public/pdfs'), 0755,true);
        } //create a folder

        $items = Product::all();
        $total = 0;
        $date = Carbon::now()->timestamp;

        $quote = Quote::with(
            'document_type',
            'details.product_color',
            'details.service',
            'details.cake_size',
            'details.flavor',
            'details.filling'
        )->find($quote->id);

        $products = [];
        $services = [];

        foreach ($quote->details as $detail) {
            if($detail->product_color) {
                $productInfo = [
                    'product_id' => $detail->product_color->product->id,
                    'product_name' => $detail->product_color->product->name,
                    'product_price' => $detail->price,
                    'product_total' => $detail->total * $detail->quantity,
                    'product_image' => asset('storage/' . $detail->product_color->product->image),
                    'color' => $detail->product_color->color->name,
                    'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($products, $productInfo);
            } else {
                $serviceInfo = [
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_price' => $detail->price,
                    'service_total' => $detail->total *  $detail->quantity,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor->name,
                    'filling' => $detail->filling->name,
                    'cake_size' => $detail->cake_size->name,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
        }

        PDF::loadView('pdfs.quote', compact('quote', 'products', 'services'))->save(storage_path('app/public/pdfs').'/'.Str::slug($quote->id).'-'.$date.'.pdf');

        $quote->file = 'pdfs/'.Str::slug($quote->id).'-'.$date.'.pdf';
        $quote->update();

        return $quote;
    }

    public static function deleteQuotes($ids) {
        foreach ($ids as $id) {
            $quote = self::find($id);
            $quote->delete();
        }
    }

}
