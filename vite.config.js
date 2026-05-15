import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  build: {
    outDir: './dist',
    emptyOutDir: true,
    sourcemap: false,        // sin .map en JS
    cssSourceMap: false,     // sin .map en CSS
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
    allowedHosts: ['telamonet.com', '.up.railway.app', 'localhost'],
    watch: {
      usePolling: true,
      interval: 1000,
      ignored: ['**/node_modules/**', '**/backend/vendor/**', '**/backend/storage/**']
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  css: {
    devSourcemap: false,     // sin source maps de CSS en dev
    preprocessorOptions: {
      scss: {}
    }
  }
})
