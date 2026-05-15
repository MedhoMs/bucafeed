import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  root: './', // Asegura que el escaneo sea relativo a la raíz definida
  build: {
    outDir: './dist',
    emptyOutDir: true,
    sourcemap: false,
    cssSourceMap: false,
    rollupOptions: {
      output: {
        chunkFileNames: 'assets/[hash].js',
        entryFileNames: 'assets/[hash].js',
        assetFileNames: 'assets/[hash].[ext]',
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      clientPort: 5173,
    },
    allowedHosts: ['telamonet.com', '.up.railway.app', 'localhost', '127.0.0.1'],
    watch: {
      usePolling: false,
      ignored: [
        '**/backend/**',
        '**/signaling/**',
        '**/node_modules/**',
        '**/.git/**',
        '**/dist/**',
        '**/public/**',
        '**/docker/**',
        '**/docker-compose.yml',
        '**/Dockerfile'
      ]
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  css: {
    devSourcemap: false,
    preprocessorOptions: {
      scss: {}
    }
  }
})
