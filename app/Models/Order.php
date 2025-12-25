<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'tracking_number',
        'status',
        'bosta_id',
        'state_code',
        'type',
        'cod',
        'state_changed_at',
        'is_confirmed_delivery',
        'delivery_promise_date',
        'exception_reason',
        'exception_code',
        'business_reference',
        'number_of_attempts',
    ];

    protected $casts = [
        'state_changed_at' => 'datetime',
        'delivery_promise_date' => 'date',
        'is_confirmed_delivery' => 'boolean',
        'cod' => 'decimal:2',
    ];
}
