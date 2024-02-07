module.exports = {
  content: [
    "**/*.twig",
    "../../../modules/custom/**/*.twig",
  ],
  theme: {
    container: {
      center: true,
    },
    extend: {},
  },
  safelist: [
    {
      pattern: /rotate-.+/,
    },
  ],
  variants: {
    extend: {},
  },
  plugins: [
    require('daisyui')
  ],
  daisyui: {
    themes: [
      {
        lofi: {
          ...require("daisyui/src/theming/themes")["lofi"],
          info: "FF4D4D",
          success: "6df78a",
          warning: "FF4D4D",
          error: "f64d4d"
        },
      },
    ],
  }
}
