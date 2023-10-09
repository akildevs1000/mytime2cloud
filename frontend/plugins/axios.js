export default ({ $axios, store }, inject) => {
  // Add an interceptor to modify requests globally
  $axios.onRequest((config) => {
    // Append the branchid parameter to all requests
    let user = store.state.auth.user;
    if (user && user.branch_id) {
      config.params = {
        ...config.params,
        branch_id: user && user.branch_id,
      };
    }
  });
};
