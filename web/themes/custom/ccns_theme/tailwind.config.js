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
    'my-1',
    'my-2',
    'my-4',
    'my-8',
    'mb-1',
    'mb-2',
    'mb-4',
    'mb-8',
    'p-2',
    'bg-neutral-100',
    'bg-amber-100',
    'loading',
    'loading-spinner',
    'loading-xs',
    'text-center',
    'skeleton',
    'text-wrap',
    'gap-1',
    'gap-4',
    'justify-center',
    'cursor-not-allowed',
    {
      pattern: /rotate-.+|loading-.+/,
    },
  ],
  variants: {
    extend: {},
  }
}
