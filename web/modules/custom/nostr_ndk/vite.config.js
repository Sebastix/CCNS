import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    target: 'esnext',
    manifest: true,
    rollupOptions: {
      input: ['/js/main.js'],
      output: {
        entryFileNames: `js/[name].js`,
        chunkFileNames: `chunks/[name].[hash].js`,
        assetFileNames: `[name].[ext]`
      }
    }
  }
})
