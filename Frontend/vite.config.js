import { fileURLToPath, URL } from 'node:url'; // Có sẵn
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueDevTools from 'vite-plugin-vue-devtools';
import svgLoader from 'vite-svg-loader';

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    svgLoader(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    rollupOptions: {
      input: {
        // Entry point cho trang client
        main: fileURLToPath(new URL('./index.html', import.meta.url)),

        // Entry point cho trang admin
        admin: fileURLToPath(new URL('./admin.html', import.meta.url)),
      },
    },
  },
});