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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Mono ala Hermes (AeonikFono) untuk label, angka & blok terminal
                term: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'monospace'],
            },
            colors: {
                // Merah sambal — adaptasi biru elektrik Hermes (#0000f2)
                brand: {
                    50: '#FDF2F4',
                    100: '#FBE4E8',
                    200: '#F6C2CC',
                    400: '#E14B5A',
                    500: '#D21F36',
                    600: '#C8102E',
                    700: '#A50D27',
                    800: '#7E0A1F',
                    900: '#560614',
                    950: '#3A040C',
                    DEFAULT: '#A50D27',
                },
                cream: '#FAF5EC',
                paper: '#FFFDF8',
                coal: '#220D0A',
                ember: '#E4572E',
            },
        },
    },

    plugins: [forms],
};
