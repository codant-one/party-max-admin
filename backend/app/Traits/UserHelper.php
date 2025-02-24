<?php

namespace App\Traits;

use App\Models\UserDetails;
use App\Models\UserMenu;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\ProductLike;
use App\Models\Product;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Carbon\Carbon;

/**
 * Trait for models with stores
 */
trait UserHelper
{
    /**** Relationship ****/
    public function menu(){
        return $this->hasOne(UserMenu::class, 'user_id');
    }

    public function userDetail() {
        return $this->hasOne(UserDetails::class, 'user_id', 'id');
    }

    public function client(){
        return $this->hasOne(Client::class, 'user_id', 'id');
    }

    public function supplier() {
        return $this->hasOne(Supplier::class, 'user_id', 'id');
    }

    public function favorites() {
        return $this->hasMany(ProductLike::class, 'user_id', 'id');
    }

    public function products() {
        return $this->hasMany(Product::class, 'user_id');
    }

    /**** Public methods ****/
    public function getOnlineAttribute($value) {
        if($value!=null)
            return $this->asDateTime($value);
        else
            return $value;
    }

    public static function updateProfile($request, $user) {
        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'full_profile' => true
        ]);

        UserDetails::updateOrCreateUser($request, $user);
        
        return $user;
    }

    public static function createUser($request) {
        $user = self::create([
            'name' => $request->name,
            'last_name' =>  $request->last_name,
            'username' => Str::slug($request->name . ' ' . $request->last_name),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password)
        ]);

        //Si NO es cliente se evalua la existencia del Rol.
        if (!request('is_client'))
            $user->syncRoles($request->roles);

        UserDetails::updateOrCreateUser($request, $user);

        return $user;
    }

    public static function updateUser($request, $user) {
        $user->update([
            'name' => $request->name,
            'last_name' =>  $request->last_name,
            'username' => Str::slug($request->name . ' ' . $request->last_name),
            'email' => strtolower($request->email)
        ]);

        //Si NO es cliente se evalua la existencia del Rol.
        if (!request('is_client') && isset($request->roles)){
            $user->roles()->detach();  
            $user->syncRoles($request->roles);
        }
        
        UserDetails::updateOrCreateUser($request, $user);

        return $user;
    }

    public static function deleteUser($id) {
        $user = self::find($id);
        $user->roles()->detach();
        $user->delete();
    }

    public static function getOnline($request) {

        $users = self::select('id','online')
                     ->whereIn('id', explode(',', $request->ids))
                     ->get();

        return $users;
    }

    public static function updateProfileClient($request, $user) {
        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'full_profile' => 1
        ]);

        UserDetails::updateOrCreateClient($request, $user);
        
        return $user;
    }

    public static function updateAvatar($request, $user) {

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $path = 'avatars/';

            $file_data = uploadFile($image, $path, $user->avatar);

            $user->update([
                'avatar' => $file_data['filePath']
            ]);
        }
        
        return $user;
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        foreach (explode(' ', $search) as $term) {
            $query->whereHas('roles', function ($q) use ($term) {
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'LIKE', '%' . $term . '%');
                });
            })
            ->orWhere('name', 'LIKE', '%' . $term . '%')
            ->orWhere('email', 'LIKE', '%' . $term . '%');
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderBy($orderByField, $orderBy);
    }
    
    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'created_at';
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

    public function scopeProductsCount($query)
    {
        return  $query->addSelect(['product_count_published' => function ($q){
                    $q->selectRaw('COUNT(*)')
                        ->from('users as u')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->where('p.state_id', 3)
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['product_count_pending' => function ($q){
                    $q->selectRaw('COUNT(*)')
                        ->from('users as u')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->where('p.state_id', 4)
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['product_count_rejected' => function ($q){
                    $q->selectRaw('COUNT(*)')
                        ->from('users as u')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->where('p.state_id', 6)
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['product_count_deleted' => function ($q){
                    $q->selectRaw('COUNT(*)')
                        ->from('users as u')
                        ->leftJoin('products as p', 'p.user_id', '=', 'u.id')
                        ->where('p.state_id', 5)
                        ->whereColumn('u.id', 'users.id');
                }]);
    }
  
    public function scopeOrdersCount($query)
    {
        return  $query->addSelect(['order_count_payment' => function ($q){
                    $q->selectRaw('COUNT(DISTINCT o.id)')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('o.payment_state_id', 4)
                        ->whereNull('o.deleted_at')
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['order_count_pending' => function ($q){
                    $q->selectRaw('COUNT(DISTINCT o.id)')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('o.payment_state_id', 1)
                        ->whereNull('o.deleted_at')
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['order_count_failed' => function ($q){
                    $q->selectRaw('COUNT(DISTINCT o.id)')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('o.payment_state_id', 3)
                        ->whereNull('o.deleted_at')
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['order_count_canceled' => function ($q){
                    $q->selectRaw('COUNT(DISTINCT o.id)')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('o.payment_state_id', 2)
                        ->whereNull('o.deleted_at')
                        ->whereColumn('u.id', 'users.id');
                }]);
    }

    public function scopeSales($query)
    {
        return  $query->addSelect(['sales_today' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->whereDate('o.updated_at', Carbon::today())
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['sales_last_7_days' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->whereBetween('o.updated_at', [Carbon::now()->subDays(7), Carbon::now()])                        
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['sales_last_30_days' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->whereBetween('o.updated_at', [Carbon::now()->subDays(30), Carbon::now()])                        
                        ->whereColumn('u.id', 'users.id');
                }])->addSelect(['sales_year' => function ($q){
                    $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('users as u')
                        ->join('products as p', 'p.user_id', '=', 'u.id')
                        ->join('product_colors as pc', 'pc.product_id', '=', 'p.id')
                        ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                        ->join('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('p.state_id', 3)
                        ->where('o.payment_state_id', 4)
                        ->whereYear('o.updated_at', now()->year)                        
                        ->whereColumn('u.id', 'users.id');
                }]);
    }

}
