<template>
    <div>
        <v-row>

            <v-col md="9">
                <v-card class="pa-2">
                    <v-row background fill>
                        <v-col md=" 8">

                            <h4>September 2023 Attendance</h4>
                            <DashboardAttendanceChart :name="'AttendanceChart1'" :height="'400'" />

                        </v-col>


                        <v-col md="4" class="pa-2">
                            Aug 2023 Attendance(previous Month)
                            <v-row>
                                <v-col md="2"><v-icon color="green" size="50" fill>mdi-account-box</v-icon></v-col>
                                <v-col md="10">150<br /> Presents</v-col>
                            </v-row>
                            <v-row>
                                <v-col md="2"><v-icon color="red" size="50" fille>mdi-account-box</v-icon></v-col>
                                <v-col md="10">150<br /> Presents</v-col>
                            </v-row>
                            <v-row>
                                <v-col md="2"><v-icon color="grey" size="50" fill>mdi-account-box</v-icon></v-col>
                                <v-col md="10">150<br /> Presents</v-col>
                            </v-row>

                            <v-row>
                                <v-col md="12">

                                    <v-btn size="small" class="btn   btn-block   fa-lg   mt-1 mb-3  "
                                        style="background-color: #6946dd;color:#FFF">
                                        View Reports
                                    </v-btn>
                                </v-col>

                            </v-row>
                        </v-col>
                    </v-row>
                </v-card>
                <v-row>
                    <v-col md="6">
                        <v-card class="py-2 mt-2" style="height:500px">

                            <DashboardAttednaceDepartmentWise />

                        </v-card>
                    </v-col>
                    <v-col md="6">

                        <v-row>

                            <v-col md="6">
                                <v-card class="py-2 mt-2" style="height:230px">


                                    <v-row>
                                        <v-col md="6"><v-icon color="green" size="50" fill>mdi-account-box</v-icon></v-col>

                                        <v-col md="6"> <v-icon>mdi-dots-vertical</v-icon></v-col>


                                        <v-col md="12">
                                            <div>Multi In/Out</div>
                                            <div class="text-h4 green--text">180</div>
                                            <div>Today Presents</div>
                                        </v-col>


                                    </v-row>

                                </v-card>
                            </v-col>


                            <v-col md="6">
                                <v-card class="py-2 mt-2" style="height:230px">


                                    <v-row>
                                        <v-col md="6"><v-icon color="green" size="50" fill>mdi-account-box</v-icon></v-col>

                                        <v-col md="6"> <v-icon>mdi-dots-vertical</v-icon></v-col>


                                        <v-col md="12">
                                            <div>Multi In/Out</div>
                                            <div class="text-h4 green--text">180</div>
                                            <div>Today Presents</div>
                                        </v-col>


                                    </v-row>

                                </v-card>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col md="12">
                                <v-card class="py-2  " style="height:250px">Hourly In/Out


                                    <DashboardAttendanceChart :name="'AttendanceChart2'" :height="'200'" />
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

            <v-col md="3">

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

export default {
    components: { DashboardAttendanceChart, DashboardRealTimeLogTableview, DashboardAnnouncment, DashboardLoginActivities, DashboardAttednaceDepartmentWise, DashboardRightsideStaticstics },
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