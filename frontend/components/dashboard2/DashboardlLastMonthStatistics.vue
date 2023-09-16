<template>
    <div>


        <v-row>
            <v-col md=" 6">
                <h6>Previous Week Attendance</h6>
            </v-col>

            <v-col md="2"> <v-icon>mdi-dots-vertical</v-icon></v-col>

        </v-row>
        <v-row>

            <v-col md="2">
                <v-avatar color="#FFCDD2">
                    <v-icon>mdi-account</v-icon>
                </v-avatar>

            </v-col>
            <v-col md="10">{{ data.absentCount }}<br /> Absents</v-col>
        </v-row>
        <v-row>
            <v-col md="2">

                <v-avatar color="#BBDEFB">
                    <v-icon>mdi-account</v-icon>
                </v-avatar>
            </v-col>
            <v-col md="10">{{ data.leaveCount }}<br /> Leaves</v-col>
        </v-row>
        <v-row>
            <v-col md="2">
                <v-avatar color="#FFE0B2">
                    <v-icon>mdi-account-minus</v-icon>
                </v-avatar>
            </v-col>
            <v-col md="10">{{ data.missingCount }}<br /> Missing</v-col>
        </v-row>

        <v-row>
            <v-col md="12">

                <v-btn size="small" class="btn   btn-block   fa-lg   mt-1 mb-3  "
                    style="background-color: #6946dd;color:#FFF">
                    View Reports
                </v-btn>
            </v-col>

        </v-row>

    </div>
</div></template>
<script>
export default {
    data: () => ({
        options: {},

        loading: false,
        dataLength: 0,


        data: [],
    }),

    created() {
        this.getDataFromApi();
    },

    methods: {
        getDataFromApi() {
            let options = {
                params: {

                    company_id: this.$auth.user.company_id,
                },
            };

            this.$axios.get("dashboard_get_count_previous_month", options).then(({ data }) => {
                this.data = data[0];
            });
        },
    },
};
</script>
  
<style scoped>
.center-both {
    height: 31vh;
    /* Adjust the height as needed */
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
  