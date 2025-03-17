export default ({ $axios, store }, inject) => {
  // Add an interceptor to modify requests globally
  $axios.onRequest(async (config) => {
    config.baseURL = process.env.BACKEND_URL || `http://${window.location.hostname || "localhost"}:8000/api`
    console.log("🚀 ~ $axios.onRequest ~ process.env.BACKEND_URL:", process.env.BACKEND_URL)
    console.log("🚀 ~ $axios.onRequest ~ baseURL:", config.baseURL)

    let user = store.state.auth.user;

    if (user) {
      config.params = {
        ...config.params,
        company_id: user.company_id,
      };
    }
    
    if (user && user.branch_id && user.branch_id > 0) {
      config.params = {
        ...config.params,
        branch_id: user && user.branch_id,
      };
    }

    if (user && user.user_type == "department") {
      config.params = {
        ...config.params,
        department_id: user && user.department_id,
        user_type: user && user.user_type,
      };
    }

    return config; // Return the modified config
  });
};
