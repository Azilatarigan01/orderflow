import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,jsx}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#F0F7FF',
                    100: '#E0EFFF',
                    200: '#B9DEFE',
                    300: '#7CC2FD',
                    400: '#38A2F9',
                    500: '#0E85EB',
                    600: '#0267C7',
                    700: '#0352A1',
                    800: '#074684',
                    900: '#0C3B6E',
                    950: '#072448',
                },
                navy: {
                    800: '#0F2038',
                    900: '#0A1526',
                    950: '#060D18',
                }
            },
            boxShadow: {
                'corporate': '0 4px 20px -2px rgba(14, 133, 235, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04)',
                'corporate-lg': '0 12px 32px -4px rgba(12, 59, 110, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
            }
        },
    },

    plugins: [forms],
};

