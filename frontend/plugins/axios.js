export default ({ $axios, store }, inject) => {
  // Define global URLs

  let backendURL = process.env.BACKEND_URL;
  let appURL = process.env.APP_URL;


  if (!process.env.BACKEND_URL) {
    backendURL = `http://${window.location.hostname || "localhost"}:8000/api`;
  }
  if (!process.env.APP_URL) {
    appURL = `http://${window.location.hostname || "localhost"}:3001`;
  }

  // Inject globally (accessible via this.$backendUrl and this.$appUrl)
  inject("backendUrl", backendURL);
  inject("appUrl", appURL);

  $axios.onRequest(async (config) => {
    config.baseURL = backendURL; // Set backend API URL

    let user = store.state.auth.user;

    if (user) {
      config.params = { ...config.params, company_id: user.company_id };
    }

    if (user?.branch_id && user.branch_id > 0) {
      config.params = { ...config.params, branch_id: user.branch_id };
    }

    if (user?.user_type === "department") {
      config.params = {
        ...config.params,
        department_id: user.department_id,
        user_type: user.user_type,
      };
    }

    console.log("🚀 ~ Backend URL:", backendURL);
    console.log("🚀 ~ App URL:", appURL);

    return config;
  });
};
