<template>
  <div
    style="width: 100%"
    v-if="can('dashboard_access') && can('dashboard_view')"
  >
    <v-overlay :value="overlay">
      <v-progress-circular indeterminate size="64"></v-progress-circular>
    </v-overlay>

    <v-row style="width: 100%">
      <v-col lg="9" md="9" sm="12" xs="12">
        <v-row>
          <v-col md="12">
            <v-card class="pa-2" style="height: 400px; overflow: hidden">
              <v-row background fill>
                <v-col
                  lg="8"
                  md="8"
                  sm="12"
                  xs="12"
                  class="d-xs-flex"
                  style="flex: auto"
                >
                  <CommDashboardAttendanceChart
                    :branch_id="branch_id"
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
                  <CommDashboardLastMonthStatistics
                    :branch_id="branch_id"
                    name="LastMonthStatistics"
                  />
                </v-col>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2" style="height: 772px; overflow: hidden">
              <CommDashboardRealTimeLogTableview :branch_id="branch_id" />
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
              <CommDashboardAttendanceHourChart
                :branch_id="branch_id"
                :name="'AttendanceChart2'"
                :height="'260'"
              />
            </v-card>
          </v-col>
          <v-col lg="6" md="6" sm="12" xs="12">
            <!-- <CommDashboardlastMultiStatistics /> -->

            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <CommDashboardLoginActivities :branch_id="branch_id" />
            </v-card>
            <!-- <v-card class="mt-2" style="height: 350px"> </v-card> -->
          </v-col>
        </v-row>
      </v-col>

      <v-col lg="3" md="3" sm="12" xs="12">
        <v-card class="py-2 mb-2" v-if="branchList.length > 1">
          <v-row class="mt-2">
            <v-col cols="1">
              <label> </label>
            </v-col>
            <v-col cols="3"> </v-col>

            <v-col cols="4" class="pa-0 ma-0">
              <v-autocomplete
                class="no-border no-underline border-0"
                style="width: 150px"
                @change="overlay = !overlay"
                v-model="branch_id"
                dense
                text
                :items="[{ name: 'All Branches', id: '' }, ...branchList]"
                item-text="name"
                item-value="id"
              ></v-autocomplete>
            </v-col>
            <v-col cols="4">
              <label> </label>
            </v-col>
          </v-row>
        </v-card>

        <CommDashboardRightsideStaticstics :branch_id="branch_id" />

        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2 mt-2" style="height: 443px; overflow: hidden">
              <CommDashboardAnnouncment :branch_id="branch_id" />
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <CommDashboardAttednaceDepartmentWise
                :branch_id="branch_id"
                name="AttendanceDepartmentWise"
              />
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>

  <NoAccess v-else />
</template>

<script>
import CommDashboardAttendanceChart from "../../../components/Community/Dashboard/CommDashboardAttendanceChartV1.vue";
import CommDashboardLastMonthStatistics from "../../../components/Community/Dashboard/CommDashboardlLastMonthStatisticsV1.vue";
import CommDashboardRealTimeLogTableview from "../../../components/Community/Dashboard/CommDashboardRealTimeLogTableviewV1.vue";
import CommDashboardRightsideStaticstics from "../../../components/Community/Dashboard/CommDashboardRightsideStaticsticsV1.vue";
import CommDashboardAnnouncment from "../../../components/Community/Dashboard/CommDashboardAnnouncmentV1.vue";
import CommDashboardAttendanceHourChart from "../../../components/Community/Dashboard/CommDashboardAttendanceHourChartV1.vue";
import CommDashboardLoginActivities from "../../../components/Community/Dashboard/CommDashboardLoginActivitiesV1.vue";
import CommDashboardAttednaceDepartmentWise from "../../../components/Community/Dashboard/CommDashboardAttednaceDepartmentWiseV1.vue";
// import DashboardlastMultiStatistics from "../../components/dashboard2/DashboardlastMultiStatistics.vue";
export default {
  components: {
    CommDashboardAttendanceChart,
    CommDashboardLastMonthStatistics,
    CommDashboardRealTimeLogTableview,
    CommDashboardAnnouncment,
    CommDashboardLoginActivities,
    CommDashboardAttednaceDepartmentWise,
    CommDashboardRightsideStaticstics,
    // DashboardlastMultiStatistics,
    CommDashboardAttendanceHourChart,
  },
  data() {
    return {
      branchList: [],
      selectedBranchName: "All Branches",
      seelctedBranchId: "",
      branch_id: "",
      overlay: false,
    };
  },
  // watch: {
  //   branch_id(branch_id) {
  //     return branch_id > 0 ? branch_id : null;
  //   },
  // },
  mounted() {
    // if (this.$auth.user.user_type == "employee") {
    //   this.$router.push(`/dashboard/employee`);
    // }

    if (this.$auth.user.branch_id == 0 && this.$auth.user.is_master == false) {
      alert("You do not have permission to access this branch");
      //this.$router.push("/login");
      this.$axios.get(`/logout`).then(({ res }) => {
        this.$auth.logout();
        this.$router.push(`/login`);
      });

      this.$router.push(`/login`);
      return "";
    }
  },
  async created() {
    if (this.$auth.user.branch_id == 0 && this.$auth.user.is_master == false) {
      alert("You do not have permission to access this branch");
      //this.$router.push("/login");
      this.$axios.get(`/logout`).then(({ res }) => {
        this.$auth.logout();
        this.$router.push(`/login`);
      });

      this.$router.push(`/login`);
      return "";
    }

    try {
      await this.$store.dispatch("fetchDropDowns", {
        key: "deviceList",
        endpoint: "device-list",
        refresh: true,
      });
      await this.$store.dispatch("fetchDropDowns", {
        key: "employeeList",
        endpoint: "employee-list",
        refresh: true,
      });
      this.branchList = await this.$store.dispatch("fetchDropDowns", {
        key: "branchList",
        endpoint: "branch-list",
        refresh: true,
      });
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  },
  watch: {
    overlay(val) {
      val &&
        setTimeout(() => {
          this.overlay = false;
        }, 3000);
    },
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    openalert(data) {
      alert("");
    },
    filterBranch(branch) {
      this.$emit("openalert", "");

      // if (branch) {
      //   this.selectedBranchName = branch.branch_name;
      //   this.seelctedBranchId = branch.id;
      //   this.branch_id = branch.id;
      // } else {
      //   this.selectedBranchName = "All Branches";
      //   this.seelctedBranchId = "";
      //   this.branch_id = "";
      // }
    },
  },
};
</script>

<style scoped>
.theme--light.v-text-field > .v-input__control > .v-input__slot:before {
  border-color: #fff !important;
}

.no-border:before {
  border-color: #fff !important;
}
</style>
