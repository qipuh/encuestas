/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        navy: {
          900: '#0a1628',
          800: '#0f1f3d',
          700: '#152b52',
          600: '#1e3a6e'
        },
        brand: {
          green: '#2ecc71',
          'green-dark': '#27ae60',
          'green-light': '#a8e6c3'
        }
      },
      fontFamily: {
        sans: ['Nunito', 'system-ui', 'sans-serif']
      }
    }
  },
  plugins: []
}
