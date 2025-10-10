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
    'my-2',
    'my-4',
    'my-8',
    'p-2',
    'bg-neutral-100',
    'bg-amber-100',
    'loading',
    'loading-spinner',
    'loading-xs',
    'text-center',
    'skeleton',
    {
      pattern: /rotate-.+|loading-.+/,
    },
  ],
  variants: {
    extend: {},
  }
}
