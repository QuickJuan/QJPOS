# Visual UI Workflow - Void Modal with OTP

## User Journey: Deleting a Placed Order Item

```
┌────────────────────────────────────────────────────────────────┐
│ 1. ORDER SUMMARY SCREEN                                        │
│                                                                │
│ Order Items:                                                   │
│ ├─ KFC BMG3                         Dine-in  [Delete Icon] ✕  │
│ ├─ Royal in can                     Dine-in  [Delete Icon] ✕  │
│ └─ Ice Tea                          Dine-in  [Delete Icon] ✕  │
│                                                                │
│ Status: This order is PLACED (placed_order: true)             │
│                                                                │
│ User clicks delete button on "Royal in can"                    │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 2. CONFIRMATION DIALOG                                         │
│                                                                │
│  ⚠  Are you sure you want to remove this item?               │
│                                                                │
│                               [Cancel]  [Remove] ◄─ Click    │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 3. SYSTEM CHECK (Backend)                                      │
│                                                                │
│ Backend checks: placed_order = true ✓                          │
│ Backend fetches eligible approvers                             │
│ Backend returns 422 with approvers list                        │
│                                                                │
│ Response:                                                      │
│ {                                                              │
│   "requires_approval": true,                                  │
│   "approvers": [                                               │
│     {id: 1, name: "John Manager", email: "..."},             │
│     {id: 2, name: "Jane Supervisor", email: "..."}           │
│   ]                                                            │
│ }                                                              │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 4. OTP APPROVAL MODAL OPENS                                    │
│                                                                │
│  Enter the reason for voiding this order            [X]       │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │                                                          │ │
│ │ Reason *                                               │ │
│ │ ┌────────────────────────────────────────────────────┐ │ │
│ │ │ Customer requested item removed from order         │ │ │
│ │ │                                                     │ │ │
│ │ └────────────────────────────────────────────────────┘ │ │
│ │                                                          │ │
│ │ Select Approver                                        │ │
│ │ ┌────────────────────────────────────────────────────┐ │ │
│ │ │ John Manager                                   ▼  │ │ │
│ │ │ Choose an approver                               │ │ │
│ │ │ ├─ John Manager (Manager)                        │ │ │
│ │ │ └─ Jane Supervisor (Supervisor)                  │ │ │
│ │ └────────────────────────────────────────────────────┘ │ │
│ │ Only authorized users with OTP enabled can approve     │ │
│ │                                                          │ │
│ │ OTP Code                                               │ │
│ │ ┌────────────────────────────────────────────────────┐ │ │
│ │ │ 000000                                             │ │ │
│ │ └────────────────────────────────────────────────────┘ │ │
│ │ Enter the 6-digit code from approver's auth app        │ │
│ │                                                          │ │
│ │                        [Cancel]  [Save ✓ Enabled]       │ │
│ └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
    ↓ After filling reason and selecting approver
┌────────────────────────────────────────────────────────────────┐
│ 5. WAITING FOR APPROVER OTP CODE                              │
│                                                                │
│ User says to John Manager:                                    │
│ "I need to delete an item from this order. What's your OTP?"  │
│                                                                │
│ John opens his authenticator app (Google Authenticator, etc)  │
│ His app shows: 547821 (6-digit code)                          │
│ Code refreshes every 30 seconds                               │
│                                                                │
│ User types 547821 into the OTP Code field                     │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 6. MODAL FULLY FILLED OUT                                      │
│                                                                │
│  Enter the reason for voiding this order            [X]       │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │                                                          │ │
│ │ Reason *                                               │ │
│ │ ┌────────────────────────────────────────────────────┐ │ │
│ │ │ Customer requested item removed from order         │ │ │
│ │ └────────────────────────────────────────────────────┘ │ │
│ │                                                          │ │
│ │ Select Approver                                        │ │
│ │ ┌────────────────────────────────────────────────────┐ │ │
│ │ │ John Manager                                       │ │ │
│ │ └────────────────────────────────────────────────────┘ │ │
│ │ Only authorized users with OTP enabled can approve     │ │
│ │                                                          │ │
│ │ OTP Code                                               │ │
│ │ ┌────────────────────────────────────────────────────┐ │ │
│ │ │ 547821                          ◄─ Filled in      │ │ │
│ │ └────────────────────────────────────────────────────┘ │ │
│ │ Enter the 6-digit code from approver's auth app        │ │
│ │                                                          │ │
│ │                      [Cancel]  [Save ✓ All Fields OK]   │ │
│ └──────────────────────────────────────────────────────────┘ │
│                          ▲                                     │
│                    All 3 fields filled                        │
│                    Save button enabled!                       │
└────────────────────────────────────────────────────────────────┘
                            ↓ Click Save
┌────────────────────────────────────────────────────────────────┐
│ 7. SUBMISSION (Frontend)                                       │
│                                                                │
│ POST /cart/item/delete-with-approval                          │
│ {                                                              │
│   "cart_item_id": 123,                                        │
│   "approver_id": 1,           ◄─ John Manager's ID           │
│   "otp_code": "547821"        ◄─ From authenticator app       │
│ }                                                              │
│                                                                │
│ Loading... ⏳                                                  │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 8. BACKEND VALIDATION                                          │
│                                                                │
│ Checks:                                                        │
│ ✓ Cart item 123 exists                                        │
│ ✓ Approver (John Manager) exists and is active               │
│ ✓ John has one of: manager, supervisor, OIC, admin           │
│ ✓ John has OTP enabled                                        │
│ ✓ John is in same branch as requester                         │
│ ✓ OTP code 547821 is valid (uses John's secret)              │
│                                                                │
│ Backend creates audit log:                                    │
│ "Cart item 123 deleted with OTP approval"                     │
│  - deleted_by: 5 (current user)                               │
│  - approved_by: 1 (John Manager)                              │
│  - timestamp: 2025-12-26 10:30:45                             │
│                                                                │
│ Item is deleted from database                                 │
│ Returns 200 OK with success message                           │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 9. SUCCESS NOTIFICATION                                        │
│                                                                │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ ✓ Success                                              │ │
│ │                                                         │ │
│ │ Item deleted successfully with approval               │ │
│ │                                              [Dismiss] │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ Modal closes automatically                                    │
│ Cart refreshes and item is gone                               │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 10. FINAL ORDER SUMMARY                                        │
│                                                                │
│ Order Items (Updated):                                         │
│ ├─ KFC BMG3                         Dine-in  [Delete Icon] ✕  │
│ └─ Ice Tea                          Dine-in  [Delete Icon] ✕  │
│                                                                │
│ Royal in can has been removed! ✓                              │
│                                                                │
│ Subtotal: P1,045.00  (decreased from P1,345.00)              │
└────────────────────────────────────────────────────────────────┘
```

