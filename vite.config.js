import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';
export default defineConfig({
    plugins: [
        laravel({
            input: [ 'resources/css/app.css','resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        viteStaticCopy({
            targets: [
                {
                    src: path.resolve(__dirname, 'resources/sounds'),
                    dest: path.resolve(__dirname,'public'), // 2️⃣
                },
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ["**/vendor/**", "**/node_modules/**"],
        },
    },
});
