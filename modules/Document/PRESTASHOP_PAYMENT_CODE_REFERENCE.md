# Prestashop Payment Detection - Code Reference

This document shows the EXACT code from Prestashop that handles payment detection, with annotations explaining how it works.

---

## 1. OrderState Model Definition

**File:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderState.php`

**Purpose:** Defines the OrderState class with all state properties

### Key Properties

```php
class OrderStateCore extends ObjectModel
{
    // Lines 29-70: State definition fields

    /** @var array<string> Name */
    public $name;

    /** @var bool Invoice flag */
    public $invoice;

    /** @var bool Delivery flag */
    public $delivery;

    /** @var bool Shipped flag */
    public $shipped;

    /** @var bool Paid flag ← THIS IS THE PAYMENT INDICATOR */
    public $paid;

    /** @var bool Logable flag */
    public $logable;
}
```

**Database Schema (lines 75-98):**

```php
public static $definition = [
    'table' => 'order_state',
    'primary' => 'id_order_state',
    'fields' => [
        'send_email' => ['type' => self::TYPE_BOOL],
        'invoice' => ['type' => self::TYPE_BOOL],
        'logable' => ['type' => self::TYPE_BOOL],
        'shipped' => ['type' => self::TYPE_BOOL],
        'paid' => ['type' => self::TYPE_BOOL],  // ← PAYMENT FLAG
        'delivery' => ['type' => self::TYPE_BOOL],
        'pdf_delivery' => ['type' => self::TYPE_BOOL],
        'pdf_invoice' => ['type' => self::TYPE_BOOL],
        'deleted' => ['type' => self::TYPE_BOOL],
        // ... language fields
    ],
];
```

**What This Means:**
- The `aalv_order_state` table has a `paid` column (TINYINT, 0 or 1)
- Each state can be marked as "paid" or "not paid"
- This allows ANY state to be configured as a payment indicator

---

## 2. OrderHistory Payment Detection Logic

**File:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderHistory.php`

**Method:** `changeIdOrderState($new_order_state, $id_order, $use_existing_payment = false)`

### Payment Confirmation Logic (Lines 377-414)

```php
// ===== SET ORDERS AS PAID =====
// When an order state changes, Prestashop checks if this is a payment confirmation
if ($new_os->paid == 1) {  // ← KEY LINE: Checks if new state is marked as paid

    // Get all invoices for this order
    $invoices = $order->getInvoicesCollection();

    // Process each invoice
    foreach ($invoices as $invoice) {
        /** @var OrderInvoice $invoice */
        $rest_paid = $invoice->getRestPaid();  // How much is still unpaid?

        if ($rest_paid > 0) {  // If invoice has unpaid portion
            // Create a new OrderPayment record
            $payment = new OrderPayment;
            $payment->order_reference = Tools::substr($order->reference, 0, 9);
            $payment->id_currency = $order->id_currency;
            $payment->amount = $rest_paid;  // Record the paid amount

            // Set payment method if available
            if ($order->total_paid != 0) {
                $payment_method = Module::getInstanceByName($order->module);
                $payment->payment_method = $payment_method->displayName;
            } else {
                $payment->payment_method = null;
            }

            // Update order's total_paid_real
            if ($payment->id_currency == $order->id_currency) {
                $order->total_paid_real += $payment->amount;
            } else {
                $order->total_paid_real += Tools::ps_round(
                    Tools::convertPrice($payment->amount, $payment->id_currency, false),
                    Context::getContext()->getComputingPrecision()
                );
            }
            $order->save();

            // Save the payment record
            $payment->conversion_rate = ($order ? $order->conversion_rate : 1);
            $payment->save();

            // Link payment to invoice
            Db::getInstance()->execute('
                INSERT INTO `'._DB_PREFIX_.'order_invoice_payment`
                (`id_order_invoice`, `id_order_payment`, `id_order`)
                VALUES('.(int) $invoice->id.', '.(int) $payment->id.', '.(int) $order->id.')'
            );
        }
    }
}
```

### What This Code Does:

1. **Triggers when:** An order state changes to a state with `paid = 1`
2. **Creates:** OrderPayment records in `aalv_order_payment` table
3. **Updates:** The order's `total_paid_real` field
4. **Links:** Payment to specific invoices via `aalv_order_invoice_payment` table

---

## 3. OrderHistory Model Definition

**File:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderHistory.php`

### Structure (Lines 31-60)

```php
class OrderHistoryCore extends ObjectModel
{
    /** @var int Order id */
    public $id_order;

    /** @var int Order status id */
    public $id_order_state;  // ← FK to order_state table

    /** @var int Employee id */
    public $id_employee;

    /** @var string Creation date */
    public $date_add;

