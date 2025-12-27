# Color Palette Visual Reference

## Primary Color - Blue (#669bbc)

Used for main actions, links, and primary UI elements.

```
50:   #f0f6fa  ████████████████ Very Light Background
100:  #dbeaf3  ████████████████ Light Background
200:  #b3d1e6  ████████████████ Light Border
300:  #8bb8d9  ████████████████ Medium Light
400:  #669bbc  ████████████████ Light Standard
500:  #5688a8  ████████████████ DEFAULT/Standard
600:  #467594  ████████████████ Hover State ← Most Common
700:  #366280  ████████████████ Active State
800:  #264f6c  ████████████████ Dark
900:  #1a3c58  ████████████████ Very Dark
```

**Usage:**
```css
.bg-primary              /* Default: #669bbc */
.bg-primary-50          /* Very light backgrounds */
.hover:bg-primary-600   /* Hover state (darker) */
.active:bg-primary-700  /* Active/pressed state */
.text-primary-600       /* Primary text */
```

---

## Secondary Color - Dark Blue (#003049)

Alternative color for dark themes and secondary actions.

```
50:   #e6f0f7  ████████████████ Very Light
100:  #cce0ef  ████████████████ Light
200:  #99c1df  ████████████████ Light Border
300:  #66a2cf  ████████████████ Medium
400:  #3383bf  ████████████████ Medium Dark
500:  #0064af  ████████████████ Dark Blue
600:  #00519f  ████████████████ Darker Blue
700:  #003e8f  ████████████████ Very Dark Blue
800:  #003049  ████████████████ DEFAULT/Dark (Header)
900:  #002439  ████████████████ Darkest
```

**Usage:**
```css
.bg-secondary          /* Header background */
.bg-secondary-800      /* Dark header (#003049) */
.text-secondary        /* Text on light bg */
```

---

## Tertiary Color - Gold/Accent (#fdf0d5)

Accent color for highlights and special elements.

```
50:   #fefcf9  ████████████████ Palest
100:  #fdf0d5  ████████████████ DEFAULT/Light Gold
200:  #fbe8c1  ████████████████ Medium Gold
300:  #f9e0ad  ████████████████ Darker Gold
400:  #f7d899  ████████████████ Even Darker
500:  #f5d085  ████████████████ Rich Gold
600:  #e6c077  ████████████████ Deep Gold
700:  #d7b069  ████████████████ Dark Gold
800:  #c8a05b  ████████████████ Very Dark
900:  #b9904d  ████████████████ Darkest Gold
```

**Usage:**
```css
.bg-tertiary           /* Gold background (#fdf0d5) */
.text-tertiary         /* Gold text */
```

---

## Warning Color - Amber (#f59e0b)

For warnings, important actions, and alerts.

```
50:   #fffbeb  ████████████████ Extremely Light
100:  #fef3c7  ████████████████ Very Light
200:  #fde68a  ████████████████ Light
300:  #fcd34d  ████████████████ Medium Light
400:  #fbbf24  ████████████████ Medium
500:  #f59e0b  ████████████████ DEFAULT/Standard ← Amber
600:  #d97706  ████████████████ Hover State (Darker)
700:  #b45309  ████████████████ Active State
800:  #92400e  ████████████████ Very Dark
900:  #78350f  ████████████████ Darkest
```

**Usage:**
```css
.bg-warning            /* Close Shift button (#f59e0b) */
.hover:bg-warning-600  /* Hover: darker amber (#d97706) */
.bg-warning-50         /* Light amber background */
.text-warning-900      /* Dark amber text */
```

**Common Applications:**
- 🔵 Close Shift Button (Primary Action)
- ⚠️ Warning Alerts
- 🚨 Important Notifications
- ⏰ Time-sensitive Operations

---

## Error Color - Red (#dc2626)

For errors, deletions, and destructive actions.

```
50:   #fef2f2  ████████████████ Very Light Pink
100:  #fee2e2  ████████████████ Light Pink
200:  #fecaca  ████████████████ Light Red
300:  #fca5a5  ████████████████ Medium Light
400:  #f87171  ████████████████ Medium Red
500:  #ef4444  ████████████████ Standard Red
600:  #dc2626  ████████████████ DEFAULT/Dark Red ← Hover
700:  #c1121f  ████████████████ Active (Darkest)
800:  #991b1b  ████████████████ Very Dark
900:  #780000  ████████████████ Darkest Maroon
```

