# Supplier Automation System - Database Seeders

## Overview

This document describes the comprehensive seeders created for the Supplier Automation System based on the requirements in `/Users/functionbytes/Function/Coding/manager/docs/backend/ai-content-automation-requirements.md`.

## Created Seeders

### 1. SupplierSeeder.php
Creates 5 example suppliers with realistic data.

**Suppliers Created:**
- **Nike** (Code: NIKE)
  - ERP ID: 1001
  - Website: https://www.nike.com
  - Active: Yes

- **Adidas** (Code: ADIDAS)
  - ERP ID: 1002
  - Website: https://www.adidas.com
  - Active: Yes

- **Puma** (Code: PUMA)
  - ERP ID: 1003
  - Website: https://www.puma.com
  - Active: Yes

- **Asics** (Code: ASICS)
  - ERP ID: 1004
  - Website: https://www.asics.com
  - Active: Yes

- **New Balance** (Code: NB)
  - ERP ID: 1005
  - Website: https://www.newbalance.com
  - Active: Yes

---

### 2. SupplierSourceSeeder.php
Creates multiple data sources per supplier with different source types.

**Sources Created:**

#### Nike (3 sources):
1. **Nike Official Website (ES)** - Website scraping
   - Priority: 1
   - Trust Level: High
   - Type: website

2. **Nike FTP Catalog** - FTP file sync
   - Priority: 2
   - Trust Level: High
   - Type: ftp

3. **Nike Product API** - REST API
   - Priority: 3
   - Trust Level: High
   - Type: api

#### Adidas (2 sources):
1. **Adidas Official Website (ES)** - Website scraping
   - Priority: 1
   - Trust Level: High
   - Type: website

2. **Adidas Excel Catalog** - Manual file upload
   - Priority: 2
   - Trust Level: Medium
   - Type: file

#### Puma (1 source):
1. **Puma FTP CSV Catalog** - FTP CSV sync
   - Priority: 1
   - Trust Level: High
   - Type: ftp

#### Asics (1 source):
1. **Asics Product API** - OAuth2 API
   - Priority: 1
   - Trust Level: High
   - Type: api

#### New Balance (1 source):
1. **New Balance Web Scraping** - Website scraping
   - Priority: 1
   - Trust Level: Medium
   - Type: website

---

### 3. SupplierSourceOptionSeeder.php
Creates comprehensive configuration options for each source.

**Configuration Types:**

#### Website Sources:
- `base_url` - Base URL of the website
- `product_url_pattern` - URL pattern for product pages
- `search_url` - Search URL pattern
- `selectors` - CSS selectors (JSON) for data extraction
- `pagination` - Pagination configuration (JSON)
- `headers` - HTTP headers (JSON)
- `rate_limit` - Requests per minute

**Example Nike Website Configuration:**
```json
{
  "base_url": "https://www.nike.com/es",
  "product_url_pattern": "/es/t/{slug}",
  "selectors": {
    "name": "h1.product-title",
    "description": ".description-preview",
    "specifications": ".product-specs li",
    "images": ".product-gallery img@src"
  },
  "rate_limit": 30
}
```

#### FTP Sources:
- `host` - FTP server hostname
- `port` - FTP port (21 for FTP, 22 for SFTP)
- `protocol` - Protocol type (ftp, sftp, ftps)
- `username` - FTP username
- `password` - Encrypted password
- `remote_path` - Remote directory path
- `file_pattern` - File pattern (e.g., `*.xlsx`)
- `file_format` - File format (xlsx, csv, pdf)
- `sync_frequency` - Sync frequency (daily, weekly)

#### API Sources:
- `base_url` - API base URL
- `auth_type` - Authentication type (bearer, oauth2, basic)
- `api_key` - Encrypted API key
- `endpoints` - API endpoints (JSON)
- `rate_limit` - Requests per minute
- `response_format` - Response format (json, xml)

