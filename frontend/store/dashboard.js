// store/dashboard.js

export const state = () => ({
    dashboardData: null,
    date_from: null, // Add 'date_from' property
    date_to: null,   // Add 'date_to' property
    recent_logs: null,   // Add 'date_to' property
    previous_week_attendance_count: null,   // Add 'date_to' property
    attendance_count: null,   // Add 'date_to' property
    off_devices_count: null,   // Add 'date_to' property
    leaves_request_count: null,   // Add 'date_to' property
});

export const mutations = {
    setDashboardData(state, data) {
        state.dashboardData = data;
    },

    date_from(state, date_from) { // Mutation to set 'date_from'
        state.date_from = date_from;
    },
    date_to(state, date_to) {     // Mutation to set 'date_to'
        state.date_to = date_to;
    },
    recent_logs(state, recent_logs) {     // Mutation to set 'recent_logs'
        state.recent_logs = recent_logs;
    },
    previous_week_attendance_count(state, previous_week_attendance_count) {     // Mutation to set 'previous_week_attendance_count'
        state.previous_week_attendance_count = previous_week_attendance_count;
    },
    attendance_count(state, attendance_count) {     // Mutation to set 'attendance_count'
        state.attendance_count = attendance_count;
    },
    off_devices_count(state, off_devices_count) {     // Mutation to set 'off_devices_count'
        state.off_devices_count = off_devices_count;
    },
    leaves_request_count(state, leaves_request_count) {     // Mutation to set 'leaves_request_count'
        state.leaves_request_count = leaves_request_count;
    },
};

export const actions = {
    async states_for_7_days({ commit, state }) {
        console.log(state.dashboardData);
        if (state.dashboardData && state.date_from && state.date_to) {
            console.log("from store data loaded");
            return state.dashboardData; // Return cached data if available.
        }

        try {
            const { data } = await this.$axios.get('dashboard_counts_last_7_days', {
                params: {
                    company_id: this.$auth.user.company_id,
                    date_from: state.date_from,
                    date_to: state.date_to,
                },
            });

            commit('setDashboardData', data);
            console.log("from database data loaded");

            return data;
        } catch (error) {
            return error;
        }
    },

    setDates({ commit }, { date_from, date_to }) { // Action to set 'date_from' and 'date_to'
        commit('date_from', date_from);
        commit('date_to', date_to);
    },
};  