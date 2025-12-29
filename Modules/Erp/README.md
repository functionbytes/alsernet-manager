# ERP Module

Enterprise Resource Planning (ERP) system integration module for Alsernet. Provides comprehensive API communication with ERP systems for managing clients, orders, catalogs, and inventory.

## Features

- **Client Management** - Retrieve and manage customer data from ERP
- **Order Processing** - Handle order retrieval and processing from ERP
- **Catalog Management** - Manage product catalogs and subscriptions
- **Inventory Sync** - Synchronize stock information
- **Connection Management** - Configure and monitor ERP connections
- **Settings Dashboard** - Manage ERP configuration through UI
- **Cache Management** - Optimize performance with caching
- **Statistics Tracking** - Monitor ERP integration health

## Installation

The module is automatically loaded as part of the Alsernet application. No additional installation required.

## Configuration

ERP configuration is stored in `config/erp.php` and can be accessed via:

```php
config('erp.url')           // ERP server URL
config('erp.timeout')       // Request timeout
config('erp.connect_timeout') // Connection timeout
```

## Usage

### Via Facade

```php
use Modules\Erp\Facades\Erp;

// Get client from ERP
$client = Erp::getClient($idweb);

// Get orders
$orders = Erp::getOrders($customerId);

// Check connection
$status = Erp::checkConnection();
```

### Via Service Container

```php
use Modules\Erp\Services\Integrations\ErpService;

$erp = app(ErpService::class);
$client = $erp->getClient($idweb);
```

## API Endpoints

### Manager Routes (`/manager/settings/erp/`)

- `GET /` - View ERP settings
- `GET /edit` - Edit ERP configuration
- `PUT /update` - Update ERP settings
- `POST /check-connection` - Test ERP connection
- `POST /toggle-active` - Enable/disable ERP integration
- `POST /clear-cache` - Clear ERP cache
- `POST /reset-stats` - Reset integration statistics
- `GET /get-stats` - Get integration statistics
- `POST /test-sync` - Test synchronization

### API Routes (`/api/erp/`)

- `POST recuperarclienteerp` - Retrieve client
- `POST recuperarpedidoscliente` - Get customer orders
- `POST recuperarpedido` - Get order details
- `POST recuperarcatalogosclienteerp` - Get catalogs
- `POST guardardatosclienteerp` - Save client data
- And more (see ErpController for complete list)

## Console Commands

### Check ERP Connection

```bash
php artisan erp:check

# Update connection status
php artisan erp:check --update-status
```

## Architecture

```
Modules/Erp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/ErpController.php       - API endpoints
│   │   │   └── Managers/ErpSettingsController.php - Settings UI
│   │   └── Requests/                       - Form validation
│   ├── Services/
│   │   └── Integrations/ErpService.php    - Core ERP service
│   ├── Console/
│   │   └── Commands/ErpCheckCommand.php   - CLI commands
│   ├── Facades/
│   │   └── Erp.php                        - Service facade
│   ├── Jobs/                              - Async jobs
│   ├── Events/                            - ERP events
│   ├── Models/                            - ERP models
│   └── Providers/
│       └── ErpServiceProvider.php         - Service bootstrap
├── config/
│   └── erp.php                            - Configuration
├── routes/
│   ├── managers.php                       - Admin routes
│   └── api.php                            - API routes
└── database/
    ├── migrations/                        - Database changes
    └── seeders/                           - Seed data
```

## Payment Methods

The ERP module supports the following payment methods:

1. Cash on Delivery (COD)
2. Wire Transfer (Transferencia)
3. Credit Card (Tarjeta)
4. Bizum
5. Redsys
6. Google Pay
7. Apple Pay
8. PayPal
9. Finance/Installments (Financiación)
10. Sequra
11. Alsernet Finance
12. Online Transfer (Transferencia Online)
13. BanLendSmart

## Integration Points

- **Guzzle HTTP Client** - For ERP API communication
- **Laravel Cache** - For performance optimization
- **Database Logging** - Connection statistics and errors
- **Service Container** - Dependency injection
- **Facade Pattern** - Easy service access

## Testing

Test ERP connection from the settings dashboard or via CLI:

```bash
php artisan erp:check
```

## Troubleshooting

### Connection Issues

1. Verify ERP URL in configuration
2. Check network connectivity to ERP server
3. Review connection logs
4. Use dashboard "Check Connection" button

### Performance Issues

1. Clear cache: `POST /manager/settings/erp/clear-cache`
2. Check timeout settings in config
3. Monitor integration statistics

## Contributing

When adding new ERP features:

1. Add endpoints to `ErpController` (API) or `ErpSettingsController` (UI)
2. Implement logic in `ErpService`
3. Add routes in appropriate route file
4. Document in this README

## License

Proprietary - Alsernet
