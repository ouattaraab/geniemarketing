import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
        './app/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                gm: {
                    red: '#B40F1E',
                    'red-bright': '#D81B2A',
                    'red-deep': '#8A0A15',
                    'red-soft': '#FDECEE',
                    ink: '#1A1A1A',
                    charcoal: '#2D2D2D',
                    'charcoal-2': '#4B4B4B',
                    gray: '#7A7A7A',
                    'gray-line': '#E5E2DC',
                    'gray-soft': '#F1EEE7',
                    paper: '#FAF8F4',
                    cream: '#F2EFE8',
                },
            },
            fontFamily: {
                slab: ['Zilla Slab', 'Rockwell', 'Georgia', ...defaultTheme.fontFamily.serif],
                sans: ['Mulish', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            maxWidth: {
                container: '1400px',
                'container-narrow': '960px',
            },
            boxShadow: {
                'gm-red': '0 4px 12px rgba(180, 15, 30, 0.3)',
            },
            animation: {
                'gm-pulse': 'gm-pulse 1.5s infinite',
            },
            keyframes: {
                'gm-pulse': {
                    '0%': { boxShadow: '0 0 0 0 rgba(216, 27, 42, 0.7)' },
                    '70%': { boxShadow: '0 0 0 8px rgba(216, 27, 42, 0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(216, 27, 42, 0)' },
                },
            },
        },
    },

    plugins: [forms, typography],
};
