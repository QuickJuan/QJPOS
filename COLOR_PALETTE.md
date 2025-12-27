# QuickJuan Color Palette Guide

This document outlines the standardized color palette for the QuickJuan application. All UI components should use these colors for consistency across the platform.

## Color System Overview

The application uses a structured color system with 5 main semantic color palettes:

- **Primary** - Main brand color (Blue)
- **Secondary** - Dark alternative (Dark Blue)
- **Tertiary** - Accent/Gold color
- **Warning** - Warning/Action color (Amber)
- **Error** - Error/Danger color (Red)
- **Success** - Success/Positive color (Green)
- **Neutral** - Gray scale for UI

## Primary Color (Blue)

Main brand color used for primary actions, links, and key UI elements.

```css
/* Usage Examples */
.bg-primary          /* #669bbc */
.bg-primary-500      /* #5688a8 */
.bg-primary-600      /* #467594 */
.bg-primary-700      /* #366280 */
.text-primary        /* #669bbc */
.text-primary-300    /* #8bb8d9 */
.text-primary-600    /* #467594 */
.border-primary      /* #669bbc */
```

**Color Shades:**
- 50: `#f0f6fa` - Lightest background
- 100: `#dbeaf3`
- 200: `#b3d1e6`
- 300: `#8bb8d9`
- 400: `#669bbc`
- 500: `#5688a8` - Primary brand
- 600: `#467594` - Hover state
- 700: `#366280` - Active state
- 800: `#264f6c`
- 900: `#1a3c58` - Darkest variant

## Secondary Color (Dark Blue)

Alternative color for darker themes and secondary UI elements.

```css
/* Usage Examples */
.bg-secondary        /* #003049 */
.bg-secondary-500    /* #0064af */
.text-secondary      /* #003049 */
.border-secondary    /* #003049 */
```

**Color Shades:**
- 50: `#e6f0f7`
- 100: `#cce0ef`
- 200: `#99c1df`
- 300: `#66a2cf`
- 400: `#3383bf`
- 500: `#0064af`
- 600: `#00519f`
- 700: `#003e8f`
- 800: `#003049` - Secondary dark (Header default)
- 900: `#002439` - Darkest variant

## Tertiary Color (Gold/Accent)

Accent color used for highlights and special elements.

```css
/* Usage Examples */
.bg-tertiary         /* #fdf0d5 */
.text-tertiary       /* #fdf0d5 */
.border-tertiary     /* #fdf0d5 */
```

**Color Shades:**
- 50: `#fefcf9`
- 100: `#fdf0d5`
- 200: `#fbe8c1`
- 300: `#f9e0ad`
- 400: `#f7d899`
- 500: `#f5d085`
- 600: `#e6c077`
- 700: `#d7b069`
- 800: `#c8a05b`
- 900: `#b9904d`

## Warning Color (Amber)

Used for warning messages, important actions, and alerts.

```css
/* Usage Examples */
.bg-warning          /* #f59e0b */
.bg-warning-500      /* #f59e0b */
.bg-warning-600      /* #d97706 */
.text-warning        /* #f59e0b */
.border-warning      /* #f59e0b */
```

**Color Shades:**
- 50: `#fffbeb`
- 100: `#fef3c7`
- 200: `#fde68a`
- 300: `#fcd34d`
- 400: `#fbbf24`
- 500: `#f59e0b` - Warning standard
- 600: `#d97706` - Hover state
- 700: `#b45309` - Active state
- 800: `#92400e`
- 900: `#78350f`

## Error Color (Red)

Used for errors, critical alerts, and destructive actions.

```css
/* Usage Examples */
.bg-error            /* #dc2626 */
.bg-error-600        /* #dc2626 */
.text-error          /* #dc2626 */
.border-error        /* #dc2626 */
```

**Color Shades:**
- 50: `#fef2f2`
- 100: `#fee2e2`
- 200: `#fecaca`
- 300: `#fca5a5`
- 400: `#f87171`
- 500: `#ef4444`
- 600: `#dc2626` - Error standard
- 700: `#c1121f` - Active state
- 800: `#991b1b`
- 900: `#780000`

## Success Color (Green)

Used for success messages and positive feedback.

```css
/* Usage Examples */
.bg-success          /* #22c55e */
.text-success        /* #22c55e */
.border-success      /* #22c55e */
```

**Color Shades:**
- 50: `#f0fdf4`
- 100: `#dcfce7`
- 200: `#bbf7d0`
- 300: `#86efac`
- 400: `#4ade80`
- 500: `#22c55e` - Success standard
- 600: `#16a34a` - Hover state
- 700: `#15803d` - Active state
- 800: `#166534`
- 900: `#14532d`

## Neutral Colors (Gray)

Used for text, borders, backgrounds, and general UI elements.

