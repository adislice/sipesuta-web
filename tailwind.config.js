module.exports = {
  content: ["./**/*.{html,js,php}"],
  theme: {
    extend: {},
    fontFamily: {
      sans: ['Poppins', 'sans-serif']
    }
  },
  darkMode: 'class',
  plugins: [require("daisyui")],
  daisyui: {
    themes:[
      {
        light:{
          ...require("daisyui/src/colors/themes")["[data-theme=light]"],
          primary: '#45a6eb',
          "--btn-text-case": "capitalize",
          "--tab-color": "#1f2937",
        }
      }
    ]
  }
}
