module.exports = {
  content: [
    "**/*.twig",
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
          warning: "FF4D4D"
        },
      },
    ]
  }
}