```css
/* Usage Examples */
.bg-neutral          /* #fdf0d5 - Legacy */
.bg-neutral-50       /* #fafafa */
.bg-neutral-100      /* #f5f5f5 */
.bg-neutral-500      /* #737373 */
.text-neutral-700    /* #404040 */
.text-neutral-900    /* #171717 */
```

**Color Shades:**
- 50: `#fafafa` - Lightest background
- 100: `#f5f5f5`
- 200: `#e5e5e5`
- 300: `#d4d4d4`
- 400: `#a3a3a3`
- 500: `#737373` - Medium gray
- 600: `#525252`
- 700: `#404040` - Darker gray
- 800: `#262626`
- 900: `#171717` - Darkest text

## Usage Examples

### Button Styling

```vue
<!-- Primary Action Button -->
<button class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg">
  Primary Action
</button>

<!-- Warning/Secondary Button -->
<button class="bg-warning hover:bg-warning-600 text-white px-4 py-2 rounded-lg">
  Close Shift
</button>

<!-- Danger/Error Button -->
<button class="bg-error hover:bg-error-700 text-white px-4 py-2 rounded-lg">
  Delete Item
</button>

<!-- Success Button -->
<button class="bg-success hover:bg-success-600 text-white px-4 py-2 rounded-lg">
  Confirm
</button>
```

### Card and Background Styling

```vue
<!-- Light Background Card -->
<div class="bg-neutral-50 border border-neutral-200 rounded-lg p-4">
  <h3 class="text-neutral-900 font-semibold">Card Title</h3>
  <p class="text-neutral-600">Card content goes here</p>
</div>

<!-- Dark Header -->
<header class="bg-secondary-800 text-white">
  <h1 class="text-white">Dark Header</h1>
</header>

<!-- Alert Box (Warning) -->
<div class="bg-warning-50 border border-warning-200 text-warning-800 p-4 rounded-lg">
  <p class="font-semibold">Warning Message</p>
</div>
```

### Text and Border Styling

```vue
<!-- Primary Text -->
<p class="text-primary-600">Primary colored text</p>

<!-- Secondary Text -->
<p class="text-neutral-600">Secondary gray text</p>

<!-- Error Text -->
<p class="text-error">Error message in red</p>

<!-- Success Text -->
<p class="text-success">Success message in green</p>

<!-- Border Examples -->
<div class="border-l-4 border-primary-500">Bordered element</div>
<div class="border border-neutral-200">Standard border</div>
<div class="border border-error">Error border</div>
```

## Dark Mode Support

For dark mode implementations, use:
- **Text on dark:** `text-white`, `text-slate-200`, `text-slate-400`
- **Background on dark:** `bg-slate-950`, `bg-slate-900`, `bg-slate-800`
- **Borders on dark:** `border-white/10`, `border-white/20`

Example:
```vue
<header class="bg-slate-950/95 border-b border-white/10 text-white">
  <!-- Header content -->
</header>
```

## Theme Customization

To change the overall theme, modify the color values in `tailwind.config.js` under the `colors` section. All UI elements will automatically update since they reference the Tailwind color system.

### Current Theme Setup:
- **Header:** Dark slate (slate-950/95) with white/10 borders
- **Primary Actions:** Blue (primary-500/600)
- **Warning/Close Actions:** Amber (warning-500/600)
- **Error Actions:** Red (error-600/700)
- **Success:** Green (success-500/600)

## Migration Guide

When updating existing components from hardcoded colors:

1. Replace `bg-gray-600` → `bg-neutral-600` or `bg-primary-600`
2. Replace `bg-orange-600` → `bg-warning-600`
3. Replace `bg-red-600` → `bg-error-600`
4. Replace `text-gray-700` → `text-neutral-700`
5. Replace `border-gray-200` → `border-neutral-200`

## Best Practices

1. **Use semantic colors:** Choose colors based on purpose (primary, warning, error, success)
2. **Maintain contrast:** Ensure text color provides sufficient contrast with background
3. **Consistent hover states:** Always include hover variants (e.g., `hover:bg-primary-600`)
4. **Use color opacity:** For overlays and subtle effects, use opacity modifiers (`/50`, `/75`)
5. **Dark mode consideration:** Always consider how colors appear on dark backgrounds
6. **Accessibility:** Test color combinations with WCAG contrast ratio checkers

## Color Definitions in Tailwind Config

All colors are centrally defined in `tailwind.config.js`:

```javascript
theme: {
    extend: {
        colors: {
            primary: { /* color shades */ },
            secondary: { /* color shades */ },
            tertiary: { /* color shades */ },
            warning: { /* color shades */ },
            error: { /* color shades */ },
            success: { /* color shades */ },
            neutral: { /* color shades */ },
        }
    }
}
```

This ensures:
- Single source of truth for colors
- Easy theme switching
- Consistent application design
- Simple maintenance and updates
