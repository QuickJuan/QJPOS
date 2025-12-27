# QuickJuan Theme - Quick Reference Guide

## 🎨 Color Palette Overview

### Primary Colors (Use for main actions and links)
- **Primary:** `bg-primary` | `text-primary` | `#669bbc`
- **Primary Hover:** `hover:bg-primary-600` | `#467594`
- **Primary Dark:** `bg-primary-700` | `#366280`

### Status Colors

| Status | Color Class | Usage |
|--------|------------|-------|
| **Warning** | `bg-warning` | Close Shift, Important Actions |
| **Error** | `bg-error` | Deletions, Refunds, Errors |
| **Success** | `bg-success` | Confirmations, Positive Feedback |
| **Secondary** | `bg-secondary` | Alternative UI, Dark Themes |

### Neutral Colors (For text, borders, backgrounds)
- **Light:** `bg-neutral-50` (backgrounds) | `#fafafa`
- **Medium:** `border-neutral-200` (borders) | `#e5e5e5`
- **Dark:** `text-neutral-900` (primary text) | `#171717`

## 🚀 Quick Start Examples

### Create a Button
```vue
<!-- Primary Button -->
<button class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg">
  Confirm
</button>

<!-- Warning Button (Close Shift) -->
<button class="bg-warning hover:bg-warning-600 text-white px-4 py-2 rounded-lg">
  Close Shift
</button>

<!-- Error Button (Delete) -->
<button class="bg-error hover:bg-error-700 text-white px-4 py-2 rounded-lg">
  Delete
</button>
```

### Create a Card
```vue
<div class="bg-white border border-neutral-200 rounded-lg p-6">
  <h3 class="text-neutral-900 font-semibold">Card Title</h3>
  <p class="text-neutral-600">Card content here</p>
</div>
```

### Create an Alert
```vue
<!-- Warning Alert -->
<div class="bg-warning-50 border-l-4 border-warning-500 p-4 rounded">
  <p class="text-warning-900 font-semibold">Warning Message</p>
</div>

<!-- Error Alert -->
<div class="bg-error-50 border-l-4 border-error-500 p-4 rounded">
  <p class="text-error-900">Error Message</p>
</div>

<!-- Success Alert -->
<div class="bg-success-50 border-l-4 border-success-500 p-4 rounded">
  <p class="text-success-900">Success Message</p>
</div>
```

## 📍 Color Shades Reference

Every color has 9 shades for different use cases:

```
50   → Lightest (backgrounds)
100  → Light backgrounds
200  → Light borders
300  → Medium borders
400  → Medium shade
500  → Default/Standard
600  → Hover state
700  → Active/Pressed state
800  → Dark
900  → Darkest
```

**Example with Primary Color:**
```
bg-primary-50   → Very light blue background
bg-primary-100  → Light blue background
border-primary-200 → Light border
bg-primary-500  → Standard blue (DEFAULT)
bg-primary-600  → Darker blue for hover
bg-primary-700  → Darkest blue for active state
```

## 🎯 Where Each Color is Used

### Primary (Blue)
- Main action buttons
- Links and navigation
- Primary CTAs
- Focus states
- Accent elements

```vue
<button class="bg-primary hover:bg-primary-600">Primary Action</button>
<a class="text-primary">Link</a>
<input class="focus:ring-2 focus:ring-primary" />
```

### Warning (Amber)
- Close Shift button
- Important alerts
- Caution dialogs
- Warning messages

```vue
<button class="bg-warning hover:bg-warning-600">Close Shift</button>
<div class="bg-warning-50 text-warning-900">Warning</div>
```

### Error (Red)
- Delete confirmations
- Error messages
- Refund operations
- Invalid state indicators

```vue
<button class="bg-error hover:bg-error-700">Delete</button>
<div class="text-error">Error message</div>
```

### Success (Green)
- Confirmation messages
- Success notifications
- Positive feedback
- Checkmarks/badges

```vue
<div class="bg-success-50 text-success-900">✓ Success!</div>
```

### Neutral (Gray)
- Body text
- Borders
- Backgrounds
- Disabled states
- Secondary information

```vue
<p class="text-neutral-600">Secondary text</p>
<div class="border border-neutral-200">Element</div>
<p class="text-neutral-500">Tertiary text</p>
```

## 🌓 Dark Mode Support

For dark backgrounds (like the header):

```vue
<header class="bg-slate-950/95 border-b border-white/10">
  <h1 class="text-white">Header</h1>
  <p class="text-slate-300">Secondary text</p>
  <button class="border border-white/20 text-white hover:bg-white/10">
    Action
  </button>
</header>
```

