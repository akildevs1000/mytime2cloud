// holds your root state
export const state = () => ({
  first_login: 1,
  color: "primary",
  employee_id: "",
  main_report_type: "",
  loginType: "manager",
  shift_type_id: "",
  shift_id: "",
  shift_name: "",
  branch_id: "",

  devices: [],
  employees: null,
  department_list: null,
  timezone_list: null,
  branches_list: null,
  roles: null,
  designation_list: null,
  leave_groups: null,
  leave_managers: null,

  login_token: "",
  email: "",
  password: "",
});

// contains your mutations
export const mutations = {
  RESET_STATE(state) {
    // Object.keys(state).forEach((key) => {
    //   state[key] = null;
    // });
    state = () => ({
      first_login: 1,
      color: "primary",
      employee_id: "",
      main_report_type: "",
      loginType: "manager",
      shift_type_id: "",
      shift_id: "",
      shift_name: "",
      branch_id: "",
      devices: [],
      employees: null,
      department_list: null,
      timezone_list: null,
      branches_list: null,
      roles: null,
      designation_list: null,
      leave_groups: null,
      leave_managers: null,

      login_token: "",
      email: "",
      password: "",
    });
  },
  login_token(state, value) {
    state.login_token = value;
  },
  email(state, value) {
    state.email = value;
  },
  password(state, value) {
    state.password = value;
  },
  first_login(state, value) {
    state.first_login = value;
  },
  devices(state, value) {
    state.devices = value;
  },
  employees(state, value) {
    state.employees = value;
  },
  department_list(state, value) {
    state.department_list = value;
  },

  timezone_list(state, value) {
    state.timezone_list = value;
  },

  designation_list(state, value) {
    state.designation_list = value;
  },
  roles(state, value) {
    state.roles = value;
  },
  leave_groups(state, value) {
    state.leave_groups = value;
  },
  leave_managers(state, value) {
    state.leave_managers = value;
  },
  loginType(state, value) {
    state.loginType = value;
  },
  branch_id(state, value) {
    state.branch_id = value;
  },
  change_color(state, value) {
    state.color = value;
  },
  employee_id(state, value) {
    state.employee_id = value;
  },
  main_report_type(state, value) {
    state.main_report_type = value;
  },
  shift_type_id(state, value) {
    state.shift_type_id = value;
  },
  shift_id(state, value) {
    state.shift_id = value;
  },
  shift_name(state, value) {
    state.shift_name = value;
  },

  branches_list(state, value) {
    state.branches_list = value;
  },
};

export const actions = {
  resetState({ commit }) {
    commit("RESET_STATE");
  },

  async branches_list({ commit, state }) {

    if (state.branches_list) return state.branches_list;

    try {
      const { data } = await this.$axios.get(`branch-list`, {
        params: {
          order_by: "name",
          company_id: this.$auth.user.company_id,
        },
      });
      commit("branches_list", data);

      return data;
    } catch (error) {
      // Handle the error
      console.error("Error fetching branch list", error);
    }
  },

  async roles_list({ commit, state }) {
    if (state.roles) return state.roles;

    try {
      const { data } = await this.$axios.get('role-list', {
        params: {
          order_by: "name",
          company_id: this.$auth.user.company_id,
        },
      })
      commit("roles", data);
      return data;
    } catch (error) {
      return error;
    }
  },

  async leave_groups_list({ commit, state }) {
    if (state.leave_groups) return state.leave_groups;

    try {
      const { data } = await this.$axios.get('leave-group-list', {
        params: {
          order_by: "name",
          company_id: this.$auth.user.company_id,
        },
      })
      commit("leave_groups", data);
      return data;
    } catch (error) {
      return error;
    }
  },

  async leave_managers_list({ commit, state }) {
    if (state.leave_managers) return state.leave_managers;

    try {
      const { data } = await this.$axios.get('employee-list', {
        params: {
          order_by: "name",
          company_id: this.$auth.user.company_id,
        },
      })
      commit("leave_managers", data);
      return data;
    } catch (error) {
      return error;
    }
  },

  async employees({ commit, state }, options) {
    try {
      if (state.employees && options.isFilter == false) return state.employees;
      const { data } = await this.$axios.get(options.endpoint, options)
      commit("employees", data);
      return data;
    } catch (error) {
      return error;
    }
  },

  async department_list({ commit, state }, options) {
    try {
      if (state.department_list && options.isFilter == false) return state.department_list;
      const { data } = await this.$axios.get(options.endpoint, options)
      commit("department_list", data);
      return data;
    } catch (error) {
      return error;
    }
  },

  async timezone_list({ commit, state }, options) {
    try {
      if (state.timezone_list && options.isFilter == false) return state.timezone_list;
      const { data } = await this.$axios.get(options.endpoint, options)
      commit("timezone_list", data);
      return data;
    } catch (error) {
      return error;
    }
  },

  async designation_list({ commit, state }, options) {
    try {
      if (state.designation_list && options.isFilter == false) return state.designation_list;
      const { data } = await this.$axios.get(options.endpoint, options)
      commit("designation_list", data);
      return data;
    } catch (error) {
      return error;
    }
  },



};
