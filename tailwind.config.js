import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './app/Filament/**/*.php',
        './app/Forms/Components/**/*.php',
        './app/Livewire/**/*.php',
        './app/Infolists/Components/**/*.php',
        './app/Providers/Filament/**/*.php',
        './app/Tables/Columns/**/*.php',
        './resources/views/**/*.blade.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: 'rgb(var(--filament-color-primary-50) / <alpha-value>)',
                    100: 'rgb(var(--filament-color-primary-100) / <alpha-value>)',
                    200: 'rgb(var(--filament-color-primary-200) / <alpha-value>)',
                    300: 'rgb(var(--filament-color-primary-300) / <alpha-value>)',
                    400: 'rgb(var(--filament-color-primary-400) / <alpha-value>)',
                    500: 'rgb(var(--filament-color-primary-500) / <alpha-value>)',
                    600: 'rgb(var(--filament-color-primary-600) / <alpha-value>)',
                    700: 'rgb(var(--filament-color-primary-700) / <alpha-value>)',
                    800: 'rgb(var(--filament-color-primary-800) / <alpha-value>)',
                    900: 'rgb(var(--filament-color-primary-900) / <alpha-value>)',
                    950: 'rgb(var(--filament-color-primary-950) / <alpha-value>)',
                },
                danger: defaultTheme.colors.red,
                success: defaultTheme.colors.green,
                warning: defaultTheme.colors.yellow,
            },
        },
    },

    safelist: [
        // Text
        'text-xs', 'text-sm', 'text-gray-400', 'text-gray-500', 'leading-tight',
        // Layout
        'inline-flex', 'inline-block', 'items-center', 'justify-center', 'flex', 'flex-wrap', 'gap-1', 'space-x-1', 'mt-1', 'mx-0.5',
        // Sizing & shape
        'w-4', 'h-4', 'w-6', 'h-6', 'rounded-full',
        // Backgrounds & shadows
        'bg-gray-100', 'shadow-sm',
        // Badges used in HTML strings
        'px-2', 'py-0.5', 'rounded-full', 'bg-blue-100', 'text-blue-700', 'bg-orange-100', 'text-orange-700',
    ],

    plugins: [forms, typography],
};
