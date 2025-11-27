<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['invoice_id',
        'order_id',
        'product_id',    
        'service_id',
        'price',
        'quantity',
        'total'
    ];

}