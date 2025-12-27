# Theme Implementation Guide

This guide explains how to implement and use the QuickJuan color theme throughout the application.

## Overview

The QuickJuan application now has a comprehensive, centralized color system that enables:
- Consistent visual design across all pages
- Easy theme switching without code changes
- Simple color palette updates via configuration
- Semantic color usage (primary, secondary, warning, error, success)

## System Architecture

### 1. Color Definition Layer (`tailwind.config.js`)

All colors are defined in a single location: `tailwind.config.js`

**Why this approach:**
- Single source of truth for all colors
- Easy maintenance and updates
- Enables theme switching at build time
- Supports gradual migration of components

### 2. Usage in Vue Components

Colors are applied using Tailwind CSS utility classes directly in components:

```vue
<!-- Background colors -->
<div class="bg-primary">Primary background</div>
<div class="bg-secondary">Secondary background</div>
<div class="bg-warning">Warning background</div>

<!-- Text colors -->
<p class="text-primary">Primary text</p>
<p class="text-error">Error text</p>

<!-- Border colors -->
<div class="border border-neutral-200">Neutral border</div>
```

### 3. Component-Level Applications

Layouts and major components use the color system:

- **CashierHeader.vue** - Dark slate theme (slate-950) with white text
- **CashieringLayout.vue** - Neutral backgrounds with primary action colors
- **TransactionsLayout.vue** - Clean white header with primary accents
- **TenantLandingLayout.vue** - Dark mode with accent highlights

## Color Usage Patterns

### Primary Actions

Primary buttons and critical actions use the `primary` color family:

```vue
<button class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg">
  Confirm Order
</button>

<a href="/menu" class="text-primary hover:text-primary-600">
  View Menu
</a>
```

### Warning/Caution Actions

Close shift, logout, and warning dialogs use the `warning` color:

```vue
<button class="bg-warning hover:bg-warning-600 text-white px-4 py-2 rounded-lg">
  Close Shift
</button>
```

### Error States

Errors, deletions, and destructive actions use the `error` color:

```vue
<button class="bg-error hover:bg-error-700 text-white px-4 py-2 rounded-lg">
  Delete Item
</button>

<div class="bg-error-50 border border-error-200 text-error-800 p-4 rounded">
  Error message
</div>
```

### Success States

Confirmations and successful operations use the `success` color:

```vue
<div class="bg-success-50 border border-success-200 text-success-800 p-4 rounded">
  ✓ Order confirmed successfully
</div>

<button class="bg-success hover:bg-success-600 text-white px-4 py-2 rounded-lg">
  Confirm
</button>
```

### Neutral Elements

Text, borders, and general UI elements use the `neutral` color family:

```vue
<!-- Light background -->
<div class="bg-neutral-50 text-neutral-900 p-4">Content</div>

<!-- Border -->
<div class="border-t border-neutral-200"></div>

<!-- Text hierarchy -->
<h1 class="text-neutral-900">Primary heading</h1>
<p class="text-neutral-600">Secondary text</p>
<span class="text-neutral-500">Tertiary text</span>
```

## Implementation Checklist

When implementing new features, follow this checklist:

- [ ] Identify the semantic purpose (primary, warning, error, success, neutral)
- [ ] Use the appropriate color family for that purpose
- [ ] Include hover/active state variants for interactive elements
- [ ] Ensure sufficient contrast for accessibility
- [ ] Test on both light and dark backgrounds
- [ ] Update this guide if new color patterns are introduced

## Component-Specific Patterns

### Buttons

```vue
<!-- Primary Button -->
<button class="bg-primary hover:bg-primary-600 active:bg-primary-700 text-white px-4 py-2 rounded-lg transition">
  Primary
</button>

<!-- Warning Button -->
<button class="bg-warning hover:bg-warning-600 active:bg-warning-700 text-white px-4 py-2 rounded-lg transition">
  Warning
</button>

<!-- Secondary Button (Outlined) -->
<button class="border-2 border-primary text-primary hover:bg-primary-50 px-4 py-2 rounded-lg transition">
  Secondary
</button>

<!-- Ghost Button -->
<button class="text-primary hover:bg-primary-50 px-4 py-2 rounded-lg transition">
  Ghost
</button>
```

### Cards

