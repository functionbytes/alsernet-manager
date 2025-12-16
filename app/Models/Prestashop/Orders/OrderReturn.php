<?php

namespace App\Models\Prestashop\Orders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Orders\Order;
use App\Models\Prestashop\Customer;

class OrderReturn extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_order_return';
    protected $primaryKey = 'id_order_return';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_customer',
        'id_order',
        'state',
        'question',
        'date_add',
        'date_upd',
    ];

        protected $casts = [
        'date_add' => 'datetime',
        'date_upd' => 'datetime',
        'id_customer' => 'integer',
        'id_order' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }
}
