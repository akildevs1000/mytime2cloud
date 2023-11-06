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
  employees: [],
  login_token: "",
});

// contains your mutations
export const mutations = {
  login_token(state, value) {
    state.login_token = value;
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
    // console.log(value);
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
};
