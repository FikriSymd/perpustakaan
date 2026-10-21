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
                display: ['Fraunces', 'Georgia', 'Times New Roman', 'serif'],
                sans: ['Archivo', ...defaultTheme.fontFamily.sans],
                mono: ['IBM Plex Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                paper: { DEFAULT: '#F4F0E4', deep: '#E8E2CF', soft: '#FBF9F2' },
                ink: { DEFAULT: '#201D16', soft: '#4E483A', mute: '#8B8269' },
                moss: { DEFAULT: '#2C4A39', deep: '#1B3125', bright: '#437057', pale: '#DDE6DC' },
                brass: { DEFAULT: '#96742A', bright: '#BE9A3D', pale: '#EFE3C4' },
                rust: { DEFAULT: '#9C3D2A', deep: '#7A2E1F', pale: '#F0DAD3' },
                line: { DEFAULT: '#D6CEB8', dark: '#BFB69C' },
            },
            boxShadow: {
                stamp: '3px 3px 0 0 #201D16',
                'stamp-sm': '2px 2px 0 0 #201D16',
                'stamp-brass': '3px 3px 0 0 #96742A',
            },
            letterSpacing: {
                label: '0.18em',
            },
            transitionTimingFunction: {
                'out-quart': 'cubic-bezier(0.25, 1, 0.5, 1)',
            },
        },
    },

    plugins: [forms],
};
