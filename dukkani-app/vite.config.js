import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Old phones in the shops we build for often run Android 7-10 era browsers.
// Tailwind v4 emits modern colours (oklab/color-mix) and gradient syntax that
// those browsers silently drop, which removes tinted panels and the dark
// shading over hero photos. Lightning CSS writes plain rgba/hex fallbacks for
// them while keeping the richer colour for modern browsers.
const legacyTargets = {
    chrome: 70 << 16,
    edge: 79 << 16,
    firefox: 78 << 16,
    ios_saf: 12 << 16,
    safari: 12 << 16,
};

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    css: {
        transformer: 'lightningcss',
        lightningcss: {
            targets: legacyTargets,
        },
    },
    build: {
        cssMinify: 'lightningcss',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
