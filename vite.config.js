import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig(({ mode }) => ({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            strategies: 'generateSW',
            registerType: 'autoUpdate',
            devOptions: {
                enabled: false,
            },
            workbox: {
                globPatterns: ['**/*.{js,css,woff,woff2,ttf,eot,svg,png,jpg,jpeg,gif,ico}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https?:\/\/.*\/storage\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'storage-images',
                            expiration: { maxEntries: 50, maxAgeSeconds: 30 * 24 * 60 * 60 },
                        },
                    },
                ],
            },
            manifest: {
                name: 'NAKUNDA BUSINESS SOLUTIONS',
                short_name: 'NAKUNDA',
                description: 'Premium curtains & fabrics — made to measure',
                theme_color: '#09090b',
                background_color: '#09090b',
                display: 'standalone',
                orientation: 'portrait-primary',
                start_url: '/fabrics',
                scope: '/',
                icons: [
                    {
                        src: '/icons/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
        }),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
}));
