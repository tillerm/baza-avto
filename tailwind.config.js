const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',

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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', 'Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                bg: {
                    base: 'rgb(var(--bg-base) / <alpha-value>)',
                    elevated: 'rgb(var(--bg-elevated) / <alpha-value>)',
                    glass: 'var(--bg-glass)',
                    surface: 'rgb(var(--surface-1) / <alpha-value>)',
                    'surface-2': 'rgb(var(--surface-2) / <alpha-value>)',
                },
                ink: {
                    primary: 'rgb(var(--text-primary) / <alpha-value>)',
                    secondary: 'rgb(var(--text-secondary) / <alpha-value>)',
                    muted: 'rgb(var(--text-muted) / <alpha-value>)',
                },
                accent: {
                    DEFAULT: 'rgb(var(--accent) / <alpha-value>)',
                    hover: 'rgb(var(--accent-hover) / <alpha-value>)',
                    soft: 'var(--accent-soft)',
                },
                line: {
                    subtle: 'var(--border-subtle)',
                    strong: 'var(--border-strong)',
                },
            },

            borderRadius: {
                sm: 'var(--radius-sm)',
                md: 'var(--radius-md)',
                lg: 'var(--radius-lg)',
                xl: 'var(--radius-xl)',
                pill: 'var(--radius-pill)',
            },

            boxShadow: {
                soft: 'var(--shadow-sm)',
                md: 'var(--shadow-md)',
                lg: 'var(--shadow-lg)',
                glow: 'var(--shadow-glow)',
                'glow-lg': 'var(--shadow-glow-lg)',
                elevation: 'var(--shadow-elevation)',
            },

            letterSpacing: {
                display: '-0.04em',
                eyebrow: '0.18em',
                wide: '0.08em',
            },

            backdropBlur: {
                xs: '2px',
                xl: '24px',
                '2xl': '40px',
            },

            transitionTimingFunction: {
                'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
                'spring': 'cubic-bezier(0.34, 1.56, 0.64, 1)',
            },

            transitionDuration: {
                250: '250ms',
                400: '400ms',
                600: '600ms',
            },

            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(24px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'shimmer': {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                'marquee': {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
                'pulse-glow': {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(239, 68, 68, 0.4)' },
                    '50%': { boxShadow: '0 0 0 12px rgba(239, 68, 68, 0)' },
                },
            },

            animation: {
                'fade-in-up': 'fade-in-up 600ms cubic-bezier(0.16, 1, 0.3, 1) both',
                'fade-in': 'fade-in 400ms ease-out both',
                'shimmer': 'shimmer 2.5s linear infinite',
                'marquee': 'marquee 30s linear infinite',
                'pulse-glow': 'pulse-glow 2.5s ease-out infinite',
            },
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