## 📱 Component Examples

### Header (Dark)
```vue
<header class="bg-slate-950/95 border-b border-white/10 text-white">
  <div class="flex items-center justify-between">
    <h1 class="text-lg font-black">QuickJuan</h1>
    <button class="bg-white/10 hover:bg-white/20 text-white rounded-full px-4 py-2">
      Account
    </button>
  </div>
</header>
```

### Form Input
```vue
<div class="space-y-2">
  <label class="block text-sm font-medium text-neutral-700">
    Field Label
  </label>
  <input
    type="text"
    class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
  />
</div>
```

### Modal/Dialog
```vue
<div class="fixed inset-0 bg-black/50">
  <div class="bg-white rounded-lg p-6 shadow-xl">
    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Dialog Title</h2>
    <p class="text-neutral-600 mb-6">Dialog content</p>
    <div class="flex gap-3 justify-end">
      <button class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50">
        Cancel
      </button>
      <button class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg">
        Confirm
      </button>
    </div>
  </div>
</div>
```

## ✅ Color Checklist for New Components

When creating a new component, use this checklist:

- [ ] **Text:** Use `text-neutral-900` (dark), `text-neutral-600` (medium), or `text-neutral-500` (light)
- [ ] **Backgrounds:** Use `bg-white`, `bg-neutral-50`, or `bg-neutral-100`
- [ ] **Borders:** Use `border-neutral-200` or semantic color (`border-primary-200`)
- [ ] **Primary Actions:** Use `bg-primary hover:bg-primary-600`
- [ ] **Warning Actions:** Use `bg-warning hover:bg-warning-600`
- [ ] **Danger Actions:** Use `bg-error hover:bg-error-700`
- [ ] **Disabled State:** Use `opacity-50` or `bg-neutral-200 text-neutral-400`
- [ ] **Hover Effects:** Always include hover variant (`hover:bg-*`)
- [ ] **Focus States:** Include `focus:ring-2 focus:ring-primary`
- [ ] **Contrast:** Verify text/background contrast (WCAG AA minimum 4.5:1)

## 🔧 Common Patterns

### Disabled Button
```vue
<button 
  disabled 
  class="px-4 py-2 bg-neutral-200 text-neutral-400 rounded-lg cursor-not-allowed"
>
  Disabled
</button>
```

### Loading State
```vue
<button class="opacity-50 cursor-not-allowed">
  <svg class="animate-spin"><!-- spinner --></svg>
  Loading...
</button>
```

### Badge/Tag
```vue
<span class="inline-flex items-center bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm font-medium">
  Badge
</span>
```

### Divider
```vue
<hr class="border-t border-neutral-200" />
```

### Link with Icon
```vue
<a class="text-primary hover:text-primary-600 flex items-center gap-2">
  <IconComponent class="w-4 h-4" />
  <span>Link Text</span>
</a>
```

## 🎨 Customization

To change the entire theme, edit `/tailwind.config.js`:

```javascript
colors: {
    primary: {
        50: "#your-color-50",
        // ... other shades
        DEFAULT: "#your-primary-color",
    },
    // ... other colors
}
```

All components using `bg-primary`, `text-primary`, etc., will automatically update.

## 📚 Full Documentation

For complete specifications and guidelines, see:
- **[COLOR_PALETTE.md](COLOR_PALETTE.md)** - Detailed color specs and accessibility
- **[THEME_IMPLEMENTATION.md](THEME_IMPLEMENTATION.md)** - Complete implementation guide
- **[THEME_UPDATE.md](THEME_UPDATE.md)** - What changed and migration guide

## 🚨 Don't Forget

- Always use **semantic color names** (`primary`, `warning`, `error`) not Tailwind defaults
- Include **hover variants** on all interactive elements
- Test colors on **actual devices** and lighting
- Verify **accessibility contrast** (use WCAG checkers)
- When migrating, search and replace systematically:
  - `bg-gray-*` → `bg-neutral-*`
  - `bg-orange-*` → `bg-warning`
  - `bg-red-*` → `bg-error`

---

**Quick Links:**
- 🎯 **[Tailwind Config](tailwind.config.js)** - Central color definitions
- 🎨 **[CashierHeader Component](resources/js/Components/CashierHeader.vue)** - Header example
- 📖 **[Full Color Palette](COLOR_PALETTE.md)** - All colors and specs
- 🛠️ **[Implementation Guide](THEME_IMPLEMENTATION.md)** - How to use colors

**Version:** 1.0 | **Last Updated:** December 2025
