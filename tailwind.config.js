import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#EEF2FF',
                    100: '#E0E7FF',
                    200: '#C7D2FE',
                    300: '#A5B4FC',
                    400: '#818CF8',
                    500: '#6366F1',
                    600: '#4F46E5', // Primary Accent
                    700: '#4338CA',
                    800: '#3730A3',
                    900: '#312E81',
                },
                navy: {
                    800: '#1E293B',
                    900: '#0F172A', // Primary Navy
                    950: '#020617',
                },
                teal: {
                    500: '#14B8A6',
                    600: '#0D9488',
                }
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
