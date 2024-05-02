export default ({ $axios, store }, inject) => {
  // Add an interceptor to modify requests globally
  $axios.onRequest(async (config) => {
    config.baseURL = `http://${
      window.location.hostname ?? "localhost"
    }:8000/api`;
    // Append the branchid parameter to all requests
    let user = store.state.auth.user;

    if (user?.role?.role_type == "guard") {
      if (user && user.employee && user.employee.branch_id > 0) {
        config.params = {
          ...config.params,
          branch_id: user.employee.branch_id,
        };
      }
    }
    if (user && user.branch_id && user.branch_id > 0) {
      config.params = {
        ...config.params,
        branch_id: user && user.branch_id,
      };
    }

    return config; // Return the modified config
  });
};
