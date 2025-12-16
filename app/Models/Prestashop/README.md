# PrestaShop to Laravel Eloquent Models

## Conversion Summary

Successfully converted **97 PrestaShop core entity classes** to Laravel Eloquent models.

### Conversion Date
2025-11-07

### Models Generated

All models follow the Laravel Eloquent structure with:
- Namespace: `App\Models\Prestashop`
- Connection: `prestashop`
- Table prefix: `aalv_`
- Timestamps: disabled (`public $timestamps = false`)
- Proper fillable attributes
- Type casting for booleans, integers, floats, and datetime fields

### Complete List of Models

#### Core Entities
- Access
- Address
- AddressFormat
- Alias
- Attachment
- Attribute
- AttributeGroup

#### Content & CMS
- CMS
- CMSCategory
- CMSRole
- Contact
- Hook
- Meta
- Page
- Tab

#### Products & Catalog
- Product
- Combination (Product Attributes)
- ProductSupplier
- ProductDownload
- Category
- Feature
- FeatureValue
- Image
- ImageType
- Manufacturer
- Supplier
- Tag

#### Customers
- Customer
- CustomerMessage
- CustomerSession
- CustomerThread
- Guest
- Gender
- Group
- GroupReduction

#### Orders
- Order
- OrderCarrier
- OrderCartRule
- OrderDetail
- OrderHistory
- OrderInvoice
- OrderMessage
- OrderPayment
- OrderReturn
- OrderReturnState
- OrderSlip
- OrderState

#### Cart & Pricing
- Cart
- CartRule
- SpecificPrice
- SpecificPriceRule
- Delivery

#### Shipping & Geography
- Carrier
- Country
- State
- Zone
- RangePrice
- RangeWeight

#### Stock & Warehouse
- Stock
- StockAvailable
- StockMvt
- StockMvtReason
- StockMvtWS
- Warehouse
- WarehouseProductLocation
- SupplyOrder
- SupplyOrderDetail
- SupplyOrderHistory
- SupplyOrderReceiptHistory
- SupplyOrderState

#### Tax
- Tax
- TaxRule
- TaxRulesGroup

#### Shop & Configuration
- Shop
- ShopGroup
- ShopUrl
- Configuration
- Language
- Currency

#### Administration
- Employee
- EmployeeSession
- Profile
- QuickAccess
- PrestaShopLogger
- RequestSql

#### Other
- Connection
- ConnectionsSource
- Customization
- CustomizationField
- DateRange
- Mail
- Message
- Referrer
- Risk
- SearchEngine
- Store

## Usage Example

```php
use App\Models\Prestashop\Product;
use App\Models\Prestashop\Category;
use App\Models\Prestashop\Order;

// Get all active products
$products = Product::where('active', true)->get();

// Get orders with customer
$orders = Order::with('customer')->where('current_state', 2)->get();

// Get categories
$categories = Category::where('active', 1)->orderBy('position')->get();
```

## Next Steps

To complete the implementation:

1. **Add Relationships**: Define `belongsTo`, `hasMany`, `belongsToMany` relationships between models
2. **Create Lang Models**: For multilingual tables (product_lang, category_lang, etc.)
3. **Configure Database Connection**: Update `config/database.php` with PrestaShop connection
4. **Add Scopes**: Add query scopes for common filters (active products, valid orders, etc.)
5. **Add Accessors/Mutators**: For computed properties and formatting

## Database Configuration

Add to `config/database.php`:

```php
'connections' => [
    // ... other connections
    
    'prestashop' => [
        'driver' => 'mysql',
        'host' => env('PRESTASHOP_DB_HOST', '127.0.0.1'),
        'port' => env('PRESTASHOP_DB_PORT', '3306'),
        'database' => env('PRESTASHOP_DB_DATABASE', 'prestashop'),
        'username' => env('PRESTASHOP_DB_USERNAME', 'root'),
        'password' => env('PRESTASHOP_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => false,
    ],
],
```

## Notes

- All models use `public $timestamps = false` as PrestaShop uses `date_add` and `date_upd` instead of Laravel's default `created_at` and `updated_at`
- Primary keys are preserved from PrestaShop (e.g., `id_product`, `id_order`, `id_customer`)
- Table names use the `aalv_` prefix as specified
- Boolean fields are properly cast to `bool`
- Datetime fields are cast to `datetime`
- Float/decimal fields are cast to `float`
- Integer fields are cast to `integer`
