<?php

namespace App\Traits;

use App\Models\UserDetails;
use App\Models\UserMenu;

use Illuminate\Support\Facades\Hash;

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
        return $this->belongsTo(Client::class, 'user_id', 'id');
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
            'username' => $request->username,
            'email' => $request->email
        ]);

        //Si NO es cliente se evalua la existencia del Rol.
        if (!request('is_client')){
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
