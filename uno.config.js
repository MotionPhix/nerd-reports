import {
  defineConfig,
  presetIcons, presetTypography, presetUno, presetWebFonts,
  transformerDirectives, transformerVariantGroup,
} from 'unocss'

export default defineConfig({
  shortcuts: [
    // ...
  ],

  theme: {
    darkMode: 'class',
  },

  presets: [
    presetUno(),

    presetIcons(),

    presetTypography(),

    presetWebFonts({
      provider: 'none',

      fonts: {
        sans: 'v-sans',
        display: 'Source Serif Pro',
      },
    }),
  ],

  transformers: [
    transformerDirectives(),
    transformerVariantGroup(),
  ],
})
