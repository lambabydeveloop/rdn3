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
        
        },
    },
    plugins: [
        require('preline/plugin'),
        require('@tailwindcss/forms'),
    ],
}