**Usage:**
```css
.bg-error              /* Standard error (#dc2626) */
.hover:bg-error-700    /* Hover: darkest red (#c1121f) */
.bg-error-50           /* Light error background */
.text-error-900        /* Dark error text */
```

**Common Applications:**
- 🗑️ Delete Buttons
- ❌ Error Messages
- 🔴 Refund Operations
- ⛔ Blocked/Disabled States

---

## Success Color - Green (#22c55e)

For confirmations and positive feedback.

```
50:   #f0fdf4  ████████████████ Very Light Green
100:  #dcfce7  ████████████████ Light Green
200:  #bbf7d0  ████████████████ Light Green
300:  #86efac  ████████████████ Medium Light
400:  #4ade80  ████████████████ Medium Green
500:  #22c55e  ████████████████ DEFAULT/Standard ← Green
600:  #16a34a  ████████████████ Hover (Darker Green)
700:  #15803d  ████████████████ Active (Dark Green)
800:  #166534  ████████████████ Very Dark
900:  #14532d  ████████████████ Darkest
```

**Usage:**
```css
.bg-success            /* Confirm button (#22c55e) */
.hover:bg-success-600  /* Hover: darker green (#16a34a) */
.bg-success-50         /* Light green background */
.text-success-900      /* Dark green text */
```

**Common Applications:**
- ✅ Confirmation Messages
- 💚 Success Notifications
- 📊 Positive Indicators
- 🎉 Celebratory States

---

## Neutral Colors - Gray Scale

For text, borders, backgrounds, and general UI elements.

```
50:   #fafafa  ████████████████ Lightest (Off-white)
100:  #f5f5f5  ████████████████ Very Light Background
200:  #e5e5e5  ████████████████ Light Border/Divider
300:  #d4d4d4  ████████████████ Medium Light Border
400:  #a3a3a3  ████████████████ Medium Gray Text
500:  #737373  ████████████████ Medium Gray
600:  #525252  ████████████████ Dark Gray
700:  #404040  ████████████████ Darker Gray
800:  #262626  ████████████████ Very Dark
900:  #171717  ████████████████ Darkest (Near Black)
```

**Usage:**
```css
/* Backgrounds */
.bg-neutral-50         /* Off-white backgrounds */
.bg-neutral-100        /* Light gray backgrounds */

/* Borders */
.border-neutral-200    /* Light borders */
.border-neutral-300    /* Medium borders */

/* Text */
.text-neutral-900      /* Primary text (dark) */
.text-neutral-700      /* Secondary text */
.text-neutral-600      /* Tertiary text */
.text-neutral-500      /* Muted text */
.text-neutral-400      /* Disabled/Placeholder text */
```

**Text Hierarchy:**
```
Dark:    text-neutral-900    ████████████████ Main Headings
Medium:  text-neutral-700    ████████████████ Subheadings
Regular: text-neutral-600    ████████████████ Body Text
Light:   text-neutral-500    ████████████████ Secondary Info
Muted:   text-neutral-400    ████████████████ Disabled/Hints
```

---

## Color Combinations

### Recommended Button Pairs

```
Primary Button
.bg-primary           → #669bbc
.hover:bg-primary-600 → #467594
.active:bg-primary-700 → #366280

Warning Button (Close Shift)
.bg-warning           → #f59e0b
.hover:bg-warning-600 → #d97706
.active:bg-warning-700 → #b45309

Error Button (Delete)
.bg-error             → #dc2626
.hover:bg-error-700   → #c1121f
.active:bg-error-800  → #991b1b

Success Button (Confirm)
.bg-success           → #22c55e
.hover:bg-success-600 → #16a34a
.active:bg-success-700 → #15803d
```

### Recommended Alert Combinations

