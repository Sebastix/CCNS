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
          info: "blue",
          warning: "ea580ccc", // same color as decoration-orange-600/80
        },
      },
    ]
  }
}
