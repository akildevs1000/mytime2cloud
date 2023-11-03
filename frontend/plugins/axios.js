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

    // config.cache = {
    //   maxAge: 10 * 1000, // Set maxAge to 30 minutes if the condition is met
    // };
    // if (config.cache) {
    //   config.cache = {
    //     maxAge: 10 * 1000, // Set maxAge to 30 minutes if the condition is met
    //   };
    // } else {
    //   config.cache = {
    //     maxAge: 0, // Set maxAge to 1 hour otherwise
    //   };
    // }
  });
};
