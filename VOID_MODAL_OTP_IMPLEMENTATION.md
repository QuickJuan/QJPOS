# Void Modal with OTP Approval - Implementation Summary

## Changes Made

### 1. RequiredReasonModal.vue
Updated the modal component to include approver selection and OTP input fields.

**New Features:**
- **Approver Dropdown**: Conditionally displayed when item is from a placed order
- **OTP Input Field**: 6-digit code input with InputMask component
- **Smart Fetching**: Automatically fetches available approvers when modal opens
- **Validation**: Save button disabled until both approver and OTP are provided for placed orders
- **Conditional Display**: Extra fields only show for placed orders (`placed_order: true`)

**Form Fields:**
1. **Reason** (always shown): Textarea for void reason
2. **Select Approver** (placed orders only): Dropdown with available approvers
3. **OTP Code** (placed orders only): 6-digit input field

### 2. OrderSummary.vue
Updated the `handleRequiredReason` function to handle two workflows:

**Workflow 1 - Placed Order with OTP Approval:**
- Sends POST request to `/cart/item/delete-with-approval`
- Includes: `cart_item_id`, `approver_id`, `otp_code`
- On success: Shows success toast and reloads cart
- On error: Shows specific error message from server

**Workflow 2 - Regular Void (non-placed orders):**
- Uses existing PUT request to `/cart/item/void-cart`
- Includes: reason field
- Original behavior preserved

## Frontend Flow

### User Interaction
1. User clicks delete button on a placed order item
2. Confirmation dialog appears asking for confirmation
3. If confirmed:
   - **If placed order**: Modal opens with Reason, Approver, and OTP fields
   - **If regular order**: Item deleted directly
4. User fills in reason
5. User selects approver from dropdown (eligible approvers only)
6. User enters OTP code from approver's authenticator app
7. User clicks "Save"
8. System submits to backend with all required data
9. Success/error toast displayed based on response

### Modal Validation
- Save button is disabled if:
  - Placed order AND no approver selected
  - Placed order AND no OTP code entered
- Non-placed orders only require reason

## API Endpoints Used

### For Placed Orders
```
POST /cart/item/delete-with-approval
{
  "cart_item_id": 123,
  "approver_id": 5,
  "otp_code": "123456"
}
```

### For Regular Orders
```
PUT /cart/item/void-cart/{cartItemId}
{
  "reason": "Item no longer needed"
}
```

## Eligible Approvers

Only users with the following criteria appear in the dropdown:
- Has one of these roles:
  - manager
  - supervisor
  - oic
  - admin
  - super admin
- Has OTP enabled (`otp_enabled = true`)
- Has OTP secret configured
- Is in the same branch as the requesting user

## Error Handling

**Invalid OTP Code:**
- Message: "Invalid OTP code. Please try again."
- User can retry with correct code

**Approver Not Authorized:**
- Message: "Selected user does not have the required role for approval."

**Approver OTP Not Enabled:**
- Message: "Selected approver does not have OTP enabled."

**Approver From Different Branch:**
- Message: "Approver must be from the same branch."

## Security Features

1. **OTP Validation**: Uses TOTP (Time-based One-Time Password) with 30-second window
2. **Role-Based Access**: Only specific roles can approve deletions
3. **Branch Isolation**: Approvers must be in same branch
4. **Audit Logging**: All OTP-approved deletions logged with:
   - Cart item ID
   - User who initiated deletion
   - Approver user ID
   - Timestamp
5. **Frontend Validation**: Modal prevents submission without all required fields

## Testing Checklist

- [ ] Non-placed order items delete directly when confirmed
- [ ] Placed order items show modal with approver dropdown
- [ ] OTP field only appears after selecting approver
- [ ] Save button disabled when fields missing for placed orders
- [ ] Approver dropdown only shows eligible users (correct roles + OTP enabled)
- [ ] Valid OTP code successfully deletes item
- [ ] Invalid OTP code shows error message
- [ ] Success toast shows after successful deletion
- [ ] Cart automatically refreshes after successful OTP approval
- [ ] Approvers from different branches don't appear in dropdown

## Notes

- The approvers list is fetched by attempting the initial delete, which triggers the 422 response with approver list
- The modal automatically resets all fields after submission
- The system maintains backward compatibility with non-placed orders
- Loading state is shown while fetching approvers from the backend

