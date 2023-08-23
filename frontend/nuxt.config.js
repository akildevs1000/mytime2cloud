import colors from "vuetify/es5/util/colors";

export default {
  // Target: https://go.nuxtjs.dev/config-target
  target: "server",

  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    titleTemplate: "",
    title: "Idea HRMS",
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { hid: "description", name: "description", content: "" },
      { name: "format-detection", content: "telephone=no" },
    ],

    link: [
      { rel: "icon", type: "image/x-icon", href: "/favicon.ico" },

      {
        rel: "stylesheet",
        href: "https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css",
      },
      {
        rel: "stylesheet",
        href: "https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons",
      },
      {
        rel: "stylesheet",
        href: "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css",
      },
      {
        rel: "stylesheet",
        href: "https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/4.4.95/css/materialdesignicons.min.css",
      },
      //Datatables
      // {
      //   rel: "stylesheet",
      //   href: "https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.dataTables.min.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css",
      // },
      // {
      //   rel: "stylesheet",
      //   href: "https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.css",
      // },

      // {
      //   rel: "stylesheet",
      //   href: "https://unpkg.com/cropperjs@1.5.12/dist/cropper.min.css",
      // },
    ],
    script: [
      {
        type: "text/javascript",
        src: "https://code.jquery.com/jquery-3.5.1.js",
        async: false,
        body: false,
      }, // Insert in body
      {
        type: "text/javascript",
        src: "https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js",
        async: false,
        body: false,
      }, // Insert in body

      // // with shorthand
      // {
      //   type: "text/javascript",
      //   src: "https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body

      // // ...
      // {
      //   type: "text/javascript",
      //   src: "https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body

      // {
      //   type: "text/javascript",
      //   src: "https://code.jquery.com/ui/1.12.1/jquery-ui.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body

      {
        type: "text/javascript",
        src: "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js",
        async: false,
        body: false,
      }, // Insert in body
      // {
      //   type: "text/javascript",
      //   src: "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js",
      //   async: false,
      //   body: false,
      // }, //

      // {
      //   type: "text/javascript",
      //   src: "https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body

      // {
      //   type: "text/javascript",
      //   src: "https://unpkg.com/cropperjs@1.5.12/dist/cropper.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body

      // // {
      // //   type: "text/javascript",
      // //   src: "https://unpkg.com/vue@3/dist/vue.global.js",
      // //   async: false,
      // //   body: false,
      // // }, // Insert in body

      // {
      //   type: "text/javascript",
      //   src: "https://unpkg.com/vue-avatar-cropper/dist/avatar-cropper.umd.js",
      //   async: false,
      //   body: false,
      // }, // Insert in body

      // {
      //   type: "text/javascript",
      //   src: "https://wzrd.in/standalone/mime%2flite@latest",
      //   async: false,
      //   body: false,
      // }, // Insert in body
    ],
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  // css: ["~/assets/styles"],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [
    '~/plugins/qrcode.js',
    { src: '~/plugins/crypto.js', mode: 'client' },
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
  axios: {
    baseURL: process.env.BACKEND_URL,
  },

  auth: {
    strategies: {
      local: {
        endpoints: {
          login: { url: "login", method: "post", propertyName: "token" },
          logout: false,
          user: { url: "me", method: "get", propertyName: "user" },
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

  // serverMiddleware: ['~middleware/verify.js'],

  // PWA module configuration: https://go.nuxtjs.dev/pwa
  pwa: {
    manifest: {
      name: 'Your App Name',
      short_name: 'App Name',
      lang: 'en',
    },
    icon: {
      source: 'static/icon.png', // Path to your app icon
    },

  },

  // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify
  vuetify: {
    customVariables: ["~/assets/variables.scss"],
    theme: {
      dark: false,

      themes: {
        light: {
          primary: "#5fafa3",
          accent: "#d8363a",
          secondary: "#242424",
          background: "#34444c",
          info: colors.teal.lighten1,
          warning: colors.amber.base,
          error: colors.deepOrange.accent4,
          success: colors.green.accent3,
          main_bg: "#ECF0F4",
        },
        // extend: {
        //   fontFamily: {
        //     roboto: ["Roboto"],
        //   },
        // },
        // defaultAssets: {
        //   font: {
        //     family: "Roboto",
        //   },
        // },
        // dark: {
        //     // primary: "#fffff",
        //     // accent: "#d8363a",
        //     // secondary: "#242424",
        //     // background: "#34444c",
        //     // info: colors.teal.lighten1,
        //     // warning: colors.amber.base,
        //     // error: colors.deepOrange.accent4,
        //     // success: colors.green.accent3,
        //     // main_bg: "#ECF0F4"
        // }
      },
    },
  },

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {
    transpile: ["vuetify/lib", "tiptap-vuetify", "vue-apexchart"],
  },

  server: {
    host: process.env.LOCAL_IP,
    port: process.env.LOCAL_PORT,
  },

  env: {
    SECRET_PASS_PHRASE: process.env.SECRET_PASS_PHRASE
  },
};