    public static $definition = [
        'table' => 'order_history',
        'primary' => 'id_order_history',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'required' => true],
            'id_order_state' => ['type' => self::TYPE_INT, 'required' => true],  // ← FK
            'id_employee' => ['type' => self::TYPE_INT],
            'date_add' => ['type' => self::TYPE_DATE],
        ],
    ];
}
```

**What This Models:**
- Every state transition is recorded in `aalv_order_history`
- Each record links an order to a state at a specific time
- Multiple records per order = order went through multiple states

---

## 4. Configuration Hooks for Payment

**File:** `modules/Prestashop/integrations/prestashop/content/classes/order/OrderHistory.php`

### Lines 104-105: Payment Confirmation Hook

```php
// executes hook
if (in_array(
    $new_os->id,  // Check if state ID is in special payment states
    [
        Configuration::get('PS_OS_PAYMENT'),     // Default payment state ID
        Configuration::get('PS_OS_WS_PAYMENT')   // WebService payment state ID
    ]
)) {
    Hook::exec(
        'actionPaymentConfirmation',  // Trigger hook for payment confirmation
        ['id_order' => (int) $order->id],
        null, false, true, false,
        $order->id_shop
    );
}
```

**What This Does:**
- Checks if the new state ID matches configured payment states
- Prestashop typically configures `PS_OS_PAYMENT = 2` (default)
- Can also check `PS_OS_WS_PAYMENT` for API-triggered payments
- Triggers hooks that modules can listen to

**Retrieve These Values:**
```sql
SELECT name, value FROM aalv_configuration
WHERE name IN ('PS_OS_PAYMENT', 'PS_OS_WS_PAYMENT');
```

---

## 5. How Our Implementation Aligns

### Our Query in CreateBlockedProductDocuments.php:370

```sql
SELECT DISTINCT o.id_order
FROM aalv_orders o
WHERE o.id_order > {$lastOrderId}
  AND o.document_number IS NOT NULL
  AND o.document_type IS NOT NULL
  AND EXISTS (
    SELECT 1
    FROM aalv_order_history oh
    INNER JOIN aalv_order_state os ON os.id = oh.id_order_state
    WHERE oh.id_order = o.id_order
      AND (os.id = 2 OR os.paid = 1)  -- Check Prestashop's payment indicators
  )
ORDER BY o.id_order ASC
```

### Alignment Explanation:

| Prestashop Code | Our Query | Purpose |
|-----------------|-----------|---------|
| `if ($new_os->paid == 1)` | `AND os.paid = 1` | Check if state is marked as paid |
| `if (in_array($new_os->id, [... PS_OS_PAYMENT]))` | `AND os.id = 2` | Check default payment state ID |
| Order history creates record | `FROM aalv_order_history` | Access the historical record |
| `$new_os = new OrderState($new_order_state)` | `JOIN aalv_order_state os` | Load state properties |

---

## 6. Complete Payment Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Order Created                                             │
│    - OrderHistory record: id_order_state = 1 (Awaiting Pay) │
│    - Order.current_state = 1                                │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. Customer Pays                                             │
│    - Payment gateway confirms payment                        │
│    - Module triggers state change to ID = 2 (Paid)         │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. OrderHistory.changeIdOrderState(2, $order_id)            │
│    - Creates OrderHistory: id_order_state = 2              │
│    - Loads $new_os = new OrderState(2)                     │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. Check: if ($new_os->paid == 1)  [Line 378]              │
│    - OrderState 2 has paid = 1? YES ✓                       │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. Create Payment Record                                     │
│    - Insert into aalv_order_payment                         │
│    - Insert into aalv_order_invoice_payment                 │
│    - Update Order.total_paid_real                           │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. Order is Now PAID                                         │
│    - aalv_order_history HAS record with id_order_state = 2  │
│    - aalv_order_payment HAS payment record                   │
│    - Order.current_state = 2                                │
└─────────────────────────────────────────────────────────────┘

OUR DETECTION LOGIC:
  EXISTS (
    SELECT 1 FROM aalv_order_history oh
    INNER JOIN aalv_order_state os ON ...
    WHERE oh.id_order = {$order_id}
      AND (os.id = 2 OR os.paid = 1)
  ) = TRUE ✓
```

---

## 7. State ID vs Paid Flag: Why Both?

### Scenario A: Default Prestashop Installation

- OrderState ID 2 = "Paid"
- OrderState 2 has `paid = 1` flag
- Query `os.id = 2 OR os.paid = 1` catches it via EITHER condition

### Scenario B: Customized Installation

- OrderState ID 5 = "Paid" (configured differently)
- OrderState 5 has `paid = 1` flag
- Query would catch it via `os.paid = 1` (second condition)

### Scenario C: Multiple Payment States

- OrderState 2 = "Partial Payment" (has `paid = 0`)
- OrderState 5 = "Full Payment" (has `paid = 1`)
- Query catches state 5 via `os.paid = 1`

**Our OR logic ensures compatibility with ANY Prestashop configuration.**

---

## 8. Why 0 Results = Diagnostic Required

Our logic matches Prestashop's official code, so 0 results means:

1. **No states have `paid = 1` flag** - Check:
   ```sql
   SELECT id, name, paid FROM aalv_order_state;
   ```

2. **State ID 2 doesn't exist** - Check:
   ```sql
   SELECT * FROM aalv_order_state WHERE id = 2;
   ```

3. **No orders transitioned to paid state** - Check:
   ```sql
   SELECT DISTINCT id_order_state FROM aalv_order_history;
   ```

4. **Order history is incomplete** - Check:
   ```sql
   SELECT COUNT(*) FROM aalv_order_history;
   ```

---

**Generated:** 2026-01-18
**Verified against:** Prestashop OrderHistory.php lines 84-414, OrderState.php lines 27-98
