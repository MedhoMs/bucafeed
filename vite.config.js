import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [vue(), laravel({ input: 'src/main.js', publicDirectory: 'backend/public', buildDirectory: 'frontend', refresh: true, }),], build: { outDir: './backend/public/frontend', emptyOutDir: true, manifest: true, rollupOptions: { input: 'src/main.js', }, }, server: { host: '0.0.0.0', port: 5173, strictPort: true, hmr: { host: 'localhost' }, watch: { usePolling: true }, }, resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)), }, },
});