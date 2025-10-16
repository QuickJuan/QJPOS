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
                primary: {
                    DEFAULT: '#393E46',
                    light: '#6B7684',
                },
                secondary: {
                    DEFAULT: '#948979',
                    light: '#BFB7A9',
                },
                third: {
                    DEFAULT: '#222831',
                    light: '#474F5A',
                },
                fourth: {
                    DEFAULT: '#DFD0B8',
                    light: '#F5EEDD',
                },
            },
        },
    },

    plugins: [forms, typography],
};
