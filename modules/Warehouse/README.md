# Warehouse Module

## Overview

The Warehouse module provides comprehensive warehouse management functionality including inventory tracking, location management, product organization, and transfer operations.

## Features

- **Warehouse Management**: Create and configure multiple warehouses
- **Multi-level Inventory System**: Organize inventory with floors, locations, sections, and slots
- **Location Styling**: Customize location appearance with predefined styles
- **Inventory Tracking**: Track product movements and inventory history
- **Transfer Operations**: Manage product transfers between locations
- **Manager Dashboard**: Administrative tools for warehouse configuration
- **Worker Interface**: Daily operations interface for warehouse staff
- **Reports & Analytics**: Generate reports on inventory and operations

## Architecture

### Directory Structure

```
Modules/Warehouse/
├── app/
│   ├── Entities/              # Eloquent models (formerly Models/)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Managers/      # Manager profile controllers (admin)
│   │   │   └── Warehouses/    # Warehouse worker controllers (operations)
│   │   ├── Requests/          # Form request validation classes
│   │   └── ViewComposers/     # View data composers
│   ├── Policies/              # Authorization policies
│   ├── Providers/             # Service providers
│   └── Services/              # Business logic services
├── config/
│   └── warehouse.php          # Module configuration
├── database/
│   ├── migrations/            # Migration stubs (actual migrations in root)
│   └── seeders/               # Database seeders
├── resources/
│   └── views/
│       ├── managers/          # Manager views (admin)
│       └── warehouses/        # Warehouse worker views (operations)
├── routes/
│   ├── managers.php           # Manager routes
│   ├── warehouses.php         # Warehouse worker routes
│   └── api.php                # API routes (future)
└── tests/
    ├── Feature/               # Integration tests
    └── Unit/                  # Unit tests
```

### Namespaces

- **Models**: `Modules\Warehouse\Entities\*`
- **Controllers**:
  - Managers: `Modules\Warehouse\Http\Controllers\Managers\*`
  - Workers: `Modules\Warehouse\Http\Controllers\Warehouses\*`
- **Requests**: `Modules\Warehouse\Http\Requests\*`
- **Services**: `Modules\Warehouse\Services\*`
- **Tests**: `Modules\Warehouse\Tests\*`

## Database

### Entities

- **Warehouse** - Base warehouse entity
- **WarehouseFloor** - Floors/levels within warehouses
- **WarehouseLocation** - Storage locations (stands)
- **WarehouseLocationSection** - Sections within locations
- **WarehouseInventorySlot** - Individual inventory slots
- **WarehouseLocationStyle** - Visual style definitions
- **WarehouseLocationCondition** - Condition templates
- **WarehouseInventoryMovement** - Inventory movement history
- **WarehouseInventoryOperation** - Inventory operations
- **WarehouseOperationItem** - Operation line items
- **WarehouseUser** - Warehouse-User relationship (pivot)
- **WarehouseShop** - Warehouse-Shop relationship (pivot)

### Migrations

All migrations are stored in `/database/migrations/warehouses/` in the root project (not in the module).

### Seeders

Database seeders are in `/database/seeders/Warehouse/`.

## Routes

### Manager Routes
- **Prefix**: `/manager/warehouse`
- **Middleware**: `auth`, `role:manager|super-admin`
- **Routes**:
  - Warehouse CRUD: `/manager/warehouse/warehouses`
  - Floor management: `/manager/warehouse/floors`
  - Location management: `/manager/warehouse/locations`
  - Inventory slots: `/manager/warehouse/slots`
  - Styles: `/manager/warehouse/styles`
  - History: `/manager/warehouse/history`
  - Reports: `/manager/warehouse/reports`

### Warehouse Worker Routes
- **Prefix**: `/warehouse`
- **Middleware**: `auth`, `check.roles.permissions:warehouse`
- **Routes**:
  - Dashboard: `/warehouse/dashboard`
  - Warehouse list: `/warehouse/warehouses`
  - Locations: `/warehouse/locations`
  - Products: `/warehouse/products`
  - Transfers: `/warehouse/transfer`

## Authorization

The module uses two authorization methods:

### Policies
- `WarehousePolicy` - Controls access to warehouse operations
- `WarehouseLocationPolicy` - Controls access to location operations

### Permissions
- `warehouse.manage` - Full warehouse management
- `warehouse.inventory` - Inventory operations
- `warehouse.transfer` - Transfer operations
- `warehouse.reports` - View reports

### Roles
- `manager` or `super-admin` - Can access manager routes
- `warehouse-worker` - Can access warehouse worker routes (with appropriate permissions)

## Services

### WarehouseLayoutParser
Handles parsing and processing of warehouse layout configurations.

```php
$service = app(\Modules\Warehouse\Services\WarehouseLayoutParser::class);
// Use service methods
```

## Configuration

Configuration file: `/config/warehouse.php`

Key settings:
- Default warehouse dimensions
- Permission names
- Navigation structure
- View and layout options

## Testing

Run tests:
```bash
php artisan test modules/Warehouse/tests
```

Run specific test:
```bash
php artisan test modules/Warehouse/tests/Feature/WarehouseManagementTest.php
```

## Views

Views use the module namespace: `warehouse::`

Example:
```blade
@extends('warehouse::managers.layouts.app')

@section('content')
    <!-- Your content -->
@endsection
```

## Installation

The module is automatically registered via `bootstrap/providers.php`:

```php
Modules\Warehouse\Providers\WarehouseServiceProvider::class,
```

## Development

### Creating Models
```bash
php artisan make:model modules/Warehouse/app/Entities/YourModel
```

### Creating Controllers
```bash
php artisan make:controller modules/Warehouse/app/Http/Controllers/Managers/YourController
```

### Creating Tests
```bash
php artisan make:test modules/Warehouse/tests/Feature/YourTest
```

## Contributing

When contributing to the Warehouse module:

1. Follow PSR-12 code style (run `vendor/bin/pint`)
2. Add tests for new features
3. Update documentation
4. Use meaningful commit messages
5. Create feature branches

## Future Enhancements

- Real-time inventory updates via WebSocket (Laravel Reverb)
- Mobile app API endpoints
- Advanced reporting with exports (Excel, PDF)
- IoT sensor integration
- Automated inventory reconciliation

## Support

For issues or questions regarding the Warehouse module, please refer to the project documentation or open an issue in the repository.
