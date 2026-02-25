# ALAMI GESTION - Inventory Management System

## Project Overview

A full-featured inventory management system built with Laravel, Livewire, and Tabler UI. Manages products, orders, purchases, quotations, customers, suppliers, payments, repair tickets, and user roles/permissions.

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **Frontend:** Livewire 3, Tabler UI (Blade templates), Vite
- **Database:** MySQL 8.4 (via Docker/Sail)
- **Tables:** PowerGrid v6, Rappasoft Livewire Tables v3
- **Auth:** Laravel Breeze, Spatie Permission v6
- **Testing:** PHPUnit 11
- **Code Style:** Laravel Pint
- **Dev Tools:** Laravel Sail (Docker), Debugbar, Query Detector

## Development Environment

```bash
# Start the environment
./vendor/bin/sail up -d

# Available services
# App:        http://localhost (port 80)
# Vite:       http://localhost:5173
# phpMyAdmin:  http://localhost:8080
# MySQL:      localhost:3306
```

## Common Commands

```bash
# Run tests (requires Sail MySQL container)
./vendor/bin/sail test

# Code formatting
./vendor/bin/pint

# Artisan
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan route:list
```

## Testing

- Tests require the Docker MySQL container running (`alami_testing` database)
- PHPUnit 11 with `#[Test]` attributes (not `@test` docblocks or `test_` prefixes)
- `RefreshDatabase` trait is in the base `Tests\TestCase` class
- Test files are in `tests/Feature/` and `tests/Unit/`

## Architecture

### Directory Structure

```
app/
  Console/Commands/       # Artisan commands (RecalculateOrderTotals)
  Enums/                  # OrderStatus, PermissionEnum, PurchaseStatus, QuotationStatus, SupplierType, TaxType
  Exports/                # Excel exports (PendingPaymentsExport)
  Helpers/helpers.php     # Global helper functions (autoloaded via composer.json)
  Http/
    Controllers/          # Resource controllers grouped by domain (Order/, Product/, Purchase/, Quotation/, API/)
    Middleware/            # setLanguage
    Requests/             # Form request validation (grouped by domain)
  Livewire/
    PowerGrid/            # 9 PowerGrid v6 tables (uses fields() API, PowerGridFields)
    Tables/               # 14 Rappasoft Livewire Tables
    Modals/               # StatusUpdateModal
    *.php                 # Interactive components (ProductCart, PurchaseForm, SupplierDropdown, etc.)
  Models/                 # 23 Eloquent models
  Observers/              # Order, Payment, Product, RepairTicket observers
  Services/               # StockService
  Traits/                 # FileUploadTrait, HasActivityLogs
  View/Components/        # Blade components
  View/PowerGridThemes/   # Custom Tabler theme for PowerGrid
routes/
  web.php                 # Main app routes (resource controllers)
  api.php                 # API routes (Sanctum-protected)
  auth.php                # Authentication routes (Breeze)
  console.php             # Scheduled commands
```

### Key Patterns

- **Resource controllers** with standard CRUD operations
- **Form Request** validation classes for all store/update operations
- **Livewire components** for interactive UI (cart, forms, search, dropdowns)
- **PowerGrid v6** tables with `fields()` method and `PowerGridFields` return type
- **Rappasoft Livewire Tables** for sortable/filterable data tables
- **Spatie Activity Log** for audit trails
- **Spatie Permission** for role-based access control
- **Observers** for side effects (stock updates, payment recalculations)
- **Eloquent `$casts` property arrays** (not `casts()` methods)
- **`to_route()` helper** for redirects (not `redirect()->route()`)
- **Constructor promotion** in services and observers
- **`DB::transaction()` closures** for atomic operations

### Models

Core: `Product`, `Order`, `OrderDetails`, `Purchase`, `PurchaseDetails`, `Quotation`, `QuotationDetails`
Commerce: `Customer`, `Supplier`, `Payment`, `Cart`, `CartItem`, `ShoppingCart`
Support: `Category`, `Unit`, `Warehouse`, `Driver`, `StockMovement`
Repair: `RepairTicket`, `RepairPhoto`, `RepairStatusHistory`, `ProgressItem`
Auth: `User`

### Coding Conventions

- Use **typed properties** and **return types** everywhere
- Use **`match` expressions** over `switch` statements
- Use **arrow functions** for simple closures
- Use **collection methods** (`map`, `filter`, `each`) over manual loops
- Use **null safe operator** (`?->`) where appropriate
- Use **pipe-delimited validation rules** (`'required|string|max:255'`) in form requests
- Use **`#[On]`** and **`#[Validate]`** attributes in Livewire components
- Use **`#[Test]`** attribute in PHPUnit tests
- Remove boilerplate docblocks; only add comments that explain *why*
- Run `./vendor/bin/pint` before committing
