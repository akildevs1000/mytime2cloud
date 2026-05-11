/** @type {import('tailwindcss').Config} */
export default {
  content: ["./src/ui/**/*.{html,ts,tsx}"],
  theme: {
    extend: {
      colors: {
        // A small palette so the UI feels consistent.
        bg:        "#0d1117",
        panel:     "#161b22",
        border:    "#30363d",
        muted:     "#8b949e",
        text:      "#c9d1d9",
        accent:    "#58a6ff",
        success:   "#3fb950",
        warn:      "#d29922",
        danger:    "#f85149"
      }
    }
  },
  plugins: []
};
