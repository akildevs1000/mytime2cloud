// holds your root state
export const state = () => ({
    first_login: 1,
    color: "primary",
    employee_id: "",
    main_report_type: "",
    loginType: "manager",
    devices: [],
    employees: [],
});

// contains your mutations
export const mutations = {
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
    change_color(state, value) {
        state.color = value;
    },
    employee_id(state, value) {
        state.employee_id = value;
    },
    main_report_type(state, value) {
        // console.log(value);
        state.main_report_type = value;
    }
};