```
Warning Alert
.bg-warning-50        → #fffbeb (Light background)
.border-warning-500   → #f59e0b (Border)
.text-warning-900     → #78350f (Dark text)

Error Alert
.bg-error-50          → #fef2f2 (Light background)
.border-error-500     → #ef4444 (Border)
.text-error-900       → #780000 (Dark text)

Success Alert
.bg-success-50        → #f0fdf4 (Light background)
.border-success-500   → #22c55e (Border)
.text-success-900     → #14532d (Dark text)
```

### Recommended Card Combinations

```
Light Card (Default)
.bg-white             → #ffffff (Background)
.border-neutral-200   → #e5e5e5 (Border)
.text-neutral-900     → #171717 (Title)
.text-neutral-600     → #525252 (Body)

Dark Card
.bg-neutral-800       → #262626 (Background)
.border-neutral-700   → #404040 (Border)
.text-white           → #ffffff (Title)
.text-neutral-300     → #d4d4d4 (Body)
```

---

## Color Usage by Component

### Header
- Background: `bg-slate-950/95` or `bg-white`
- Border: `border-white/10` (dark) or `border-neutral-200` (light)
- Text: `text-white` (dark) or `text-neutral-900` (light)
- Buttons: Primary actions use `bg-primary hover:bg-primary-600`

### Button States

| State | Class |
|-------|-------|
| **Normal** | `bg-primary` |
| **Hover** | `hover:bg-primary-600` |
| **Active** | `active:bg-primary-700` |
| **Disabled** | `bg-neutral-200 text-neutral-400 cursor-not-allowed` |
| **Loading** | `opacity-50 cursor-not-allowed` |

### Form Elements
- Input Border: `border-neutral-300`
- Focus Ring: `focus:ring-2 focus:ring-primary`
- Label: `text-neutral-700`
- Helper Text: `text-neutral-500 text-sm`
- Error Text: `text-error`

### Cards
- Background: `bg-white`
- Border: `border border-neutral-200`
- Shadow: `shadow-sm` or `shadow-lg`
- Title: `text-neutral-900 font-semibold`
- Body: `text-neutral-600`

---

## Accessibility Notes

### Contrast Ratios (WCAG AA Standard: 4.5:1)

✅ **Good Combinations:**
- `text-neutral-900` on `bg-white` → 16.5:1 (Excellent)
- `text-white` on `bg-primary-600` → 6.3:1 (Good)
- `text-white` on `bg-warning` → 5.2:1 (Good)
- `text-white` on `bg-error` → 5.9:1 (Good)

⚠️ **Be Careful:**
- `text-neutral-500` on `bg-white` → 4.5:1 (Minimum)
- `text-neutral-400` needs darker background

❌ **Avoid:**
- `text-neutral-400` on `bg-neutral-50` → 2.5:1 (Poor)
- Light text on light backgrounds

### Color Blindness
- Don't rely on color alone to convey information
- Always use text labels and icons in addition to color
- Test with [Colorblind Web Page Filter](https://www.color-blindness.com/)

---

## Implementation Example

Here's a complete component using the color palette:

```vue
<template>
  <div class="bg-white rounded-lg border border-neutral-200 p-6 shadow-md">
    <!-- Header -->
    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Order Summary</h2>
    
    <!-- Content -->
    <p class="text-neutral-600 mb-6">Total: ₱1,500.00</p>
    
    <!-- Alerts -->
    <div v-if="hasDiscount" class="bg-success-50 border border-success-200 text-success-800 p-3 rounded mb-4">
      ✓ Discount applied: 10%
    </div>
    
    <!-- Action Buttons -->
    <div class="flex gap-3">
      <button class="px-4 py-2 border border-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-50">
        Cancel
      </button>
      <button class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg">
        Confirm
      </button>
    </div>
  </div>
</template>
```

Result: Clean, accessible, and themed!

---

## Color Resources

- **Hex Color Tools:** [ColorHexa](https://www.colorhexa.com/)
- **Contrast Checker:** [WebAIM](https://webaim.org/resources/contrastchecker/)
- **Colorblind Simulator:** [Color Blindness](https://www.color-blindness.com/)
- **Tailwind Colors:** [Tailwind CSS Docs](https://tailwindcss.com/docs/customizing-colors)

---

**Created:** December 2025 | **Version:** 1.0
