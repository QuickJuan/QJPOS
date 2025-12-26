# Void Modal with OTP Approval - Visual Guide

## Modal Flow

### Step 1: User Confirms Delete on Placed Order
```
┌─────────────────────────────────────────┐
│ Confirmation Dialog                     │
├─────────────────────────────────────────┤
│ Are you sure you want to remove this    │
│ item?                                    │
│                                          │
│ [Cancel] [Remove]                       │
└─────────────────────────────────────────┘
```

### Step 2: Void Modal Opens (For Placed Orders)
```
┌─────────────────────────────────────────────┐
│ Enter the reason for voiding this order     │
├─────────────────────────────────────────────┤
│                                              │
│ Reason *                                     │
│ ┌───────────────────────────────────────┐  │
│ │ [User enters reason for void]         │  │
│ │ [User enters reason for void]         │  │
│ └───────────────────────────────────────┘  │
│                                              │
│ Select Approver                              │
│ ┌───────────────────────────────────────┐  │
│ │ John Manager              ▼           │  │
│ └───────────────────────────────────────┘  │
│ Only authorized users with OTP enabled     │
│ can approve                                  │
│                                              │
│ OTP Code                                    │
│ ┌──────────────────────────────────────┐  │
│ │ 000000                               │  │
│ └──────────────────────────────────────┘  │
│ Enter the 6-digit code from approver's     │
│ authenticator app                           │
│                                              │
│                    [Cancel] [Save]          │
└─────────────────────────────────────────────┘
```

### Step 3: Void Modal (For Non-Placed Orders)
```
┌─────────────────────────────────────────────┐
│ Enter the reason for voiding this order     │
├─────────────────────────────────────────────┤
│                                              │
│ Reason *                                     │
│ ┌───────────────────────────────────────┐  │
│ │ [User enters reason for void]         │  │
│ └───────────────────────────────────────┘  │
│                                              │
│ (No approver/OTP fields shown)              │
│                                              │
│                    [Cancel] [Save]          │
└─────────────────────────────────────────────┘
```

## Component Structure

```
RequiredReasonModal.vue
├── Dialog Header
│   └── "Enter the reason for voiding this order"
├── Dialog Body
│   ├── Textarea (Reason - Always shown)
│   ├── Dropdown (Approver - Only for placed_order=true)
│   └── InputMask (OTP Code - Only for placed_order=true)
└── Dialog Footer
    ├── Cancel Button
    └── Save Button (disabled until all fields filled for placed orders)
```

## Form Validation States

### Non-Placed Order Item
```
✓ Reason field: Required
✗ Approver field: Hidden
✗ OTP field: Hidden
✓ Save button: Enabled when reason provided
```

### Placed Order Item
```
✓ Reason field: Required
✓ Approver field: Required (shows dropdown)
✓ OTP field: Required (shows 6-digit input)
✓ Save button: Enabled ONLY when all three fields filled
```

## Error States

### Invalid OTP Code
```
┌─────────────────────────────────────────┐
│ Error                                   │
├─────────────────────────────────────────┤
│ Invalid OTP code. Please try again.     │
│                                          │
│                               [Dismiss] │
└─────────────────────────────────────────┘
```

### Approver Without OTP
```
┌─────────────────────────────────────────┐
│ Error                                   │
├─────────────────────────────────────────┤
│ Selected approver does not have OTP     │
│ enabled.                                 │
│                                          │
│                               [Dismiss] │
└─────────────────────────────────────────┘
```

### Approver From Different Branch
```
┌─────────────────────────────────────────┐
│ Error                                   │
├─────────────────────────────────────────┤
│ Approver must be from the same branch.  │
│                                          │
│                               [Dismiss] │
└─────────────────────────────────────────┘
```

## Success Flow

### After Valid OTP Submission
```
┌─────────────────────────────────────────┐
│ ✓ Success                               │
├─────────────────────────────────────────┤
│ Item deleted successfully with approval │
│                                          │
│                                 [Dismiss]│
└─────────────────────────────────────────┘

→ Cart refreshes automatically
→ Item removed from order summary
```

## Data Flow Diagram

```
User clicks Delete on Placed Order Item
            ↓
Confirmation Dialog Appears
            ↓
User Confirms
            ↓
Modal Opens
            ↓
Frontend fetches available approvers
(by making initial DELETE request)
            ↓
Modal displays with:
- Reason textarea
- Approver dropdown (populated)
- OTP input field
            ↓
User fills all 3 fields
            ↓
User clicks Save
            ↓
POST /cart/item/delete-with-approval
{
  "cart_item_id": 123,
  "approver_id": 5,
  "otp_code": "123456"
}
            ↓
Backend validates:
✓ Item exists
✓ Approver has OTP enabled
✓ Approver has required role
✓ Approver in same branch
✓ OTP code valid
            ↓
If all valid → Item deleted
             → Audit log created
             → Success response
            ↓
Frontend reloads cart
Modal closes
Success toast shown
```

## Approver Dropdown Population

Only approvers matching ALL criteria appear:

```
Available Approvers List
├── Filter by Role (Manager, Supervisor, OIC, Admin, Super Admin)
├── Filter by OTP Enabled Status (otp_enabled = true)
├── Filter by OTP Secret Configured (otp_secret NOT NULL)
├── Filter by Branch (same as requesting user's branch)
│
Result: Array of eligible approver objects
├── User 1
│   ├── id: 5
│   ├── name: "John Manager"
│   └── email: "john@example.com"
├── User 2
│   ├── id: 8
│   ├── name: "Jane Supervisor"
│   └── email: "jane@example.com"
└── ...
```

## OTP Input Behavior

```
Field: OTP Code (InputMask "999999")

Characteristics:
- Accepts only 6 digits
- Auto-masks input to format: 000000
- Placeholder shows: 000000
- Does not allow non-numeric characters
- Only enables after approver selection
- Part of form validation (required for placed orders)
```

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Cancel | Esc or Cancel button |
| Submit | Click Save button |
| Focus OTP field | Tab through form |

## Accessibility Features

- Proper label elements for form fields
- Required indicators (*) on mandatory fields
- Descriptive help text below fields
- Form validation prevents incomplete submissions
- Clear error messages for failed operations
- Toast notifications for success/failure feedback

