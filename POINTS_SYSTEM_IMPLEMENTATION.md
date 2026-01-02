# Customer Loyalty Points System - Implementation Summary

## Overview
Added a comprehensive loyalty points system where customers earn points based on spending and can redeem points for payment.

## Database Changes

### Migration: `2026_01_02_151447_add_points_columns_to_customers_table.php`
Added three decimal columns to the `customers` table:
- `earned_points` - Total points earned by customer (decimal 10,2, default 0)
- `redeemed_points` - Total points redeemed by customer (decimal 10,2, default 0)
- `balance` - Available points balance (decimal 10,2, default 0)

**Status:** ✅ Migrated to all tenants (3 tenants)

## Backend Implementation

### 1. Settings Configuration
**File:** `app/Settings/GeneralSettings.php`
- Added `points_earning_rate` property (float, default 100)
- Configures how much customer needs to spend to earn 1 point
- Example: 100 means ₱100 spent = 1 point earned

### 2. PointsService
**File:** `app/Services/PointsService.php`

Key methods:
- `calculatePointsFromAmount(float $amount): float` - Calculate points from spent amount
- `earnPoints(Customer $customer, float $amount): float` - Award points to customer
- `redeemPoints(Customer $customer, float $points): bool` - Redeem points from balance
- `hasSufficientPoints(Customer $customer, float $requiredPoints): bool` - Check balance
- `convertPointsToAmount(float $points): float` - 1 point = 1 currency unit
- `convertAmountToPoints(float $amount): float` - 1 currency unit = 1 point

**Business Logic:**
- Points earning rate is configurable via GeneralSettings
- Points calculation: `floor($amount / $earning_rate * 100) / 100`
- Points redemption validates sufficient balance
- All operations are DB transactional
- 1 point equals 1 currency unit for redemption

### 3. Customer Model Updates
**File:** `app/Models/Customer.php`

Added to fillable:
- `earned_points`, `redeemed_points`, `balance`

Added casts:
- All three as `float`

Added methods:
- `hasSufficientPoints(float $requiredPoints): bool`
- `getFormattedBalanceAttribute(): string` - Returns formatted balance (e.g., "123.45")

### 4. PaymentService Integration
**File:** `app/Services/PaymentService.php`

- Injected `PointsService` via constructor
- Added `handleCustomerPoints()` method called after payment recording
- Points redemption logic:
  - If payment type is `points`: Redeem points from customer balance
  - Validates customer_id exists
  - Validates sufficient points available
  - Logs redemption with customer_id, order_id, points_redeemed
- Points earning logic:
  - For all non-points, non-credit payments with customer_id
  - Automatically awards points based on total_due amount
  - Logs earning with customer_id, order_id, points_earned, amount_spent

### 5. Request Validation
**File:** `app/Http/Requests/SettleBillRequest.php`

Added validation rules:
- `points_used` - nullable, numeric, min:0

Added PaymentType::POINTS case:
- Validates customer_id required
- Validates customer exists in tenant database
- Validates sufficient points balance
- Validates points_used > 0
- Adds to payment_details payload

Updated reference number logic:
- Points payment does not require reference_number (like cash and credit)

## Frontend Implementation

### SettlePayment.vue Updates
**File:** `resources/js/Pages/Resto/SettlePayment.vue`

#### Added Constants & Computed:
- `paymentTypeLabels.points = "Points"`
- `isPointsMethod` - Computed boolean
- `requiresAdditionalFields` - Updated to include points
- `isCustomerSearchDisabled` - Updated for both credit and points
- `selectedCustomer` - Computed from customerResults
- `selectedCustomerName` - Customer name from selected customer
- `selectedCustomerBalance` - Points balance from selected customer
- `pointsRequired` - Computed as totalDue (1 point = 1 currency)
- `hasSufficientPoints` - Validates balance >= required

#### UI Components Added:
Customer search section for points payment:
- Search field with min 2 characters
- Real-time customer search with debounce (300ms)
- Customer list showing:
  - Customer name
  - Contact info
  - **Points balance badge** (primary color)
- Selected customer info card:
  - Customer name
  - Available balance display
  - Points required display
  - Green success styling

#### Logic Updates:
- `selectCustomer()` - Preserves customer data in results for points balance
- `fetchCustomers()` - Works for both credit and points payment types
- Watch on `customerSearchQuery` - Updated for both payment types
- Watch on `isPointsMethod` - Resets customer search when switching away
- `additionalFieldsValid` - Validates customer selection and sufficient points
- `additionalFieldErrorMessage` - Specific messages for points validation
- `handleSettlePayment()` - Sends `customer_id` and `points_used` in payload

### Payload Structure for Points Payment:
```javascript
{
  cart_id: number,
  payment_method_id: number,
  amount_in_payment_currency: number,
  amount_paid: number,
  total_amount: number,
  customer_id: number,           // Required for points
  points_used: number,            // Points to redeem
  payment_details: {
    points_used: number,
    customer_id: number
  }
}
```

## Admin Panel Updates

### 1. General Settings Page
**File:** `app/Filament/Tenant/Pages/ManageSettings.php`

Added field:
- `points_earning_rate`
- Label: "Points Earning Rate"
- Helper text: "Amount customer needs to spend to earn 1 point"
- Type: Numeric
- Default: 100
- Min value: 1
- Suffix: "per point"

### 2. Customer Resource
**File:** `app/Filament/Tenant/Resources/CustomerResource.php`

