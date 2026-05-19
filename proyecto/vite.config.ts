import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    proxy: {
      '/rest/v1': 'http://127.0.0.1:54321',
      '/auth/v1': 'http://127.0.0.1:54321',
      '/storage/v1': 'http://127.0.0.1:54321',
    },
  },
})
