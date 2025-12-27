import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary Color Palette (Main Brand Color - Blue)
                primary: {
                    50: "#f0f6fa",
                    100: "#dbeaf3",
                    200: "#b3d1e6",
                    300: "#8bb8d9",
                    400: "#669bbc",
                    500: "#5688a8",
                    600: "#467594",
                    700: "#366280",
                    800: "#264f6c",
                    900: "#1a3c58",
                    DEFAULT: "#669bbc",
                },

                // Secondary Color Palette (Dark Blue)
                secondary: {
                    50: "#e6f0f7",
                    100: "#cce0ef",
                    200: "#99c1df",
                    300: "#66a2cf",
                    400: "#3383bf",
                    500: "#0064af",
                    600: "#00519f",
                    700: "#003e8f",
                    800: "#003049",
                    900: "#002439",
                    DEFAULT: "#003049",
                },

                // Tertiary Color Palette (Accent/Gold)
                tertiary: {
                    50: "#fefcf9",
                    100: "#fdf0d5",
                    200: "#fbe8c1",
                    300: "#f9e0ad",
                    400: "#f7d899",
                    500: "#f5d085",
                    600: "#e6c077",
                    700: "#d7b069",
                    800: "#c8a05b",
                    900: "#b9904d",
                    DEFAULT: "#fdf0d5",
                },

                // Warning Color Palette (Orange/Amber)
                warning: {
                    50: "#fffbeb",
                    100: "#fef3c7",
                    200: "#fde68a",
                    300: "#fcd34d",
                    400: "#fbbf24",
                    500: "#f59e0b",
                    600: "#d97706",
                    700: "#b45309",
                    800: "#92400e",
                    900: "#78350f",
                    DEFAULT: "#f59e0b",
                },

                // Error Color Palette (Red)
                error: {
                    50: "#fef2f2",
                    100: "#fee2e2",
                    200: "#fecaca",
                    300: "#fca5a5",
                    400: "#f87171",
                    500: "#ef4444",
                    600: "#dc2626",
                    700: "#c1121f",
                    800: "#991b1b",
                    900: "#780000",
                    DEFAULT: "#dc2626",
                },

                // Success Color Palette (Green)
                success: {
                    50: "#f0fdf4",
                    100: "#dcfce7",
                    200: "#bbf7d0",
                    300: "#86efac",
                    400: "#4ade80",
                    500: "#22c55e",
                    600: "#16a34a",
                    700: "#15803d",
                    800: "#166534",
                    900: "#14532d",
                    DEFAULT: "#22c55e",
                },

                // Legacy color names for backward compatibility
                prime: {
                    main: "#003049",
                    accent: "#669bbc",
                    neutral: "#fdf0d5",
                    base: "#ffffff",
                    info: "#5688a8",
                    success: "#22c55e",
                    warning: "#f59696",
                    error: "#c1121f",
                },
                pastel: {
                    main: "#003049",
                    accent: "#669bbc",
                    neutral: "#fdf0d5",
                    base: "#ffffff",
                    info: "#5688a8",
                    success: "#22c55e",
                    warning: "#f59696",
                    error: "#c1121f",
                },
                neutral: {
                    50: "#fafafa",
                    100: "#f5f5f5",
                    200: "#e5e5e5",
                    300: "#d4d4d4",
                    400: "#a3a3a3",
                    500: "#737373",
                    600: "#525252",
                    700: "#404040",
                    800: "#262626",
                    900: "#171717",
                    DEFAULT: "#fdf0d5",
                },
            },
        },
    },

    plugins: [forms, typography],
};
