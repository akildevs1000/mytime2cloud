<template>
    <div style="width:100%">
        <v-row style="width:100%">

            <v-col lg="9" md="9" sm="12" xs="12">
                <v-card class="pa-2">
                    <v-row background fill>
                        <v-col md=" 8" style="width:100%">
                            <DashboardAttendanceChart :name="'AttendanceChart1'" :height="'250'" style="width:100%" />

                        </v-col>


                        <v-col md="4" class="pa-2" style="    border-left: 1px solid #DDD;">

                            <DashboardlLastMonthStatistics />

                        </v-col>
                    </v-row>
                </v-card>
                <v-row class="pt-2">
                    <v-col md="6">
                        <v-card class="py-2 mt-2" style="height:500px">

                            <DashboardAttednaceDepartmentWise />

                        </v-card>
                    </v-col>
                    <v-col md="6">
                        <DashboardlastMultiStatistics />


                        <v-row>
                            <v-col md="12">
                                <v-card class="py-2  " style="height:250px">


                                    <DashboardAttendanceHourChart :name="'AttendanceChart2'" :height="'200'" />
                                </v-card>

                            </v-col>

                        </v-row>

                    </v-col>
                </v-row>



                <v-row>
                    <v-col md="12">
                        <v-card class="py-2  " style="min-height: 300px">

                            <DashboardRealTimeLogTableview />
                        </v-card>


                    </v-col>
                </v-row>
            </v-col>

            <v-col lg="3" md="3" sm="12" xs="12">

                <DashboardRightsideStaticstics />

                <v-row>
                    <v-col md="12">


                        <v-card class="py-2 mt-2" style="min-height:500px">
                            <DashboardLoginActivities />


                        </v-card>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col md="12">
                        <v-card class="py-2 mt-2">

                            <DashboardAnnouncment style="min-height:500px" />


                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </div>
</template>

<script>
import DashboardAttendanceChart from "../../components/dashboard2/DashboardAttendanceChart.vue";
import DashboardRealTimeLogTableview from "../../components/dashboard2/DashboardRealTimeLogTableview.vue";
import DashboardAnnouncment from "../../components/dashboard2/DashboardAnnouncment.vue";
import DashboardLoginActivities from "../../components/dashboard2/DashboardLoginActivities.vue";
import DashboardAttednaceDepartmentWise from "../../components/dashboard2/DashboardAttednaceDepartmentWise.vue";
import DashboardRightsideStaticstics from "../../components/dashboard2/DashboardRightsideStaticstics.vue";
import DashboardlastMultiStatistics from "../../components/dashboard2/DashboardlastMultiStatistics.vue";
import DashboardlLastMonthStatistics from "../../components/dashboard2/DashboardlLastMonthStatistics.vue";
import DashboardAttendanceHourChart from "../../components/dashboard2/DashboardAttendanceHourChart.vue";
export default {
    components: { DashboardAttendanceChart, DashboardRealTimeLogTableview, DashboardAnnouncment, DashboardLoginActivities, DashboardAttednaceDepartmentWise, DashboardRightsideStaticstics, DashboardlastMultiStatistics, DashboardlLastMonthStatistics, DashboardAttendanceHourChart },
    data() {
        return {

        };
    },
    created() {


    },

    methods: {


    },

};
</script>