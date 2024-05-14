import colors from "vuetify/es5/util/colors";

export default {
  buildDir: ".nuxt",
  // Target: https://go.nuxtjs.dev/config-target
  target: "static",
  generate: {
    // Interval in milliseconds between two render cycles to avoid
    // flooding a potential API with calls from the web application.
    interval: 500,
  },
  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    titleTemplate: "",
    title: "MyTime 2 Cloud",
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { hid: "description", name: "description", content: "" },
      { name: "format-detection", content: "telephone=no" },
    ],
    css: [
    ],

    link: [
      { rel: "icon", type: "image/x-icon", href: "/favicon.ico" },
    ],
    script: [
    ],
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  // css: ["~/assets/styles"],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [
    "~/plugins/qrcode.js",
    "~/plugins/custom-methods.js",
    { src: "~/plugins/crypto.js", mode: "client" },
    { src: "~/plugins/axios.js" },
    { src: "~/plugins/TiptapVuetify", mode: "client" },
    { src: "~/plugins/vue-apexchart.js", ssr: false },
  ],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: true,

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    // https://go.nuxtjs.dev/vuetify
    "@nuxtjs/vuetify",
    "@nuxtjs/dotenv",
  ],

  // Modules: https://go.nuxtjs.dev/config-modules
  modules: [
    // https://go.nuxtjs.dev/axios
    "@nuxtjs/axios",
    // https://go.nuxtjs.dev/pwa
    "@nuxtjs/pwa",
    "@nuxtjs/auth-next",
  ],

  // Axios module configuration: https://go.nuxtjs.dev/config-axios
  // axios: {
  //   baseURL: `http://192.168.2.26:8000/api` ,
  // },

  auth: {
    strategies: {
      local: {
        endpoints: {
          login: { url: "login", method: "post", propertyName: "token" },
          logout: false,
          user: { url: "me", method: "get", propertyName: false },
        },
        maxAge: 86400, // 24 hours
      },
    },

    redirect: {
      logout: "/login",
    },
  },

  router: {
    middleware: ["auth"],
  },


  pwa: {
    manifest: {
      name: "MyTime 2 Cloud",
      short_name: "MyTime 2 Cloud",
      lang: "en",
    },
    icon: {
      source: "icon-515x512.png", // Path to your app icon
    },
  },

  // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify

  vuetify: {
    customVariables: ["~/assets/variables.scss"],
    theme: {
      dark: false,
      treeShake: true,
      themes: {
        options: {
          customProperties: true,
        },
        typography: {
          fontFamily: 'Source Sans Pro', // Use the same font family name as declared in @font-face
        },
        light: {
          //primary: "#5fafa3", //green
          primary: "#6946dd", //violoet
          accent: "#d8363a",
          secondary: "#242424",
          background: "#34444c",
          info: colors.teal.lighten1,
          warning: colors.amber.base,
          error: colors.deepOrange.accent4,
          success: colors.green.accent3,
          main_bg: "#ECF0F4",
          violet: "#6946dd",
          popup_background: "#ecf0f4",
        },
      },
    },
  },

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {
    transpile: ["vuetify/lib", "tiptap-vuetify", "vue-apexchart"],
    interval: 500,
  },

  env: {
    SECRET_PASS_PHRASE: process.env.SECRET_PASS_PHRASE,
  },
};