#### File Sources:
- `local_path` - Local storage path
- `file_pattern` - File pattern
- `file_format` - File format
- `encoding` - Character encoding (UTF-8, ISO-8859-1)
- `sheet_name` - Excel sheet name
- `delimiter` - CSV delimiter

---

### 4. SupplierSourceTemplateSeeder.php
Creates 5 reusable templates for common integration patterns.

**Templates Created:**

1. **Shopify API Template**
   - Category: E-commerce
   - Source Type: api
   - Features: Full Shopify REST API integration
   - Variables: shop_url, api_version, access_token

2. **WooCommerce API Template**
   - Category: E-commerce
   - Source Type: api
   - Features: WooCommerce REST API v3
   - Variables: site_url, consumer_key, consumer_secret

3. **Generic FTP CSV Template**
   - Category: File Import
   - Source Type: ftp
   - Features: Universal CSV import via FTP
   - Variables: ftp_host, ftp_username, ftp_password, remote_path, delimiter, encoding

4. **E-commerce Web Scraping Template**
   - Category: Web Scraping
   - Source Type: website
   - Features: Generic e-commerce site scraping
   - Variables: base_url, product_url_pattern, selector_name, selector_description

5. **Manual Excel Upload Template**
   - Category: File Import
   - Source Type: file
   - Features: Manual Excel file processing
   - Variables: upload_path, sheet_name

Each template includes:
- Connection configuration
- Data extraction mapping
- Schedule settings
- Retry logic
- Validation rules

---

### 5. SupplierPromptSeeder.php
Creates AI prompts with hierarchical priority system.

**Prompts Created:**

#### 1. Global Default Prompt (Priority: 100)
- Scope: global
- Language: es-ES
- Tone: commercial
- Use: Default fallback for all products
- Sections: name, short_description, long_description, bullet_points, seo_title, seo_description

#### 2. Supplier-Specific Prompts:

**Nike Prompt** (Priority: 10)
- Focuses on Nike technologies (Air, Zoom, React, Flyknit, Dri-FIT)
- Technical tone with performance emphasis
- Includes technology explanations and heritage

**Adidas Prompt** (Priority: 10)
- Emphasizes Adidas technologies (Boost, Primeknit, Continental)
- Highlights sustainability (Primegreen, Primeblue)
- Modern, lifestyle-oriented approach

#### 3. Category-Specific Prompts:

**Sneakers/Zapatillas** (Priority: 20, Category ID: 10)
- Technical footwear knowledge
- Focus: cushioning, upper, sole, fit
- Includes sizing guide

**Clothing/Ropa** (Priority: 20, Category ID: 20)
- Textile and construction knowledge
- Focus: materials, fit, technical features
- Includes care instructions and size guide

#### 4. Combined Prompts:

**Nike Sneakers** (Priority: 5)
- Highest priority for Nike footwear
- Combines Nike tech knowledge + sneaker expertise
- Most detailed and specialized

**Prompt Resolution Order:**
```
1. SOURCE + SUPPLIER + CATEGORY (highest)
2. SUPPLIER + CATEGORY
3. SOURCE + SUPPLIER
4. SUPPLIER only
5. CATEGORY only
6. GLOBAL default (lowest)
```

---

### 6. SupplierAutomationSettingSeeder.php
Creates 33 system-wide configuration settings.

**Settings Categories:**

#### Connection Settings (5 settings):
- `automation.max_concurrent_jobs` = 5
- `automation.default_timeout` = 300 seconds
- `automation.retry_attempts` = 3
- `automation.retry_backoff_multiplier` = 2
- `automation.n8n_enabled` = true
- `automation.n8n_base_url` = http://n8n.local:5678

#### Security Settings (4 settings):
- `automation.enable_ssl_verification` = true
- `automation.allowed_domains` = [nike.com, adidas.com, ...]
- `automation.encryption_key` = (encrypted)
- `automation.n8n_api_key` = (encrypted)

#### Rate Limiting (4 settings):
- `automation.global_rate_limit_per_minute` = 100
- `automation.rate_limit_per_source` = 30
- `automation.max_products_per_batch` = 100
- `automation.max_file_size_mb` = 50

