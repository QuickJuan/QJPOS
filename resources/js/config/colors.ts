// QuickJuan POS Color Theme Configuration
export const colors = {
  // Primary Colors (Steel Blue family)
  primary: {
    50: '#f0f6fa',   // Lightest blue
    100: '#dbeaf3',  // Very light blue
    200: '#b3d1e6',  // Light blue
    300: '#8bb8d9',  // Medium-light blue
    400: '#669bbc',  // Main primary color
    500: '#5688a8',  // Slightly darker
    600: '#467594',  // Darker blue
    700: '#366280',  // Dark blue
    800: '#264f6c',  // Very dark blue
    900: '#1a3c58',  // Darkest blue
    DEFAULT: '#669bbc'
  },

  // Secondary Colors (Dark Blue family)
  secondary: {
    50: '#e6f0f7',   // Very light dark blue
    100: '#cce0ef',  // Light dark blue
    200: '#99c1df',  // Medium light
    300: '#66a2cf',  // Medium
    400: '#3383bf',  // Medium dark
    500: '#0064af',  // Darker
    600: '#00519f',  // Even darker
    700: '#003e8f',  // Very dark
    800: '#003049',  // Main secondary color
    900: '#002439',  // Darkest
    DEFAULT: '#003049'
  },

  // Neutral/Background (Cream family)
  neutral: {
    50: '#fefcf9',   // Almost white
    100: '#fdf0d5',  // Main cream color
    200: '#fbe8c1',  // Slightly darker cream
    300: '#f9e0ad',  // Medium cream
    400: '#f7d899',  // Darker cream
    500: '#f5d085',  // Golden cream
    600: '#e6c077',  // Darker golden
    700: '#d7b069',  // Brown-ish cream
    800: '#c8a05b',  // Dark cream
    900: '#b9904d',  // Darkest cream
    DEFAULT: '#fdf0d5'
  },

  // Error/Alert (Red family)
  error: {
    50: '#fef2f2',   // Very light red
    100: '#fee2e2',  // Light red
    200: '#fecaca',  // Medium light red
    300: '#fca5a5',  // Medium red
    400: '#f87171',  // Light-medium red
    500: '#ef4444',  // Standard red
    600: '#dc2626',  // Darker red
    700: '#c1121f',  // Main error color
    800: '#991b1b',  // Very dark red
    900: '#780000',  // Darkest red
    DEFAULT: '#c1121f'
  },

  // Warning (Dark Red family)
  warning: {
    50: '#fef7f7',   // Very light dark red
    100: '#fdeaea',  // Light dark red
    200: '#fbd5d5',  // Medium light
    300: '#f9c0c0',  // Medium
    400: '#f7abab',  // Medium dark
    500: '#f59696',  // Darker
    600: '#e58181',  // Even darker
    700: '#d56c6c',  // Very dark
    800: '#c55757',  // Almost darkest
    900: '#780000',  // Main warning color
    DEFAULT: '#780000'
  },

  // Success (Green - complementary to the theme)
  success: {
    50: '#f0fdf4',
    100: '#dcfce7',
    200: '#bbf7d0',
    300: '#86efac',
    400: '#4ade80',
    500: '#22c55e',
    600: '#16a34a',
    700: '#15803d',
    800: '#166534',
    900: '#14532d',
    DEFAULT: '#22c55e'
  },

  // Info (Using lighter primary)
  info: {
    50: '#f0f6fa',
    100: '#dbeaf3',
    200: '#b3d1e6',
    300: '#8bb8d9',
    400: '#669bbc',
    500: '#5688a8',
    600: '#467594',
    700: '#366280',
    800: '#264f6c',
    900: '#1a3c58',
    DEFAULT: '#669bbc'
  }
} as const;

// Semantic color mappings for specific UI elements
export const semanticColors = {
  // Backgrounds
  background: {
    primary: colors.neutral[50],
    secondary: colors.neutral[100],
    tertiary: colors.neutral[200]
  },

  // Text colors
  text: {
    primary: colors.secondary[800],
    secondary: colors.secondary[600],
    muted: colors.secondary[400],
    inverse: colors.neutral[50]
  },

  // Interactive elements
  interactive: {
    primary: colors.primary.DEFAULT,
    primaryHover: colors.primary[500],
    secondary: colors.secondary.DEFAULT,
    secondaryHover: colors.secondary[700]
  },

  // Status colors
  status: {
    success: colors.success.DEFAULT,
    error: colors.error.DEFAULT,
    warning: colors.warning.DEFAULT,
    info: colors.info.DEFAULT
  },

  // Border colors
  border: {
    light: colors.neutral[300],
    medium: colors.primary[200],
    strong: colors.primary[400]
  }
} as const;

// Export individual color functions for easier use
export const getColor = (colorPath: string) => {
  const keys = colorPath.split('.');
  let color: any = colors;

  for (const key of keys) {
    color = color[key];
    if (!color) return null;
  }

  return color;
};
