import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    vue(),
    laravel({
      input: [
        'src/main.js',
        'backend/resources/css/app.css',
        'backend/resources/sytles/style.css',
        'backend/resources/sytles/main.css'
      ],
      publicDirectory: 'backend/public',
      buildDirectory: 'frontend',
      refresh: true,
    }),
  ],
  build: {
    outDir: './backend/public/frontend',
    emptyOutDir: true,
    manifest: 'manifest.json',
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    hmr: { host: 'localhost' },
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
  }
});
