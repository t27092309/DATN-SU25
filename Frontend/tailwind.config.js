/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',  // ✅ Bắt buộc phải có dòng này để quét mọi file .vue trong src/
  ],
  safelist: [
    'group-hover:opacity-100',
    'group-hover:visible',
    'group-hover:scale-100',
    'opacity-0',
    'scale-95',
    'invisible',
    'transition',
    'duration-200',
    'pointer-events-none',
    'pointer-events-auto'
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
