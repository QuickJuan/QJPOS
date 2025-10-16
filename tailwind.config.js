import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // QuickJuan POS Color Theme
                primary: {
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
                },
                secondary: {
                    50: '#e6f0f7',
                    100: '#cce0ef',
                    200: '#99c1df',
                    300: '#66a2cf',
                    400: '#3383bf',
                    500: '#0064af',
                    600: '#00519f',
                    700: '#003e8f',
                    800: '#003049',
                    900: '#002439',
                    DEFAULT: '#003049'
                },
                neutral: {
                    50: '#fefcf9',
                    100: '#fdf0d5',
                    200: '#fbe8c1',
                    300: '#f9e0ad',
                    400: '#f7d899',
                    500: '#f5d085',
                    600: '#e6c077',
                    700: '#d7b069',
                    800: '#c8a05b',
                    900: '#b9904d',
                    DEFAULT: '#fdf0d5'
                },
                error: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#c1121f',
                    800: '#991b1b',
                    900: '#780000',
                    DEFAULT: '#c1121f'
                },
                warning: {
                    50: '#fef7f7',
                    100: '#fdeaea',
                    200: '#fbd5d5',
                    300: '#f9c0c0',
                    400: '#f7abab',
                    500: '#f59696',
                    600: '#e58181',
                    700: '#d56c6c',
                    800: '#c55757',
                    900: '#780000',
                    DEFAULT: '#780000'
                },
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
                }
            },
        },
    },

    plugins: [forms, typography],
};
