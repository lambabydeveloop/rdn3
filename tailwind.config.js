/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./templates/*/.html.twig",
        "./templates/**/*.html.twig",
        "./assets/**/*.js",
        "./node_modules/preline/dist/*.js",
        "./assets/**/*.css",
        "templates/pages/*.html.twig",
        "templates/**/*.html.twig",
    ],
    theme: {
        extend: {
            colors: {
                primary: '#DC2626',
                'primary-dark': '#B91C1C',
                'primary-light': '#EF4444',
                dark: '#0A0A0A',
                'dark-light': '#171717',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                montserrat: ['Montserrat', 'Inter', 'system-ui', 'sans-serif'],
            },
            animation: {
                'fade-in': 'fadeIn 0.8s ease-out forwards',
                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                'float': 'float 6s ease-in-out infinite',
                'pulse-slow': 'pulse 3s ease-in-out infinite',
                'spin-slow': 'spin 8s linear infinite',
                'bounce-slow': 'bounce 3s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-15px)' },
                },
            },
            boxShadow: {
                'soft': '0 4px 20px rgba(0, 0, 0, 0.05)',
                'medium': '0 8px 30px rgba(0, 0, 0, 0.1)',
                'hard': '0 15px 40px rgba(0, 0, 0, 0.15)',
                'red': '0 4px 14px rgba(220, 38, 38, 0.2)',
                'red-lg': '0 8px 25px rgba(220, 38, 38, 0.25)',
            },
        },
    },
    plugins: [
        require('preline/plugin'),
        require('@tailwindcss/forms'),
    ],
}