```vue
<!-- Light Card -->
<div class="bg-white border border-neutral-200 rounded-lg p-6 shadow-sm">
  <h3 class="text-neutral-900 font-semibold mb-2">Title</h3>
  <p class="text-neutral-600">Description</p>
</div>

<!-- Elevated Card -->
<div class="bg-white rounded-lg p-6 shadow-lg">
  <h3 class="text-neutral-900 font-semibold">Title</h3>
</div>

<!-- Alert Card -->
<div class="bg-warning-50 border-l-4 border-warning-500 p-4 rounded">
  <p class="text-warning-900 font-semibold">Warning</p>
</div>
```

### Headers and Navbars

```vue
<!-- Dark Header -->
<header class="bg-slate-950/95 border-b border-white/10 text-white">
  <!-- Header content -->
</header>

<!-- Light Header -->
<header class="bg-white border-b border-neutral-200">
  <!-- Header content -->
</header>

<!-- Branded Header -->
<header class="bg-secondary text-white">
  <!-- Header content -->
</header>
```

### Form Elements

```vue
<!-- Standard Input -->
<input 
  type="text"
  class="border border-neutral-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary"
/>

<!-- Input with Error -->
<input
  type="email"
  class="border border-error-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-error"
/>

<!-- Label -->
<label class="block text-sm font-medium text-neutral-700 mb-2">
  Field Label
</label>
```

## Migration Guide

If you're updating existing components from hardcoded colors:

### From Tailwind Default Colors to Custom Palette

```vue
<!-- Before -->
<div class="bg-gray-50 text-gray-900 border border-gray-200">
  <button class="bg-blue-600 hover:bg-blue-700">Action</button>
</div>

<!-- After -->
<div class="bg-neutral-50 text-neutral-900 border border-neutral-200">
  <button class="bg-primary hover:bg-primary-600">Action</button>
</div>
```

### Color Mapping Reference

| Old Color | New Color | Purpose |
|-----------|-----------|---------|
| gray-50/100/200 | neutral-50/100/200 | Backgrounds |
| gray-600/700 | neutral-600/700 | Text/Borders |
| gray-900 | neutral-900 | Primary text |
| blue-600/700 | primary-600/700 | Primary actions |
| orange-600 | warning-600 | Warning/Shift actions |
| red-600/700 | error-600/700 | Errors/Deletions |
| green-600 | success-600 | Success states |

## Advanced: Theme Switching

To implement multiple themes (light/dark/custom), update `tailwind.config.js`:

```javascript
// In tailwind.config.js
const themes = {
    default: {
        primary: '#669bbc',
        secondary: '#003049',
        // ... other colors
    },
    dark: {
        primary: '#7ba8d1',
        secondary: '#1a3a52',
        // ... other colors
    },
};

// This enables runtime theme switching via CSS variables
```

See the [COLOR_PALETTE.md](COLOR_PALETTE.md) file for detailed color specifications.

## Testing Color Accessibility

When using colors, ensure:

1. **Text contrast** - WCAG AA minimum 4.5:1 for normal text
2. **Color blindness** - Avoid red/green only combinations
3. **Readability** - Test text on all background colors used

Use tools like:
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Colorblind Web Page Filter](https://www.color-blindness.com/)

## Common Issues and Solutions

### Issue: Colors not updating after changes

**Solution:** Clear Tailwind cache:
```bash
npm run build  # Rebuilds with latest config
# or
rm -rf node_modules/.cache
npm run dev
```

### Issue: Color inconsistency between pages

**Solution:** Always use semantic color names (primary, warning, etc.) instead of direct colors. Check [COLOR_PALETTE.md](COLOR_PALETTE.md) for the correct usage.

### Issue: Hover states not working

**Solution:** Ensure all interactive elements have proper hover variants:
```vue
<!-- ✓ Correct -->
<button class="bg-primary hover:bg-primary-600">Action</button>

<!-- ✗ Wrong -->
<button class="bg-primary">Action</button>
```

## Resources

- **Color Definitions:** [COLOR_PALETTE.md](COLOR_PALETTE.md)
- **Tailwind Config:** [tailwind.config.js](tailwind.config.js)
- **Header Component:** [CashierHeader.vue](resources/js/Components/CashierHeader.vue)
- **Landing Layout:** [TenantLandingLayout.vue](resources/js/Layouts/TenantLandingLayout.vue)

## Support and Questions

For color-related issues:
1. Check [COLOR_PALETTE.md](COLOR_PALETTE.md) for color specifications
2. Review component examples in this guide
3. Check existing components for implementation patterns
4. Verify `tailwind.config.js` for the latest color definitions
