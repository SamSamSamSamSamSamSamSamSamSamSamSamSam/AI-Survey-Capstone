import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Global bundle — loaded on every page
                'resources/sass/app.scss',
                'resources/js/app.js',

                // Page-specific JS — loaded only where needed via @push('scripts')
                'resources/js/admin/dashboard.js',
                'resources/js/admin/department.js',
                'resources/js/admin/users.js',

                // Page-specific SCSS — loaded only where needed via @push('styles')
                'resources/sass/pages/dashboard.scss',
                'resources/sass/pages/department.scss',
                'resources/sass/pages/users.scss',
            ],
            refresh: true,
        }),
    ],
});