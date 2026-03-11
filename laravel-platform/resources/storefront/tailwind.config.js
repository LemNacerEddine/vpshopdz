/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './**/*.{ts,tsx,html}',
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary)',
        secondary: 'var(--color-secondary)',
        accent: 'var(--color-accent)',
      },
      fontFamily: {
        heading: 'var(--font-heading)',
        body: 'var(--font-body)',
      },
      borderRadius: {
        button: 'var(--button-radius)',
        card: 'var(--card-radius)',
        input: 'var(--input-radius)',
      },
    },
  },
  plugins: [],
};