## Alternate Path: Invalid OTP Code

```
Step 6 → User enters wrong code: 123456

                            ↓
                     Click Save
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ Backend validates: OTP code incorrect                          │
│ Returns 422: "Invalid OTP code. Please try again."            │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ ERROR NOTIFICATION                                             │
│                                                                │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ ✗ Error                                                │ │
│ │                                                         │ │
│ │ Invalid OTP code. Please try again.                    │ │
│ │                                              [Dismiss] │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ Modal stays open                                              │
│ User can retry with correct code from authenticator           │
│ (Code refreshes every 30 seconds)                             │
└────────────────────────────────────────────────────────────────┘
```

## Alternative Path: Non-Placed Order

```
User clicks delete on a regular (non-placed) order item
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 1. CONFIRMATION DIALOG                                         │
│                                                                │
│  ⚠  Are you sure you want to remove this item?               │
│                                                                │
│                               [Cancel]  [Remove]              │
└────────────────────────────────────────────────────────────────┘
                            ↓ Click Remove
┌────────────────────────────────────────────────────────────────┐
│ 2. BACKEND CHECKS                                              │
│                                                                │
│ Backend check: placed_order = false ✓                          │
│ No approval needed!                                            │
│ Item deleted immediately                                       │
│ Returns 200 OK                                                 │
└────────────────────────────────────────────────────────────────┘
                            ↓
┌────────────────────────────────────────────────────────────────┐
│ 3. SUCCESS NOTIFICATION                                        │
│                                                                │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ ✓ Success                                              │ │
│ │                                                         │ │
│ │ Cart item deleted successfully.                        │ │
│ │                                              [Dismiss] │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ No modal shown!                                                │
│ Item removed directly from order                               │
└────────────────────────────────────────────────────────────────┘
```

