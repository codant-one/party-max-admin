<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Address;
use App\Models\Document;
use App\Models\SupplierAccount;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function state() {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function gender() {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function document()
    {
        return $this->hasOne(Document::class, 'id', 'document_id');
    }

    public function account()
    {
        return $this->hasOne(SupplierAccount::class, 'supplier_id', 'id');
    }

    /**** Scopes ****/
    public function scopeProductsCount($query)
    {
        return  $query->addSelect(['product_count' => function ($q){
                    $q->selectRaw('COUNT(*)')
                        ->from('suppliers as s')
                        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->where('p.state_id', 3)
                        ->whereColumn('s.id', 'suppliers.id');
                }]);
    }

    public function scopeSales($query)
    {
        return  $query->addSelect(['sales' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('suppliers as s')
                        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->leftJoin('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->leftJoin('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->leftJoin('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->whereColumn('s.id', 'suppliers.id');
                }]);
    }

    public function scopeRetailSales($query)
    {
        return  $query->addSelect(['retail_sales' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('suppliers as s')
                        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->leftJoin('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->leftJoin('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->leftJoin('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->where('o.wholesale', 0)
                        ->whereColumn('s.id', 'suppliers.id');
                }]);
    }

    public function scopeWholesaleSales($query)
    {
        return  $query->addSelect(['wholesale_sales' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('suppliers as s')
                        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->leftJoin('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->leftJoin('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->leftJoin('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->where('o.wholesale', 1)
                        ->whereColumn('s.id', 'suppliers.id');
                }]);
    }

    public function scopeWhereSearch($query, $search) {
        $query->whereHas('user', function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        })->orWhereHas('document', function ($q) use ($search) {
            $q->where('main_document', 'LIKE', '%' . $search . '%');
        })->orWhere('company_name', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderByRaw('(IFNULL('. $orderByField .', id)) '. $orderBy);
    }

    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'order_id';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function scopePaginateData($query, $limit) {
        if ($limit == 'all') {
            return collect(['data' => $query->get()]);
        }

        return $query->paginate($limit);
    }


    /**** Public methods ****/
    public static function createSupplier($request) {
        $user = User::createUser($request);
        $user->assignRole('Proveedor');

        $document = Document::createDocument($request);

        $supplier = self::create([
            'user_id' => $user->id,
            'document_id'=> $document->id,
            'company_name' => $request->company_name,
            'phone_contact' => $request->phone_contact,
            'slug' => Str::slug($user->company_name)
        ]);

        SupplierAccount::createSupplierAccount($request, $supplier->id);
        
        return $supplier;
    }

    public static function updateSupplier($request, $supplier) {

        $user = User::find($supplier->user_id);
        $document = Document::find($supplier->document_id);
        $supplier_account = SupplierAccount::where('supplier_id', $supplier->id)->first();

        $supplier->update([
            'document_id'=> $document->id,
            'company_name' => $request->company_name,
            'phone_contact' => $request->phone_contact
        ]);

        User::updateUser($request, $user);       
        Document::updateDocument($request, $document);
        SupplierAccount::updateSupplierAccount($request, $supplier_account);
        
        return $supplier;
    }

    public static function updateOrCreateStore($request, $user) {
        $supplier = Supplier::updateOrCreate(
            [    'user_id' => $user->id ],
            [
                'slug' => Str::slug($request->store_name),
                'about_us' => $request->about_us,
                'address' => $request->address
            ]
        );

        return $supplier;
    }

    public static function deleteSupplier($id) {
        self::deleteSuppliers(array($id));
    }

    public static function deleteSuppliers($ids) {
        foreach ($ids as $id) {
            $supplier = self::find($id);
            $user = User::find($supplier->user_id);

            $supplier->delete();
            User::deleteUser($user->id);
        }
    }

    public static function updateCommission($request, $supplier)
    {
        if($request->type_commission == 0) {
            $supplier->update([
                'commission' => $request->commission
            ]);      
        } elseif($request->type_commission == 1) {
            $supplier->update([
                'wholesale_commission' => $request->wholesale_commission
            ]);      
        }

        return $supplier;
    }

    public static function updateSales($total, $supplier, $order)
    {
        $supplierAccount = SupplierAccount::where('supplier_id', $supplier->id)->first();

        if($order->wholesale === 1) {//mayor
            $update_sales = ($supplierAccount->wholesale_sales_amount ?? 0) + $total;

            $supplierAccount->update([
                'wholesale_sales_amount' => $update_sales
            ]);
        } else {//detal
            $update_sales = ($supplierAccount->retail_sales_amount ?? 0) + $total;
            $supplierAccount->update([
                'retail_sales_amount' => $update_sales
            ]);
        }

        return $supplierAccount;
    }

    public static function sendInfo($orderId) {

        $order = 
            Order::with([
                'details.product_color.product.user', 
                'details.service',
                'details.cake_size',
                'details.flavor',
                'details.filling',
            ])->find($orderId); 

        $link_send = env('APP_DOMAIN_ADMIN').'/dashboard/admin/orders/'.$orderId;
        $products = [];
        $services = [];

        foreach ($order->details as $detail) {
            if($detail->product_color) {
                $email = $detail->product_color->product->user->email;
                $productInfo = [
                    'product_id' => $detail->product_color->product->id,
                    'product_name' => $detail->product_color->product->name,
                    'product_image' => asset('storage/' . $detail->product_color->product->image),
                    'color' => $detail->product_color->color->name,
                    'slug' => env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                if (!isset($products[$email])) {
                    $products[$email] = [];
                }

                $products[$email][] = $productInfo;
            } else {
                $email = $detail->service->user->email;

                $serviceInfo = [
                    'email' => $email,
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor ? $detail->flavor->name : null,
                    'filling' => $detail->filling ? $detail->filling->name : null,
                    'cake_size' => $detail->cake_size ? $detail->cake_size->name : null,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];

                if (!isset($services[$email])) {
                    $services[$email] = [];
                }

                $services[$email][] = $serviceInfo;
            }
        
        }

        ksort($products);     
        ksort($services);

        foreach($products as $key => $item) {

            $email = $key;
            $subject = 'Tienes un nuevo pedido.';
            $data = [
                'total' => $order->total,
                'products' => $item,
                'link_send' => $link_send,
                'showButton' => true
            ];

            try {
                \Mail::send(
                    'emails.payment.product_sale'
                    , ['data' => $data]
                    , function ($message) use ($email, $subject) {
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email)->subject($subject);
                });
            } catch (\Exception $e){
                $message = 'error';
                $responseMail = $e->getMessage();

                Log::info($message . ' ' . $responseMail);
            } 
        }

        foreach($services as $key => $item) {

            $email = $key;
            $subject = 'Tienes un nuevo pedido.';
            $data = [
                'total' => $order->total,
                'services' => $item,
                'link_send' => $link_send,
                'showButton' => true
            ];

            try {
                \Mail::send(
                    'emails.payment.product_sale'
                    , ['data' => $data]
                    , function ($message) use ($email, $subject) {
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email)->subject($subject);
                });
            } catch (\Exception $e){
                $message = 'error';
                $responseMail = $e->getMessage();

                Log::info($message . ' ' . $responseMail);
            } 
        }
    }

}
