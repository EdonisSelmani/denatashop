import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                denata: {
                    dark: '#15181B',
                    charcoal: '#22272B',
                    gold: '#B88A3B',
                    goldHover: '#9A712E',
                    warm: '#F7F5F1',
                    text: '#17191C',
                    muted: '#6B6F74',
                    border: '#E5E1DA',
                    success: '#25865A',
                    danger: '#C9473D',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Manrope', 'Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
