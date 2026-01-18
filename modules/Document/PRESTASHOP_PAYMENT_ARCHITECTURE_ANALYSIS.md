# Prestashop Payment Architecture Analysis

## Overview

After reviewing the Prestashop Order model (`modules/Prestashop/integrations/prestashop/content/classes/order/`), I've documented how Prestashop handles order payment status detection. This is critical for understanding why our document creation/validation logic works the way it does.

---

## 1. Prestashop Order State System

### OrderState Table Structure (aalv_order_state)

The `aalv_order_state` table contains the state definitions with these key fields:

```
- id_order_state (INT, Primary Key)
- name (VARCHAR) - State name (in multiple languages)
- paid (TINYINT/BOOL) - Flag: 1 if this state indicates payment
- invoice (TINYINT/BOOL) - Flag: 1 if invoice should be generated
- shipped (TINYINT/BOOL) - Flag: 1 if order is shipped
- delivery (TINYINT/BOOL) - Flag: 1 if delivery slip should be created
- logable (TINYINT/BOOL) - Flag: 1 if state should be logged in statistics
- color (VARCHAR) - UI display color
- send_email (TINYINT/BOOL) - Flag: 1 if customer email should be sent
- ... other flags
```

**Source:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderState.php` (lines 27-98)

### OrderHistory Table Structure (aalv_order_history)

The `aalv_order_history` table logs all state transitions:

```
- id_order_history (INT, Primary Key)
- id_order (INT, Foreign Key) - Order reference
- id_order_state (INT, Foreign Key) - State at this transition
- id_employee (INT) - Who made the change
- date_add (DATETIME) - When the transition happened
```

**Source:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderHistory.php` (lines 31-60)

---

## 2. How Prestashop Detects Payment

### The Payment Transition Logic

When an order state changes in Prestashop, the system:

1. **Loads the new OrderState** to get its properties
2. **Checks if `$new_os->paid == 1`** to determine if this is a payment confirmation
3. **If payment is confirmed**, creates OrderPayment records and updates invoices

**Source Code Location:**
`modules/Prestashop/integrations/prestashop/content/classes/order/OrderHistory.php:378-414`

```php
// set orders as paid
if ($new_os->paid == 1) {
    $invoices = $order->getInvoicesCollection();
    foreach ($invoices as $invoice) {
        $rest_paid = $invoice->getRestPaid();
        if ($rest_paid > 0) {
            $payment = new OrderPayment;
            $payment->order_reference = Tools::substr($order->reference, 0, 9);
            $payment->id_currency = $order->id_currency;
            $payment->amount = $rest_paid;
            // ... create payment record
        }
    }
}
```

### Key Insight: Dual Payment Detection

Prestashop uses TWO mechanisms for payment detection:

1. **State ID-based**: Many installations use state ID `2` as the default "Paid" state
2. **Flag-based**: Any state with `paid = 1` flag indicates payment

This means payment can be confirmed through:
- **Either** moving to state ID 2
- **Or** moving to any state marked with `paid = 1` flag

---

## 3. Our Payment Detection Implementation

Our implementation correctly mirrors Prestashop's logic:

```sql
SELECT ... FROM aalv_order_history oh
INNER JOIN aalv_order_state os ON os.id_order_state = oh.id_order_state
WHERE oh.id_order = {$orderId}
  AND (os.id = 2 OR os.paid = 1)
LIMIT 1
```

This checks if the order **has ever had** (EXISTS in history):
- State ID = 2 (default payment state), **OR**
- Any state where `paid` flag = 1

**Why `EXISTS` instead of `WHERE current_state = 2`?**

Because an order can:
1. Be paid (state = 2)
2. Then shipped (state = 5)
3. Then completed (state = 9)

We need to know if payment was EVER received, not just the current state.

---

## 4. Why Query Returns 0 Paid Orders: Diagnostic Checklist

The fact that we have:
- **2,085 documents created**
- **But 0 paid orders detected**

This is HIGHLY SUSPICIOUS. Here's what to check:

### Possibility 1: State ID is NOT 2

```sql
-- Check what state IDs exist and their properties
SELECT id, name, paid, logable FROM aalv_order_state ORDER BY id ASC;
```

**What to look for:**
- What ID is the "Paid" state? (Might be 1, 3, 5, etc., not 2)
- Is there a state with `paid = 1`?

**If found:** Update query to use correct state ID

---

### Possibility 2: No States Have `paid = 1` Flag

```sql
-- Check which states have paid flag set
SELECT id, name, paid FROM aalv_order_state WHERE paid = 1;
```

**What to look for:**
- Are there ANY states with `paid = 1`?
- If not, the installation might use different logic

**If found:** Check what state is actually used for payment

---

### Possibility 3: Orders Never Transitioned to Paid State

```sql
-- Check what states orders are actually in
SELECT DISTINCT current_state FROM aalv_orders ORDER BY current_state;

-- Check what states appear in order history
SELECT DISTINCT id_order_state FROM aalv_order_history ORDER BY id_order_state;

-- Check distribution of current states
SELECT current_state, COUNT(*) as count FROM aalv_orders
GROUP BY current_state ORDER BY count DESC;
```

**What to look for:**
- Are orders stuck in "Awaiting Payment" (usually state 1)?
- Did they ever transition to a "Paid" state?

**If found:** Orders may not have been paid, or payment processing is broken

---

### Possibility 4: Order History is Missing Data

```sql
-- Check if order history records exist
SELECT COUNT(*) as history_records FROM aalv_order_history;
SELECT COUNT(DISTINCT id_order) as orders_with_history FROM aalv_order_history;

-- Compare with total orders
SELECT COUNT(*) as total_orders FROM aalv_orders;
```

