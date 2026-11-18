import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            // Blade compiles land in storage/ and are locked mid-write on Windows.
            ignored: ['**/storage/**', '**/vendor/**', '**/node_modules/**'],
        },
    },
});
