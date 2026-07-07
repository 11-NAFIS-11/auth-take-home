import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'selector',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Requested brand blue: hsb(217, 62%, 94%) = #5B94F0, converted from
            // Figma's HSB picker (not HSL, which would give a near-white color at
            // 94% lightness). Anchored at 600 — the shade used for primary
            // buttons/the card's top border, the most prominent usage — with
            // 400/500/700/800 derived at the same hue/saturation for the lighter
            // (focus ring, gradient bar) and darker (hover, darkest text) states
            // actually used elsewhere in the app, instead of overriding Tailwind's
            // entire default blue scale or hardcoding this hex in every component.
            colors: {
                blue: {
                    400: '#619EFF',
                    500: '#5F9AFA',
                    600: '#5B94F0',
                    700: '#5184D6',
                    800: '#4875BD',
                },
            },
        },
    },

    plugins: [forms],
};
