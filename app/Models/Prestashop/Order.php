<?php

namespace App\Models\Prestashop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Orders\Order;
use App\Models\Prestashop\Shop\ShopGroup;
use App\Models\Prestashop\Shop\Shop;
use App\Models\Prestashop\Orders\OrderState;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasMany,
    HasOne
};

class Order extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_orders';
    protected $primaryKey = 'id_order';
    public $timestamps = false;

    protected $fillable = [
        'id_order',
        'id_address_delivery',
        'id_address_invoice',
        'id_shop_group',
        'id_shop',
        'id_cart',
        'id_currency',
        'id_lang',
        'id_customer',
        'id_carrier',
        'current_state',
        'secure_key',
        'payment',
        'module',
        'conversion_rate',
        'recyclable',
        'gift',
        'gift_message',
        'mobile_theme',
        'shipping_number',
        'total_discounts',
        'total_discounts_tax_incl',
        'total_discounts_tax_excl',
        'total_paid',
        'total_paid_tax_incl',
        'total_paid_tax_excl',
        'total_paid_real',
        'total_products',
        'total_products_wt',
        'total_shipping',
        'total_shipping_tax_incl',
        'total_shipping_tax_excl',
        'carrier_tax_rate',
        'total_wrapping',
        'total_wrapping_tax_incl',
        'total_wrapping_tax_excl',
        'invoice_number',
        'delivery_number',
        'invoice_date',
        'delivery_date',
        'valid',
        'date_add',
        'date_upd',
        'reference',
        'round_mode',
        'round_type',
        'note',
    ];

        protected $casts = [
        'date_add' => 'datetime',
        'date_upd' => 'datetime',
        'recyclable' => 'boolean',
        'gift' => 'boolean',
        'gift_message' => 'boolean',
        'mobile_theme' => 'boolean',
        'id_order' => 'integer',
        'id_address_delivery' => 'integer',
        'id_address_invoice' => 'integer',
        'id_shop_group' => 'integer',
        'id_shop' => 'integer',
        'id_cart' => 'integer',
        'id_currency' => 'integer',
        'id_lang' => 'integer',
        'id_customer' => 'integer',
        'id_carrier' => 'integer',
        'conversion_rate' => 'float',
        'total_discounts' => 'float',
        'total_discounts_tax_incl' => 'float',
        'total_discounts_tax_excl' => 'float',
        'total_paid' => 'float',
        'total_paid_tax_incl' => 'float',
        'total_paid_tax_excl' => 'float',
        'total_paid_real' => 'float',
        'total_products' => 'float',
        'total_products_wt' => 'float',
        'total_shipping' => 'float',
        'total_shipping_tax_incl' => 'float',
        'total_shipping_tax_excl' => 'float',
        'carrier_tax_rate' => 'float',
        'total_wrapping' => 'float',
        'total_wrapping_tax_incl' => 'float',
        'total_wrapping_tax_excl' => 'float',
    ];

    // BelongsTo Relationships
    public function customer() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Customer',
            'id_customer',
            'id_customer'
        );
    }

    public function addressDelivery() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Address',
            'id_address_delivery',
            'id_address'
        );
    }

    public function addressInvoice() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Address',
            'id_address_invoice',
            'id_address'
        );
    }

    public function cart() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Cart',
            'id_cart',
            'id_cart'
        );
    }

    public function currency() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Currency',
            'id_currency',
            'id_currency'
        );
    }

    public function language() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Language',
            'id_lang',
            'id_lang'
        );
    }

    public function carrier() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Carrier',
            'id_carrier',
            'id_carrier'
        );
    }

    public function shop() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\Shop',
            'id_shop',
            'id_shop'
        );
    }

    public function shopGroup() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\ShopGroup',
            'id_shop_group',
            'id_shop_group'
        );
    }

    public function currentOrderState() : BelongsTo
    {
        return $this->belongsTo(
            'App\Models\Prestashop\OrderState',
            'current_state',
            'id_order_state'
        );
    }

    // HasMany Relationships
    public function orderDetails() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderDetail',
            'id_order',
            'id_order'
        );
    }

    public function orderHistory() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderHistory',
            'id_order',
            'id_order'
        );
    }

    public function orderPayments() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderPayment',
            'id_order',
            'id_order'
        );
    }

    public function orderInvoices() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderInvoice',
            'id_order',
            'id_order'
        );
    }

    public function orderCarriers() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderCarrier',
            'id_order',
            'id_order'
        );
    }

    public function orderCartRules() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderCartRule',
            'id_order',
            'id_order'
        );
    }

    public function orderMessages() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderMessage',
            'id_order',
            'id_order'
        );
    }

    public function orderSlips() : HasMany
    {
        return $this->hasMany(
            'App\Models\Prestashop\OrderSlip',
            'id_order',
            'id_order'
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'id_address_delivery');
    }

    public function invoiceAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'id_address_invoice');
    }

    public function shopGroup(): BelongsTo
    {
        return $this->belongsTo(ShopGroup::class, 'id_shop_group');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'id_shop');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'id_cart');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'id_currency');
    }

    public function lang(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'id_lang');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'id_carrier');
    }

    public function currentState(): BelongsTo
    {
        return $this->belongsTo(OrderState::class, 'current_state');
    }
}
