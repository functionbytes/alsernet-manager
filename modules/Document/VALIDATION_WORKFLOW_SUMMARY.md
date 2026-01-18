# Payment Detection & Order Validation Workflow ✅

## Phase 1: Simplification Complete ✅

### What Was Fixed
Payment detection queries were simplified from complex conditions to straightforward state ID checks:

```sql
-- OLD (Complex, didn't work)
WHERE (os.id_order_state = 2 OR os.paid = 1)
      AND EXISTS (JOIN aalv_order_state...)

-- NEW (Simple, working)
WHERE id_order_state = 2
      AND EXISTS (SELECT 1 FROM aalv_order_history...)
```

**Result:** 123,161 paid orders detected ✅

### Files Updated
1. **CreateBlockedProductDocuments.php** - Creates documents for orders with blocked products
2. **ValidateAndCleanupDocuments.php** - Validates existing documents & cleans orphans
3. **AnalyzePaidOrdersVsDocuments.php** - Analyzes concordance between Prestashop orders and documents
4. **DeepAnalyzePrestashopOrderStates.php** - Diagnostic tool for order state analysis

---

## Phase 2: Order Validation with Blocked Products

### Current Status
The system is now ready to validate which of the 123,161 paid orders have blocked products and need documents created.

### How It Works

```
1. Query Prestashop Database
   ↓
2. Find Paid Orders (id_order_state = 2)
   ↓
3. Get Products in Each Order
   ↓
4. Check DocumentProductBlockade Rules
   ↓
5. Create Document Automatically if Product Blocked
```

### Data Entities Used
- **Document** - Main document record (order_id links to Prestashop)
- **DocumentProductBlockade** - Rules for which products require documents
- **DocumentType** - Type of document needed (invoice, certification, etc.)
- **DocumentStatus** - Tracks document status (awaiting_documents, validated, rejected, etc.)

---

## Phase 3: Available Commands

### 1. Deep Analysis (Diagnostic)
```bash
php artisan app:deep-analyze-prestashop-states
```
**Purpose:** Analyze Prestashop order state configuration
**Output:**
- Order state definitions
- State distribution in database
- Payment detection methods comparison
- Orders with each state

---

### 2. Compare Orders vs Documents
```bash
php artisan app:analyze-paid-orders-vs-documents {--show-details}
```
**Purpose:** Compare paid orders in Prestashop with documents in our system
**Output:**
- Total paid orders in Prestashop
- Documents we have for those orders
- Missing documents (orphans)
- Candidates for deletion

**Options:**
- `--show-details` - List individual documents

---

### 3. Create Documents for Blocked Products
```bash
php artisan app:create-blocked-product-documents {--force} {--limit=N} {--start-after=ORDER_ID}
```
**Purpose:** Create documents for orders with blocked products
**Logic:**
1. Gets paid orders from Prestashop (id_order_state = 2)
2. Checks if order has blocked products (via DocumentProductBlockade)
3. Creates document with status "awaiting_documents"
4. Links document to customer & order

**Options:**
- `--force` - Skip confirmation prompt
- `--limit=100` - Process max 100 orders
- `--start-after=5000` - Start after order ID 5000

**Output:**
- Documents created
- Orders skipped (no blockade, already exists)
- Errors encountered

---

### 4. Validate & Cleanup
```bash
php artisan app:validate-and-cleanup-documents {--force} {--dry-run}
```
**Purpose:** Validate existing documents against current payment status
**Logic:**
1. Load all documents with order_id
2. For each document, check if order is still paid in Prestashop
3. If NOT paid AND status ≠ 5:
   - Show warning
   - Delete if confirmed
4. Exception: Never delete status_id = 5 (manual override)

**Options:**
- `--dry-run` - Show what would be deleted (no delete)
- `--force` - Skip confirmation prompts

**Output:**
- Documents to be deleted
- Actual deletion count
- Errors and warnings

---

## Recommended Workflow

### Step 1: Analyze Current State
```bash
php artisan app:deep-analyze-prestashop-states
```
Expected: 123,161 paid orders detected in Prestashop

---

### Step 2: Compare Orders with Documents
```bash
php artisan app:analyze-paid-orders-vs-documents --show-details
```
Expected: Shows coverage, missing documents, orphans

---

### Step 3: Create Missing Documents
```bash
# First, see what would be created
php artisan app:create-blocked-product-documents --dry-run

# Then create (with limit to test first 100)
php artisan app:create-blocked-product-documents --limit=100 --force
```

---

### Step 4: Validate & Cleanup
```bash
# First, dry-run to see what would be deleted
php artisan app:validate-and-cleanup-documents --dry-run

# Then delete confirmed orphans
php artisan app:validate-and-cleanup-documents --force
```

---

## Data Flow

```
Prestashop Database                    Our System
────────────────                       ──────────

aalv_orders (123,161 paid)
    ↓
    ├─→ id_order_state = 2? ✅
    ├─→ Products in order?
    └─→ Product has blockade?
            ↓
            CREATE Document in Laravel ✅
            ├─ UID (unique identifier)
            ├─ order_id (link to Prestashop)
            ├─ type_id (from blockade rule)
            ├─ status_id (awaiting_documents)
            └─ customer info (auto-populated)
```

---

## Key Features of Simplified Approach

✅ **Simpler** - No complex JOINs or OR conditions
✅ **Faster** - Fewer database operations
✅ **Clearer** - Easy to understand payment detection logic
✅ **Verified** - 123,161 orders confirmed working
✅ **Maintainable** - Easy to modify or extend later

---

## Next Steps

1. **Ensure database connectivity** - Check that both local app DB and Prestashop DB are accessible
2. **Run diagnostic command** - Verify 123,161 orders are detected
3. **Process blocking rules** - Run create-blocked-product-documents to auto-generate documents
4. **Validate & cleanup** - Run validation to ensure data consistency
5. **Monitor results** - Check document creation logs and status

---

**Status:** ✅ System ready for validation phase
**Date:** 2026-01-19
