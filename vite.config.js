import { defineConfig } from "vite"
import laravel from "laravel-vite-plugin"
import vue from "@vitejs/plugin-vue"
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [
    laravel({
      input: "resources/js/app.js",
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    tailwindcss()
  ],
  server: {
    cors: {
      origin: /^https?:\/\/(?:(?:[^:]+\.)?localhost|reports|nerd-reports\.test|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
    },
  },
})