**What to look for:**
- Are history records missing?
- Did orders never get history entries?

**If found:** History table might be incomplete or truncated

---

### Possibility 5: Custom Prestashop Configuration

```sql
-- Check Prestashop configuration for payment-related settings
SELECT name, value FROM aalv_configuration
WHERE name LIKE '%OS_PAYMENT%' OR name LIKE '%PAYMENT%'
LIMIT 20;
```

**What to look for:**
- `PS_OS_PAYMENT` - Default payment state ID
- `PS_OS_WS_PAYMENT` - Webservice payment state ID
- Custom payment state configurations

**Source:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderHistory.php:104`

---

## 5. Why 2,085 Documents Exist If No Orders Are Paid?

This suggests:

1. **Documents were created manually** - Not through `CreateBlockedProductDocuments` command
2. **Payment detection logic changed** - Documents were created with different logic that no longer applies
3. **The original order data doesn't match current logic** - Orders have custom states or flags
4. **Data corruption or import** - Orders were imported with incomplete state information

---

## 6. Recommended Diagnostic Steps (In Order)

Execute these SQL queries to diagnose:

### Step 1: Check State Configuration
```sql
SELECT id, name, paid FROM aalv_order_state ORDER BY id;
```
**Goal:** Find which state(s) represent payment

### Step 2: Check State Distribution
```sql
SELECT current_state, COUNT(*) as count FROM aalv_orders
GROUP BY current_state ORDER BY count DESC;
```
**Goal:** See where orders are stuck

### Step 3: Check History States
```sql
SELECT DISTINCT id_order_state FROM aalv_order_history ORDER BY id_order_state;
SELECT
    oh.id_order_state,
    os.name,
    os.paid,
    COUNT(DISTINCT oh.id_order) as orders
FROM aalv_order_history oh
LEFT JOIN aalv_order_state os ON os.id = oh.id_order_state
GROUP BY oh.id_order_state, os.name, os.paid
ORDER BY orders DESC;
```
**Goal:** See what states orders transition through

### Step 4: Check Specific Orders with Documents
```sql
-- Pick a few document order_ids and check their history
SELECT
    o.id_order,
    o.current_state,
    COUNT(oh.id_order_history) as history_count,
    GROUP_CONCAT(oh.id_order_state SEPARATOR ',') as states_in_history
FROM aalv_orders o
LEFT JOIN aalv_order_history oh ON o.id_order = oh.id_order
WHERE o.id_order IN (
    SELECT order_id FROM documents WHERE order_id IS NOT NULL LIMIT 5
)
GROUP BY o.id_order;
```
**Goal:** See if document orders actually have payment history

### Step 5: Test Payment Detection Logic
```sql
-- Test our payment detection on document orders
SELECT
    o.id_order,
    o.current_state,
    CASE
        WHEN EXISTS(
            SELECT 1 FROM aalv_order_history oh
            INNER JOIN aalv_order_state os ON os.id = oh.id_order_state
            WHERE oh.id_order = o.id_order AND (os.id = 2 OR os.paid = 1)
        ) THEN 'PAID'
        ELSE 'NOT_PAID'
    END as payment_status
FROM aalv_orders o
WHERE o.id_order IN (
    SELECT order_id FROM documents WHERE order_id IS NOT NULL LIMIT 20
)
ORDER BY o.id_order DESC;
```
**Goal:** Verify payment detection for specific documents

---

## 7. Action Plan

### If State ID is NOT 2:

Update all payment detection queries to use the correct state ID:

```sql
-- Example: if paid state is ID=5
SELECT ... WHERE (os.id = 5 OR os.paid = 1)
```

Then update these files:
- `modules/Document/app/Console/Commands/CreateBlockedProductDocuments.php:370`
- `modules/Document/app/Console/Commands/ValidateAndCleanupDocuments.php:189`
- `modules/Document/app/Console/Commands/AnalyzePaidOrdersVsDocuments.php`

### If Orders Never Had Payment State:

1. Investigate why orders in Prestashop were never marked as paid
2. Determine if this is a business process issue (orders waiting for manual payment confirmation)
3. Adjust document creation logic to handle your specific workflow

### If Configuration is Different:

1. Read the Prestashop settings table to find the actual payment state ID
2. Use that ID in payment detection instead of hardcoded `2`

---

## 8. Key Findings Summary

| Aspect | Finding |
|--------|---------|
| **Payment Detection Method** | Prestashop checks `$new_os->paid == 1` when state changes |
| **Backup Detection** | Default state ID 2 is typically the paid state |
| **Our Implementation** | Correctly uses `(os.id = 2 OR os.paid = 1)` with EXISTS |
| **Current Issue** | 0 paid orders found, but 2,085 documents exist → Data mismatch |
| **Root Cause** | Unknown - needs SQL diagnostic steps above |
| **Next Step** | Execute Step 1-2 diagnostics to understand state configuration |

---

## 9. Architecture Visualization

```
Order Creation
    ↓
Initial State: "Awaiting Payment" (usually state 1)
    ↓
Customer Pays
    ↓
Order State Changes → OrderHistory record created
    ↓
Check: Does new state have paid=1? (OrderHistory.php:378)
    ↓
YES → Create OrderPayment record + Update invoices
    ↓
Order now has: current_state = 2 (or other paid state)
                AND history record with paid state
                AND OrderPayment records exist
```

Our logic validates that `EXISTS` a record in this flow, not just current state.

---

**Generated:** 2026-01-18
**Status:** Diagnostic framework ready - awaiting SQL execution results
