<template>
  <div style="width: 100%">
    <v-row style="width: 100%">
      <v-col lg="9" md="9" sm="12" xs="12">
        <v-row>
          <v-col md="12">
            <v-card class="pa-2" style="height: 354px; overflow: hidden">
              <v-row background fill>
                <v-col
                  lg="8"
                  md="8"
                  sm="12"
                  xs="12"
                  class="d-xs-flex"
                  style="flex: auto"
                >
                  <DashboardAttendanceChart
                    :name="'AttendanceChart1'"
                    :height="'300'"
                  />
                </v-col>
                <v-col
                  lg="4"
                  md="4"
                  sm="12"
                  xs="12"
                  class="d-xs-flex pa-2"
                  style="border-left: 1px solid #ddd"
                >
                  <DashboardlLastMonthStatistics name="LastMonthStatistics" />
                </v-col>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2" style="height: 772px; overflow: hidden">
              <DashboardRealTimeLogTableview />
            </v-card>
          </v-col>
        </v-row>
        <v-row class="d-xs-flex">
          <v-col
            lg="6"
            md="6"
            sm="12"
            xs="12"
            class="d-xs-flex"
            style="flex: auto"
          >
            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <DashboardAttendanceHourChart
                :name="'AttendanceChart2'"
                :height="'260'"
              />
            </v-card>
          </v-col>
          <v-col lg="6" md="6" sm="12" xs="12">
            <!-- <DashboardlastMultiStatistics /> -->

            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <DashboardLoginActivities />
            </v-card>
            <!-- <v-card class="mt-2" style="height: 350px"> </v-card> -->
          </v-col>
        </v-row>
      </v-col>

      <v-col lg="3" md="3" sm="12" xs="12">
        <DashboardRightsideStaticstics />

        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2 mt-2" style="height: 510px; overflow: hidden">
              <DashboardAnnouncment />
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <DashboardAttednaceDepartmentWise
                name="AttendanceDepartmentWise"
              />
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import DashboardAttendanceChart from "../../components/dashboard2/DashboardAttendanceChartV1.vue";
import DashboardRealTimeLogTableview from "../../components/dashboard2/DashboardRealTimeLogTableviewV1.vue";
import DashboardAnnouncment from "../../components/dashboard2/DashboardAnnouncment.vue";
import DashboardLoginActivities from "../../components/dashboard2/DashboardLoginActivities.vue";
import DashboardAttednaceDepartmentWise from "../../components/dashboard2/DashboardAttednaceDepartmentWise.vue";
import DashboardRightsideStaticstics from "../../components/dashboard2/DashboardRightsideStaticstics.vue";
import DashboardlastMultiStatistics from "../../components/dashboard2/DashboardlastMultiStatistics.vue";
import DashboardlLastMonthStatistics from "../../components/dashboard2/DashboardlLastMonthStatistics.vue";
import DashboardAttendanceHourChart from "../../components/dashboard2/DashboardAttendanceHourChart.vue";
export default {
  components: {
    DashboardAttendanceChart,
    DashboardRealTimeLogTableview,
    DashboardAnnouncment,
    DashboardLoginActivities,
    DashboardAttednaceDepartmentWise,
    DashboardRightsideStaticstics,
    DashboardlastMultiStatistics,
    DashboardlLastMonthStatistics,
    DashboardAttendanceHourChart,
  },
  data() {
    return {};
  },
  mounted() {
    // if (this.$auth.user.user_type == "employee") {
    //   this.$router.push(`/dashboard/employee`);
    // }
  },
  created() {
    // if (this.$auth.user.user_type == "employee") {
    //   this.$router.push(`/dashboard/employee`);
    // }
  },

  methods: {},
};
</script>
