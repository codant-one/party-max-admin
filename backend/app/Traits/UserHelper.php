<?php

namespace App\Traits;

use App\Models\Province;
use App\Models\UserDetails;
use App\Models\Theme;
use App\Models\Article;
use App\Models\AccountingMovement;
use App\Models\Review_Articles;
use App\Models\Suggestion;
use App\Models\UserNotification;
use App\Models\UserFacebookAccount;
use App\Models\UserLinkNoProduct;
use App\Models\UserSale;
use App\Models\UserStat;
use App\Models\PlatformLink;
use App\Models\UserContability;
use App\Models\UserConfirmedSale;
use App\Models\ProductControlled;
use App\Models\Coffee;

use Illuminate\Support\Facades\Hash;

/**
 * Trait for models with stores
 */
trait UserHelper
{
    /**** Relationship ****/
    public function userDetail() {
        return $this->hasOne(UserDetails::class, 'user_id', 'id');
    }

    public function themes() {
        return $this->hasMany(Theme::class, 'user_id', 'id');
    }

    public function articles() {
        return $this->hasMany(Article::class, 'user_id', 'id');
    }

    public function accounting_movements() {
        return $this->hasMany(AccountingMovement::class, 'user_id', 'id');
    }

    public function review_articles() {
        return $this->hasMany(Review_Articles::class, 'user_id', 'id');
    }

    public function suggestions() {
        return $this->hasMany(Suggestion::class, 'user_id', 'id');
    }

    public function user_notifications() {
        return $this->hasMany(UserNotification::class, 'user_id', 'id');
    }

    public function user_facebook_accounts() {
        return $this->hasOne(UserFacebookAccount::class, 'user_id', 'id');
    }

    public function user_links() {
        return $this->hasMany(UserLink::class, 'user_id', 'id');
    }

    public function user_links_no_products() {
        return $this->hasMany(UserLinkNoProduct::class, 'user_id', 'id');
    }

    public function user_sales() {
        return $this->hasMany(UserSale::class, 'user_id', 'id');
    }

    public function user_stats() {
        return $this->hasMany(UserStat::class,'user_id','id');
    }

    public function platform_links() {
        return $this->hasMany(PlatformLink::class,'user_id','id');
    }

    public function user_contabilities() {
        return $this->hasMany(UserContability::class,'user_id','id');
    }

    public function user_confirmed_sales() {
        return $this->hasMany(UserConfirmedSale::class,'user_id','id');
    }

    public function product_controlleds() {
        return $this->hasMany(ProductControlled::class,'user_id','id');
    }

    public function coffees() {
        return $this->hasMany(Coffee::class,'user_id','id');
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
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user->syncRoles($request->roles);

        UserDetails::updateOrCreateUser($request, $user);

        return $user;
    }

    public static function updateUser($request, $user) {
        $user->update([
            'name' => $request->name,
            'last_name' =>  $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user->roles()->detach();  
        $user->syncRoles($request->roles);
        
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

}
