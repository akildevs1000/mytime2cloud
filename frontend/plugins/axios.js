export default ({ $axios, store }, inject) => {

  $axios.onRequest(async (config) => {
    // Define global URLs
    const backendURL = process.env.BACKEND_URL || `http://${window.location.hostname || "localhost"}:8000/api`;
    const appURL = `http://${window.location.hostname || "localhost"}:3001`;

    // Inject appURL globally
    inject("appUrl", appURL);
    config.baseURL = backendURL; // Set backend API URL

    let user = store.state.auth.user;

    if (user) {
      config.params = { ...config.params, company_id: user.company_id };
    }

    if (user && user.branch_id && user.branch_id > 0) {
      config.params = { ...config.params, branch_id: user.branch_id };
    }

    if (user && user.user_type == "department") {
      config.params = {
        ...config.params,
        department_id: user.department_id,
        user_type: user.user_type
      };
    }

    console.log("🚀 ~ $axios.onRequest ~ baseURL:", config.baseURL);
    console.log("🚀 ~ App URL:", appURL); // Log injected app URL

    return config;
  });
};