#### AI Generation (4 settings):
- `automation.ai_provider` = anthropic
- `automation.ai_model` = claude-3-5-sonnet-20241022
- `automation.ai_temperature` = 0.7
- `automation.ai_max_tokens` = 4096

#### Content Validation (4 settings):
- `automation.require_manual_validation` = true
- `automation.auto_publish_threshold` = 0.95
- `automation.min_description_length` = 200
- `automation.max_description_length` = 2000

#### Scheduling (3 settings):
- `automation.default_schedule_time` = 02:00
- `automation.timezone` = Europe/Madrid
- `automation.enable_auto_scheduling` = true

#### Notifications (3 settings):
- `automation.notify_on_completion` = true
- `automation.notify_on_error` = true
- `automation.notification_email` = admin@alsernet.com

#### Logging (3 settings):
- `automation.log_level` = info
- `automation.log_retention_days` = 90
- `automation.enable_detailed_logging` = false

#### Cache (2 settings):
- `automation.cache_source_data` = true
- `automation.cache_ttl_hours` = 24

---

## Usage

### Running All Seeders

```bash
php artisan db:seed
```

### Running Individual Seeders

```bash
# Run in order (recommended):
php artisan db:seed --class=SupplierSeeder
php artisan db:seed --class=SupplierSourceSeeder
php artisan db:seed --class=SupplierSourceOptionSeeder
php artisan db:seed --class=SupplierSourceTemplateSeeder
php artisan db:seed --class=SupplierPromptSeeder
php artisan db:seed --class=SupplierAutomationSettingSeeder
```

### Prerequisites

1. Run migrations first:
```bash
php artisan migrate
```

2. Ensure the following tables exist:
   - `suppliers`
   - `supplier_sources`
   - `supplier_source_options`
   - `supplier_source_templates`
   - `supplier_prompts`
   - `supplier_automation_settings`

### Dependencies

The seeders have dependencies in this order:
1. **SupplierSeeder** (no dependencies)
2. **SupplierSourceSeeder** (requires SupplierSeeder)
3. **SupplierSourceOptionSeeder** (requires SupplierSourceSeeder)
4. **SupplierSourceTemplateSeeder** (no dependencies)
5. **SupplierPromptSeeder** (requires SupplierSeeder)
6. **SupplierAutomationSettingSeeder** (no dependencies)

---

## Data Summary

| Seeder | Records Created | Purpose |
|--------|----------------|---------|
| SupplierSeeder | 5 suppliers | Base supplier entities |
| SupplierSourceSeeder | 8 sources | Data sources per supplier |
| SupplierSourceOptionSeeder | ~50 options | Source configurations |
| SupplierSourceTemplateSeeder | 5 templates | Reusable integration templates |
| SupplierPromptSeeder | 6 prompts | AI content generation prompts |
| SupplierAutomationSettingSeeder | 33 settings | System configuration |

---

## Key Features

### 1. Realistic Example Data
- Based on actual sports equipment suppliers
- Real website URLs and realistic configurations
- Production-ready example prompts

### 2. Multiple Source Types
- **Website Scraping:** Nike, Adidas, New Balance
- **FTP Sync:** Nike, Puma
- **API Integration:** Nike, Asics
- **File Upload:** Adidas

### 3. Comprehensive Configuration
- Each source has complete configuration options
- Options match their source type (website, ftp, api, file)
- Includes authentication, rate limiting, selectors, etc.

### 4. Prompt Priority System
Implements the full 6-level priority system:
- Global default (everyone)
- Category-specific (all suppliers in category)
- Supplier-specific (all products from supplier)
- Source-specific (specific data source)
- Supplier + Category (Nike sneakers)
- Source + Supplier + Category (most specific)

### 5. Production-Ready Settings
- Sensible defaults for all system settings
- Organized by category (connection, security, limits, etc.)
- Includes sensitive data encryption
- N8N integration configuration

