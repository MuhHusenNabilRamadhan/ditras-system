/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./pages/**/*.{php,html,js}",
    "./components/**/*.{php,html,js}",
    "./*.{php,html,js}"
  ],
  theme: {
    extend: {
      fontFamily: {
        serif: ['"Cormorant Garamond"', 'serif'],
        sans: ['"Montserrat"', 'sans-serif'],
      },
      colors: {
        // Contoh palet warna premium untuk DITRAS
        emerald: {
          50: '#f0fdf4',
          600: '#059669', // Warna utama action
        },
        stone: {
          900: '#1c1917', // Warna gelap elegan
        }
      }
    },
  },
  plugins: [],
}