#### Form Section Added:
"Loyalty Points" section (collapsible) with 3 columns:
- `earned_points` - Disabled, display-only, suffix "pts"
- `redeemed_points` - Disabled, display-only, suffix "pts"
- `balance` - Disabled, display-only, suffix "pts", helper text

#### Table Column Added:
- `balance` - Numeric (2 decimals), sortable, primary color, suffix " pts"
- Positioned after customer type column

## Payment Flow

### Points Redemption Flow:
1. Cashier selects Points payment method
2. Customer search field appears
3. Cashier searches for customer (min 2 chars)
4. Customer list shows with points balance
5. Cashier selects customer
6. System displays:
   - Customer name
   - Available balance
   - Points required
7. System validates sufficient points
8. On settle:
   - Frontend sends customer_id and points_used
   - Backend validates in SettleBillRequest
   - PaymentService calls PointsService.redeemPoints()
   - Customer balance updated in transaction
   - Order recorded with customer_id

### Points Earning Flow:
1. Customer linked to order (any non-credit payment)
2. Order is settled
3. PaymentService.handleCustomerPoints() called
4. PointsService.earnPoints() calculates points
5. Points calculated: `floor(total_due / earning_rate * 100) / 100`
6. Customer `earned_points` and `balance` incremented
7. Transaction logged

## Configuration

### Points Earning Rate:
Configurable per tenant via:
- Admin Panel: Settings → General Settings → Points Earning Rate
- Default: 100 (₱100 spent = 1 point)
- Can be adjusted to any value ≥ 1

### Points Redemption Rate:
Fixed: 1 point = 1 currency unit

## Validation Rules

### Backend (SettleBillRequest):
- Customer ID must exist in tenant database
- Customer must have sufficient points balance
- Points used must be > 0
- Validated before payment processing

### Frontend (SettlePayment.vue):
- Customer must be selected
- Selected customer must have sufficient balance
- Error messages:
  - "Please select a customer for points payment."
  - "Insufficient points. Customer has X points but needs Y points."

## Data Flow Summary

```
1. REDEMPTION (Customer pays with points)
   SettlePayment.vue (customer_id, points_used)
   → CartController.settleBill()
   → SettleBillRequest validation
   → PaymentService.settleBill()
   → PaymentService.handleCustomerPoints()
   → PointsService.redeemPoints()
   → Customer.increment('redeemed_points')
   → Customer.decrement('balance')

2. EARNING (Customer earns points from spending)
   CartController.settleBill()
   → PaymentService.settleBill()
   → PaymentService.handleCustomerPoints()
   → PointsService.earnPoints()
   → PointsService.calculatePointsFromAmount()
   → Customer.increment('earned_points')
   → Customer.increment('balance')
```

## Testing Checklist

- [x] Database migration successful (3 tenants)
- [x] PointsService created with all methods
- [x] PaymentService integrated with points logic
- [x] SettleBillRequest validation for points
- [x] Customer model updated with fillable/casts
- [x] UI for customer search in points payment
- [x] Points balance display in customer list
- [x] Validation for sufficient points
- [x] Admin settings for earning rate
- [x] Customer resource shows points columns

## Manual Testing Scenarios

### Scenario 1: Redeem Points
1. Create customer with sufficient balance (or manually set via DB)
2. Create cart/order
3. Go to Settle Payment
4. Select Points payment method
5. Search and select customer
6. Verify balance display
7. Click Settle Bill
8. Verify: Order created, points redeemed, balance decreased

### Scenario 2: Earn Points
1. Create customer
2. Link customer to order (use credit or other payment with customer)
3. Settle bill with non-points payment
4. Check customer record
5. Verify: earned_points and balance increased

### Scenario 3: Insufficient Points
1. Create customer with 50 points balance
2. Create order for ₱100
3. Try to pay with points
4. Verify: Error message about insufficient points
5. Verify: Payment blocked

### Scenario 4: Configure Earning Rate
1. Go to Settings → General Settings
2. Change points_earning_rate to 50
3. Create order for ₱100 with customer
4. Settle with cash
5. Verify: Customer earned 2 points (100 / 50)

## Files Modified

**Backend:**
1. `database/migrations/tenant/2026_01_02_151447_add_points_columns_to_customers_table.php` ✅ Created
2. `app/Settings/GeneralSettings.php` ✅ Updated
3. `app/Services/PointsService.php` ✅ Created
4. `app/Models/Customer.php` ✅ Updated
5. `app/Services/PaymentService.php` ✅ Updated
6. `app/Http/Requests/SettleBillRequest.php` ✅ Updated
7. `app/Filament/Tenant/Pages/ManageSettings.php` ✅ Updated
8. `app/Filament/Tenant/Resources/CustomerResource.php` ✅ Updated

**Frontend:**
9. `resources/js/Pages/Resto/SettlePayment.vue` ✅ Updated

## Notes

- Points system uses decimal(10,2) for fractional points
- All database operations are transactional
- Points earning is automatic for orders with customer_id (except credit and points payments)
- Points redemption requires explicit customer selection and validation
- 1 point = 1 currency unit for redemption simplicity
- Earning rate is configurable per tenant
- System logs all points operations for audit trail
- PaymentType enum already included POINTS case (pre-existing)

## Future Enhancements (Not Implemented)

- Points transaction history table
- Points expiration dates
- Tiered earning rates (VIP customers earn more)
- Partial points redemption (use points + cash)
- Points transfer between customers
- Points adjustment admin interface
- Points activity report
- Email notifications for points earned/redeemed
