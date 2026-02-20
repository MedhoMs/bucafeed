import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

// Determinar si 'src' está en '../src' (local) o './src' (Docker build)
// En local: backend/vite.config.js -> ../src
// En prod flattener: /var/www/vite.config.js -> ./src (copiado por Docker)
const srcPath = fs.existsSync(path.resolve(__dirname, '../src')) ? '../src' : 'src';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/sytles/style.css',
                'resources/sytles/main.css'
            ],
            buildDirectory: 'frontend',
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
