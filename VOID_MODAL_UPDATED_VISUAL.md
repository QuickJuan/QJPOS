# Updated Void Modal - Visual Guide

## Modal with All Fixes Applied

```
┌─────────────────────────────────────────────────────┐
│ Enter the reason for voiding this order        [X] │
├─────────────────────────────────────────────────────┤
│                                                     │
│ Reason *                                           │
│ ┌───────────────────────────────────────────────┐  │
│ │ [Textarea for reason - always shown]         │  │
│ │                                               │  │
│ └───────────────────────────────────────────────┘  │
│                                                     │
│ Select Approver                                    │
│ ┌───────────────────────────────────────────────┐  │
│ │ John Manager                            ▼   │  │
│ │ Available Options:                          │  │
│ │ ├─ John Manager (Admin)                     │  │
│ │ ├─ Jane Supervisor (Supervisor)            │  │
│ │ ├─ Mike OIC (Officer In Charge)            │  │
│ │ └─ Sarah Manager (Manager)                 │  │
│ │                                             │  │
│ │ (Loading... while fetching)                │  │
│ │ or                                          │  │
│ │ (Red) No approvers available. Please      │  │
│ │       ensure users with Admin/Manager/    │  │
│ │       Supervisor/OIC role have OTP enabled.│  │
│ └───────────────────────────────────────────────┘  │
│ Only authorized users (Admin, Manager,             │
│ Supervisor, OIC) with OTP enabled can approve      │
│                                                     │
│ OTP Code                        (NEW - Text Input) │
│ ┌───────────────────────────────────────────────┐  │
│ │ Enter 6-digit code          │              │  │
│ └───────────────────────────────────────────────┘  │
│ Enter the 6-digit code from approver's auth app    │
│                                                     │
│                        [Cancel]  [Save ✓]         │
└─────────────────────────────────────────────────────┘
```

## Key Improvements

### 1. OTP Input Field (NEW)
```
OLD: InputMask component with format mask "999999"
NEW: Standard text input with:
     - Accepts only digits (auto-filters non-numeric)
     - Max 6 characters
     - Shows "Enter 6-digit code" placeholder
     - Only appears after selecting approver
     - Validates 6 digits required before save
```

### 2. Approver Dropdown (FIXED)
```
BEFORE: Empty dropdown (no users showing)
AFTER: Populated with eligible approvers:
       - Admin role users ✓ (was missing)
       - Manager role users ✓
       - Supervisor role users ✓
       - OIC (Officer In Charge) users ✓
       
       Only if they have:
       - OTP enabled (otp_enabled = true)
       - In same branch as current user
```

### 3. Error Handling (IMPROVED)
```
Shows helpful error when:
- No approvers available
- Message: "No approvers available. Please ensure 
           users with Admin/Manager/Supervisor/OIC 
           role have OTP enabled."
- Color: Red text
- Shows when loading is false and list is empty
```

### 4. Loading State (NEW)
```
While fetching approvers:
- Dropdown shows loading spinner
- Message: "Loading approvers..."
- Cannot interact until loaded
```

## Form Validation States

### When Item is NOT Placed Order
```
┌────────────────────────────────┐
│ Reason *                       │
│ ┌──────────────────────────┐   │
│ │ [Textarea]               │   │
│ └──────────────────────────┘   │
│                                │
│ (Approver field: HIDDEN)       │
│ (OTP field: HIDDEN)            │
│                                │
│ [Cancel] [Save - Disabled]     │
│          ↓ Enable when Reason  │
│            filled              │
└────────────────────────────────┘
```

### When Item IS Placed Order

#### Step 1: Empty
```
Reason: [empty]              ← Required
Approver: [empty]            ← Required
OTP: [hidden]                ← Will show after approver
[Cancel] [Save - DISABLED]   ← Need all 3 fields
```

#### Step 2: Reason Filled
```
Reason: [Customer requested]  ← ✓ Filled
Approver: [Choose...]         ← Still empty
OTP: [hidden]                 ← Will show after approver
[Cancel] [Save - DISABLED]    ← Still need approver
```

#### Step 3: Approver Selected
```
Reason: [Customer requested]  ← ✓ Filled
Approver: [John Manager]      ← ✓ Selected
OTP: [Enter 6-digit code]     ← ✓ Now visible
[Cancel] [Save - DISABLED]    ← Still need OTP code
```

#### Step 4: OTP Code Entered (6 digits)
```
Reason: [Customer requested]  ← ✓ Filled
Approver: [John Manager]      ← ✓ Selected
OTP: [547821]                 ← ✓ 6 digits
[Cancel] [Save - ENABLED ✓]   ← Ready to submit!
```

## Data Flow Diagram

