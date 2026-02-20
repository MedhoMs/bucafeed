import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

// Configuración para DESARROLLO LOCAL
export default defineConfig({
    plugins: [vue()],
    build: {
        rollupOptions: {
            input: {
                main: fileURLToPath(new URL('./src/main.js', import.meta.url)),
                'backend-css': fileURLToPath(new URL('./backend/resources/css/app.css', import.meta.url))
            }
        }
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
            interval: 1000,
            ignored: ['**/node_modules/**', '**/backend/vendor/**', '**/backend/storage/**']
        },
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
            '@sytles': fileURLToPath(new URL('./backend/resources/sytles', import.meta.url)),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                // variables.scss está ausente
            }
        }
    }
});