---

## Testing the Seeders

### Verify Suppliers
```php
use App\Models\Supplier\Supplier;

$nike = Supplier::where('code', 'NIKE')->first();
echo $nike->label; // "Nike"
echo $nike->sources()->count(); // 3
```

### Verify Sources
```php
use App\Models\Supplier\SupplierSource;

$nikeWebsite = SupplierSource::where('label', 'Nike Official Website (ES)')->first();
echo $nikeWebsite->options()->count(); // ~6-7 options
echo $nikeWebsite->getOption('base_url'); // "https://www.nike.com/es"
```

### Verify Prompts
```php
use App\Models\Supplier\SupplierPrompt;

// Test prompt resolution
$prompt = SupplierPrompt::resolvePrompt(
    supplierId: $nike->id,
    categoryId: 10, // Sneakers
    sourceId: null
);

echo $prompt->label; // "Prompt Nike Zapatillas" (highest priority match)
```

### Verify Settings
```php
use App\Models\Supplier\SupplierAutomationSetting;

$aiModel = SupplierAutomationSetting::getValue('automation.ai_model');
echo $aiModel; // "claude-3-5-sonnet-20241022"

$settings = SupplierAutomationSetting::getAllSettings('security');
// Returns all security settings as array
```

---

## Customization

### Adding New Suppliers
Edit `SupplierSeeder.php` and add to the `$suppliers` array:
```php
[
    'label' => 'Your Supplier',
    'code' => 'SUPPLIER_CODE',
    'erp_id' => 1006,
    'website_url' => 'https://...',
    'is_active' => true,
],
```

### Adding New Sources
Edit `SupplierSourceSeeder.php` and add to the `$sources` array.

### Customizing Prompts
Edit the prompt methods in `SupplierPromptSeeder.php`:
- `getGlobalPrompt()`
- `getNikePrompt()`
- `getAdidasPrompt()`
- etc.

### Adjusting Settings
Edit the `$settings` array in `SupplierAutomationSettingSeeder.php`.

---

## Notes

1. **UpdateOrCreate Pattern:** All seeders use `updateOrCreate()` to be idempotent - they can be run multiple times safely.

2. **Encrypted Values:** Some settings use encrypted values (passwords, API keys). The actual encryption happens at runtime using Laravel's encryption.

3. **Category IDs:** The category-specific prompts assume PrestaShop category IDs (10 for sneakers, 20 for clothing). Adjust as needed for your actual categories.

4. **N8N Integration:** The system includes configuration for N8N integration. Update the URL and API key in production.

5. **Realistic but Example Data:** While the data is realistic, credentials and API endpoints are placeholders and should be replaced with actual production values.

---

## Files Created

All seeders are located in `/Users/functionbytes/Function/Coding/manager/database/seeders/`:

1. `SupplierSeeder.php`
2. `SupplierSourceSeeder.php`
3. `SupplierSourceOptionSeeder.php`
4. `SupplierSourceTemplateSeeder.php`
5. `SupplierPromptSeeder.php`
6. `SupplierAutomationSettingSeeder.php`

Also updated:
- `DatabaseSeeder.php` - Registered all new seeders

---

## Next Steps

After running the seeders:

1. **Verify Data:** Use the testing queries above to verify all data was created correctly.

2. **Update Credentials:** Replace placeholder credentials with real ones:
   - FTP passwords
   - API keys
   - Encryption keys
   - N8N configuration

3. **Adjust Categories:** Update category IDs in `SupplierPromptSeeder` to match your PrestaShop categories.

4. **Customize Prompts:** Fine-tune AI prompts based on actual content requirements.

5. **Configure N8N:** Set up N8N workflows and update the base URL and API key.

---

## Support

For issues or questions about these seeders, refer to:
- Documentation: `/Users/functionbytes/Function/Coding/manager/docs/backend/ai-content-automation-requirements.md`
- Models: `/Users/functionbytes/Function/Coding/manager/app/Models/Supplier/`