```
Modal Opens
    ↓
Check if placed_order = true
    ↓
IF placed_order:
  ├─ Show Reason textarea
  ├─ Show Approver dropdown
  ├─ Call GET /cart/item/{id}/approvers
  │   ↓
  │   Backend queries users with:
  │   - Role IN (admin, manager, supervisor, oic)
  │   - otp_enabled = true
  │   - branch_id = current_user_branch_id
  │   ↓
  │   Returns [John Manager, Jane Supervisor, ...]
  │   ↓
  ├─ Populate dropdown ✓
  ├─ Wait for approver selection
  └─ Show OTP input
      ↓
      User enters 6-digit code
      ↓
      Click Save
      ↓
      Submit POST /cart/item/delete-with-approval
      ├─ cart_item_id: 123
      ├─ approver_id: 1
      └─ otp_code: "547821"
         ↓
         Backend validates:
         ✓ Item exists
         ✓ Approver exists
         ✓ Approver has OTP enabled
         ✓ Approver in same branch
         ✓ OTP code valid (checked against approver secret)
         ↓
         Item deleted ✓
         Audit log created
         Toast: "Item deleted successfully"
         Cart refreshes
ELSE (not placed_order):
  └─ Show Reason textarea only
     ↓
     Click Save
     ↓
     Submit PUT /cart/item/void/{id}
     └─ No OTP needed
```

## OTP Input Behavior

```
Input Properties:
- Type: "text"
- Max Length: 6 characters
- Input Mode: "numeric" (mobile keyboard optimization)
- Placeholder: "Enter 6-digit code"

On Input Event:
- Auto-filter: Removes non-numeric characters
  Example: User types "5a4b7c8" → Becomes "547"
- Max 6 digits enforced

Validation:
- Field required for save
- Minimum 6 digits required
- Cannot submit until: otpCode.length === 6

Example Inputs:
✓ "123456"     → Valid
✓ "000000"     → Valid (all zeros ok)
✗ "12345"      → Invalid (only 5 digits)
✗ "1234567"    → Invalid (max 6, won't accept 7th)
✗ "12a4b6"     → Filtered to "1246" (non-numeric removed)
```

## Dropdown Behavior

```
Approver Selection Dropdown:

Display Format: "{name}" from approvers array
Option Value: {id} from approvers array

Example Data:
[
  { id: 1, name: "John Manager", email: "john@..." },
  { id: 2, name: "Jane Supervisor", email: "jane@..." },
  { id: 3, name: "Mike OIC", email: "mike@..." }
]

Rendered:
┌─────────────────────────┐
│ Choose an approver  ▼  │ ← When empty
│ John Manager            │ ← When selected
│ ─────────────────────── │
│ John Manager            │ ← Option 1
│ Jane Supervisor         │ ← Option 2
│ Mike OIC                │ ← Option 3
└─────────────────────────┘

Loading State:
┌─────────────────────────┐
│ Loading approvers...  ▼│
└─────────────────────────┘

Empty State:
┌─────────────────────────┐
│ Choose an approver  ▼   │
│                         │
│ (Red) No approvers      │
│ available. Please       │
│ ensure users...         │
└─────────────────────────┘
```

## Button States

### Save Button
```
DISABLED When:
- Item is placed_order AND (
    reason is empty OR
    approver not selected OR
    otpCode.length < 6
  )

ENABLED When:
- Item is NOT placed_order AND reason filled
- OR Item is placed_order AND all 3 fields filled

Class: "bg-primary hover:bg-primary-600"
```

## Responsive Design

```
Mobile (< 640px):
┌──────────────┐
│ Modal        │
│ (28rem width)│
│              │
│ Fields stack │
│ vertically   │
│              │
│ Buttons:     │
│ [Cancel]     │
│ [Save]       │
└──────────────┘

Numeric Keyboard:
- input[inputmode="numeric"] triggers
- Shows only numbers + dot on mobile
- Better UX for entering 6-digit code
```

## Accessibility Features

```
Labels:
- Each field has <label> element
- Required fields marked with "*"

Help Text:
- Descriptive <small> under each field
- Error messages in red
- Loading states indicate progress

Focus Management:
- Tab through fields in order: Reason → Approver → OTP
- Enter key could submit (browser default)

Screen Reader:
- Proper label associations
- Loading state announced
- Error messages readable
```

## Console Debugging

```javascript
// What shows in browser console:

// When fetching approvers:
"Approvers received: Array [...]"

// When dropdown populates:
[
  { id: 1, name: "John Manager", email: "..." },
  { id: 2, name: "Jane Supervisor", email: "..." }
]

// When error occurs:
"Error fetching approvers: Error: Request failed"
```

---

**Color Coding:**
- 🟢 Green: Active/enabled
- 🔴 Red: Error/warning
- ⚫ Gray: Disabled/inactive
- 🔵 Blue: Loading/pending

**Icons:**
- ✓ = Complete/enabled
- ✕ = Delete/cancel
- ▼ = Dropdown arrow
- ⏳ = Loading spinner

