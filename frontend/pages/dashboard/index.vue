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
                  <!-- <DashboardlLastMonthStatistics
                    :branch_id="branch_id"
                    name="LastMonthStatistics"
                  /> -->
                  <DashboardlTodayStatistics
                    :branch_id="branch_id"
                    name="DashboardlTodayStatistics"
                  />
                </v-col>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2" style="height: 772px; overflow: hidden">
              <DashboardRealTimeLogTableview :branch_id="branch_id" />
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
                :branch_id="branch_id"
                :name="'AttendanceChart2'"
                :height="'260'"
              />
            </v-card>
          </v-col>
          <v-col lg="6" md="6" sm="12" xs="12">
            <!-- <DashboardlastMultiStatistics /> -->

            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <DashboardLoginActivities :branch_id="branch_id" />
            </v-card>
            <!-- <v-card class="mt-2" style="height: 350px"> </v-card> -->
          </v-col>
        </v-row>
      </v-col>

      <v-col lg="3" md="3" sm="12" xs="12">
        <v-card class="py-2 mb-2" v-if="branchList.length > 1 && $auth.user.user_type !== 'department'">
          <!-- <v-row>
            <v-col md="12" class="text-center"> 2222 </v-col>
          </v-row> -->
          <v-row class="mt-2">
            <v-col cols="1">
              <label> </label>
            </v-col>
            <v-col cols="3">
              <!-- <label>Branches : </label> -->
            </v-col>

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
        <DashboardRightsideStaticstics :branch_id="branch_id" />

        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2 mt-2" style="height: 443px; overflow: hidden">
              <DashboardAnnouncment :branch_id="branch_id" />
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2 mt-2" style="height: 312px; overflow: hidden">
              <DashboardAttednaceDepartmentWise
                :branch_id="branch_id"
                name="AttendanceDepartmentWise"
              />
            </v-card>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12">
        <SetupFAQSearch />
      </v-col>
    </v-row>
  </div>

  <NoAccess v-else />
</template>

<script>
import DashboardAttendanceChart from "../../components/dashboard2/DashboardAttendanceChartV1.vue";
import DashboardlLastMonthStatistics from "../../components/dashboard2/DashboardlLastMonthStatisticsV1.vue";
import DashboardlTodayStatistics from "../../components/dashboard2/DashboardlTodayStatisticsV1.vue";

import DashboardRealTimeLogTableview from "../../components/dashboard2/DashboardRealTimeLogTableviewV1.vue";
import DashboardRightsideStaticstics from "../../components/dashboard2/DashboardRightsideStaticsticsV1.vue";
import DashboardAnnouncment from "../../components/dashboard2/DashboardAnnouncmentV1.vue";
import DashboardAttendanceHourChart from "../../components/dashboard2/DashboardAttendanceHourChartV1.vue";
import DashboardLoginActivities from "../../components/dashboard2/DashboardLoginActivitiesV1.vue";
import DashboardAttednaceDepartmentWise from "../../components/dashboard2/DashboardAttednaceDepartmentWiseV1.vue";
// import DashboardlastMultiStatistics from "../../components/dashboard2/DashboardlastMultiStatistics.vue";
export default {
  components: {
    DashboardAttendanceChart,
    DashboardlLastMonthStatistics,
    DashboardlTodayStatistics,
    DashboardRealTimeLogTableview,
    DashboardAnnouncment,
    DashboardLoginActivities,
    DashboardAttednaceDepartmentWise,
    DashboardRightsideStaticstics,
    // DashboardlastMultiStatistics,
    DashboardAttendanceHourChart,
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
    let user = this.$auth.user;

    if (
      user.branch_id == 0 &&
      user.is_master == false &&
      user.user_type !== "department"
    ) {
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
    let user = this.$auth.user;

    if (
      user.branch_id == 0 &&
      user.is_master == false &&
      user.user_type !== "department"
    ) {
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
