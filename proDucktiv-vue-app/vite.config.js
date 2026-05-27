import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  base: '/cos30043/s103071591/proDucktiv/',
  plugins: [vue()],

  // Proxy API requests to backend server during development
  server: {
    proxy: {
      '/cos30043/s103071591/proDucktiv/resources': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        // Remove the base path when forwarding to backend
        rewrite: (path) => path.replace('/cos30043/s103071591/proDucktiv', '')
      },
    },
  },
})
