<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    public function scopeWhereSearch($query, $search) {
        $query->whereHas('user', function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('username', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        });
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
        $supplier->update([
            'document_id'=> $document->id,
            'company_name' => $request->company_name,
            'phone_contact' => $request->phone_contact,
            'slug' => Str::slug($user->company_name)
        ]);

        $user = User::find($supplier->user_id);
        $request->merge([ 'email' => $user->email ]);
        User::updateUser($request, $user);

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

}
