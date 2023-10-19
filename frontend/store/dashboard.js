// store/dashboard.js

export const state = () => ({
    dashboardData: null,
    date_from: null, // Add 'date_from' property
    date_to: null,   // Add 'date_to' property
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