## Modal Validation States

```
┌─────────────────────────────────────────────────────────────┐
│ STATE 1: Modal Opens (Empty)                                │
├─────────────────────────────────────────────────────────────┤
│ Reason field: [                    ] ← Empty                │
│ Approver field: [Choose an approver ▼] ← Empty             │
│ OTP field: [                    ] ← Empty & Disabled        │
│ Save button: DISABLED (grayed out)                          │
│ Status: Cannot proceed                                      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ STATE 2: Reason Filled In                                   │
├─────────────────────────────────────────────────────────────┤
│ Reason field: [Customer requested ] ← Filled               │
│ Approver field: [Choose an approver ▼] ← Still empty       │
│ OTP field: [                    ] ← Empty & Disabled        │
│ Save button: DISABLED                                        │
│ Status: Need to select approver                            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ STATE 3: Reason + Approver Selected                         │
├─────────────────────────────────────────────────────────────┤
│ Reason field: [Customer requested ] ← Filled               │
│ Approver field: [John Manager       ▼] ← Selected          │
│ OTP field: [                    ] ← Empty but Enabled ✓    │
│ Save button: DISABLED                                        │
│ Status: Need to enter OTP code                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ STATE 4: All Fields Complete ✓                              │
├─────────────────────────────────────────────────────────────┤
│ Reason field: [Customer requested ] ← Filled               │
│ Approver field: [John Manager       ▼] ← Selected          │
│ OTP field: [547821                ] ← Filled               │
│ Save button: ENABLED ✓ (clickable)                         │
│ Status: Ready to submit!                                    │
└─────────────────────────────────────────────────────────────┘
```

## Key Differences: Placed vs Non-Placed Orders

```
┌────────────────────────────────────────────────────────┐
│ NON-PLACED ORDER                  │  PLACED ORDER      │
├────────────────────────────────────────────────────────┤
│ Click delete                       │ Click delete       │
│       ↓                            │      ↓             │
│ Confirm                            │ Confirm            │
│       ↓                            │      ↓             │
│ Modal (Reason only)                │ Modal (Reason +    │
│       ↓                            │ Approver + OTP)    │
│ Fill reason                        │      ↓             │
│       ↓                            │ Fill all 3 fields  │
│ Click Save                         │      ↓             │
│       ↓                            │ Approver scans OTP │
│ Item deleted ✓                     │      ↓             │
│ (1 field)                          │ Click Save         │
│ (30 seconds)                       │      ↓             │
│                                    │ Item deleted ✓     │
│                                    │ (3 fields)         │
│                                    │ (45 seconds)       │
└────────────────────────────────────────────────────────┘
```

---

**Color Coding in UI:**
- 🟢 Green: Enabled, ready to use
- 🔴 Red: Error state, needs correction
- ⚫ Gray: Disabled, waiting for other input
- 🔵 Blue: Required field indicator (*)

**Icons Used:**
- ✓ = Success/Complete
- ✕ = Delete action
- ▼ = Dropdown menu
- ⏳ = Loading state
- ⚠ = Warning/Confirmation needed

