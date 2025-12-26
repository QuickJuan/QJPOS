# Cart Item Deletion with OTP Approval

## Overview
This feature adds OTP (One-Time Password) approval workflow for deleting cart items from placed orders. When a user attempts to delete an item from a placed order, they must first select an authorized approver and provide that approver's OTP code.

## Workflow

### 1. Attempt Delete on Placed Order
When the frontend attempts to delete a cart item:
- If the item is from a **regular order** (not yet placed): Delete directly ✓
- If the item is from a **placed order**: Return 422 response with approvers list requiring approval

### 2. Response Structure (422 - Requires Approval)
```json
{
  "success": false,
  "requires_approval": true,
  "cart_item_id": 123,
  "approvers": [
    {
      "id": 1,
      "name": "John Manager",
      "email": "john@example.com"
    },
    {
      "id": 2,
      "name": "Jane Supervisor",
      "email": "jane@example.com"
    }
  ],
  "message": "This item is from a placed order. OTP approval from an authorized user is required to delete it."
}
```

### 3. Show OTP Approval Modal
When receiving a 422 response with `requires_approval: true`:
1. Display a modal with title: "OTP Approval Required"
2. Show a dropdown to select the approver from the `approvers` list
3. Add an input field for the OTP code (6 digits)
4. Include action buttons: "Approve & Delete" and "Cancel"

### 4. Submit OTP Approval
Send POST request to: `/cart/item/delete-with-approval`

**Request Body:**
```json
{
  "cart_item_id": 123,
  "approver_id": 1,
  "otp_code": "123456"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Cart item deleted successfully with approval."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Invalid OTP code. Please try again."
}
```

## Eligible Approvers

Only users with the following roles can approve cart item deletion:
- `manager`
- `supervisor`
- `oic` (Officer In Charge)
- `admin`
- `super admin`

**Requirements for approvers:**
- Must have OTP enabled (`otp_enabled = true`)
- Must have OTP secret configured (`otp_secret` is not null)
- Must be in the same branch as the requesting user
- Must have one of the eligible roles

## Backend Implementation

### CartController Methods

#### deleteCartItem()
```php
public function deleteCartItem(int $cartItemId): RedirectResponse|JsonResponse
```
- Checks if the cart item is from a placed order
- If placed: Returns 422 with approvers list
- If not placed: Deletes directly
- Uses role-based filtering for eligible approvers

#### deleteCartItemWithApproval()
```php
public function deleteCartItemWithApproval(Request $request): JsonResponse
```
- Validates input: `cart_item_id`, `approver_id`, `otp_code`
- Verifies approver:
  - Has OTP enabled
  - Has required role
  - Is in same branch
- Validates OTP using `OtpSecretService::verifyCode()`
- Logs approval action with user IDs and timestamp
- Deletes cart item on success

### Service Used
- `OtpSecretService::verifyCode($secret, $code)` - Validates 6-digit TOTP code

## Routes

| Method | Route | Handler | Purpose |
|--------|-------|---------|---------|
| DELETE | `/cart/item/{cartItemId}` | `deleteCartItem()` | Check for approval need |
| POST | `/cart/item/delete-with-approval` | `deleteCartItemWithApproval()` | Submit OTP approval |

## Frontend Component Example (Vue 3)

```vue
<template>
  <div>
    <!-- OTP Approval Modal -->
    <Dialog 
      v-model:visible="showOtpModal" 
      header="OTP Approval Required"
      modal
      @hide="resetModal"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Select Approver</label>
          <Dropdown
            v-model="selectedApprover"
            :options="approvers"
            optionLabel="name"
            optionValue="id"
            placeholder="Choose an approver"
            class="w-full"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Enter OTP Code</label>
          <InputMask
            v-model="otpCode"
            mask="999999"
            placeholder="000000"
            class="w-full"
          />
        </div>

        <p class="text-sm text-gray-600">
          Ask the selected approver to enter their 6-digit OTP code from their authenticator app.
        </p>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          severity="secondary"
          @click="showOtpModal = false"
        />
        <Button
          label="Approve & Delete"
          @click="submitOtpApproval"
          :loading="loading"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const showOtpModal = ref(false)
const approvers = ref([])
const selectedApprover = ref(null)
const otpCode = ref('')
const loading = ref(false)
const currentCartItemId = ref(null)

const deleteCartItem = async (cartItemId) => {
  try {
    // Initial delete attempt
    const response = await axios.delete(`/cart/item/${cartItemId}`)
    // Success - item deleted directly
    showSuccessToast('Item deleted successfully')
  } catch (error) {
    if (error.response?.status === 422 && error.response?.data?.requires_approval) {
      // Approval needed
      currentCartItemId.value = cartItemId
      approvers.value = error.response.data.approvers
      showOtpModal.value = true
    } else {
      showErrorToast(error.response?.data?.message || 'Error deleting item')
    }
  }
}

const submitOtpApproval = async () => {
  if (!selectedApprover.value || !otpCode.value) {
    showErrorToast('Please select an approver and enter the OTP code')
    return
  }

  loading.value = true
  try {
    await axios.post('/cart/item/delete-with-approval', {
      cart_item_id: currentCartItemId.value,
      approver_id: selectedApprover.value,
      otp_code: otpCode.value
    })
    
    showSuccessToast('Item deleted successfully with approval')
    showOtpModal.value = false
    // Refresh cart items
    await refreshCart()
  } catch (error) {
    showErrorToast(error.response?.data?.message || 'OTP validation failed')
  } finally {
    loading.value = false
  }
}

const resetModal = () => {
  selectedApprover.value = null
  otpCode.value = ''
  currentCartItemId.value = null
  approvers.value = []
}
</script>
```

## Security Considerations

1. **OTP Validation Window**: 30-second time window (standard TOTP)
2. **Approval Logging**: All OTP-approved deletions are logged with:
   - Cart item ID
   - User who initiated deletion
   - Approver user ID
   - Timestamp
3. **Branch Isolation**: Approvers must be from the same branch
4. **Role-Based Access**: Only specific roles can approve
5. **OTP Requirement**: Approver must have OTP enabled and configured

## Testing in Filament

1. **Setup OTP for Users:**
   - Go to Users resource in Filament
   - Click "Generate OTP" on a manager/supervisor/admin user
   - Save QR code and enable OTP

2. **Test OTP Verification:**
   - Use "Test OTP" action in Filament
   - Enter the code from your authenticator app
   - Verify it's accepted

3. **Test Cart Deletion Workflow:**
   - Place an order with items
   - Try deleting an item
   - Should show approval modal with available approvers
   - Enter OTP from approver's authenticator app
   - Confirm deletion

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Approver does not have OTP enabled" | Generate OTP for user in Filament Users resource |
| "Invalid OTP code" | Ensure code is within 30-second window and from approver's app |
| "Approver must be from same branch" | Select approver who is assigned to the same branch |
| "User does not have required role" | Assign manager/supervisor/OIC/admin/super admin role to user |
| Modal doesn't appear | Check browser console for API errors |

