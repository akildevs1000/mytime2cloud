<template>
  <div v-if="can(`visitor_access`)">
    <v-dialog v-model="dialogInformation" width="auto">
      <v-card>
        <v-card-title class="popup_background">
          <span dense> Visitors Requests - {{ statisticsFilter }} </span>
          <v-spacer></v-spacer>
          <v-icon dark @click="dialogInformation = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <VisitorRequestsList
            :key="keyId"
            :isDashboard="true"
            :statsFilterValue="statisticsFilter"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <div v-if="!loading">
      <v-dialog
        persistent
        v-model="dialogGeneralreport"
        :fullscreen="false"
        max-width="1600px"
      >
        <iframe
          v-if="iframeDisplay"
          :src="iframeUrl"
          frameborder="0"
          style="width: 100%; height: 600px"
        ></iframe>
      </v-dialog>
      <v-row class="pb-0">
        <v-col cols="2">
          <v-card class="pa-2" style="height: 150px; overflow: hidden">
            <v-row>
              <v-col cols="7">
                <h3>Checked In</h3>

                <v-col class="text-left pa-0" cols="12">
                  <div class="bold" style="font-size: 40px; color: #33691e">
                    {{ items.visitorCounts[1].value }}
                  </div>
                </v-col>
              </v-col>
              <v-col cols="5" class="text-right">
                <img
                  src="../../static/checked-in2.png"
                  style="width: 100px; padding: 14%"
                />
              </v-col>
            </v-row>
          </v-card>
        </v-col>
        <v-col cols="2">
          <v-card class="pa-2" style="height: 150px; overflow: hidden">
            <v-row>
              <v-col cols="7">
                <h3>Checked Out</h3>

                <v-col class="text-left pa-0" cols="12">
                  <div class="bold" style="font-size: 40px; color: #b91e20">
                    {{ items.visitorCounts[2].value }}
                  </div>
                </v-col>
              </v-col>
              <v-col cols="5" class="text-right">
                <img
                  src="../../static/checked-out.png"
                  style="width: 100px; padding: 14%"
                />
              </v-col>
            </v-row>
          </v-card>
        </v-col>

        <v-col cols="8">
          <v-card class="pa-2" style="height: 150px; overflow: hidden">
            <h3>Statistics</h3>
            <v-row class="pt-10">
              <v-col cols="2" class="card1 rounded-5 text-left">
                <v-row>
                  <v-col cols="3" class="text-center">
                    <v-avatar size="45" color="black" class="text-center">
                      <v-icon size="35" class="pa-2" style="color: #fff"
                        >mdi-account-details</v-icon
                      >
                    </v-avatar>
                  </v-col>
                  <v-col class="text-left pa-2" cols="9">
                    <h3>Total Visitors</h3>
                    <div
                      class="bold"
                      style="font-size: 30px; margin-top: -5px; color: black"
                    >
                      {{ items.statusCounts[0].value }}
                    </div>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col cols="2" class="card1 rounded-5 text-left">
                <v-row>
                  <v-col cols="3" class="text-center">
                    <v-avatar size="45" color="#033F9B" class="text-center">
                      <v-icon size="35" class="pa-2" style="color: #fff"
                        >mdi-account-supervisor</v-icon
                      >
                    </v-avatar>
                  </v-col>
                  <v-col class="text-left pa-2" cols="9">
                    <h3>Expecting</h3>
                    <div
                      class="bold"
                      style="font-size: 30px; margin-top: -5px; color: #033f9b"
                    >
                      {{ items.visitorCounts[0].value }}
                    </div>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col cols="2" class="card1 rounded-5 text-left">
                <v-row>
                  <v-col cols="3" class="text-center">
                    <v-avatar size="45" color="#ff0000" class="text-center">
                      <v-icon size="35" class="pa-2" style="color: #fff"
                        >mdi mdi-timer-sand-full</v-icon
                      >
                    </v-avatar>
                  </v-col>
                  <v-col class="text-left pa-2" cols="9">
                    <h3>Over Stay</h3>
                    <div
                      class="bold"
                      style="font-size: 30px; margin-top: -5px; color: #ff0000"
                    >
                      {{ items.visitorCounts[3].value }}
                    </div>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>

              <v-col cols="2" class="card1 rounded-5 text-left">
                <v-row>
                  <v-col cols="3" class="text-center">
                    <v-avatar size="45" color="green" class="text-center">
                      <v-icon size="35" class="pa-2" style="color: #fff"
                        >mdi-account-multiple-check</v-icon
                      >
                    </v-avatar>
                  </v-col>
                  <v-col class="text-left pa-2" cols="9">
                    <h3>Approved</h3>
                    <div
                      class="bold"
                      style="font-size: 30px; margin-top: -5px; color: green"
                    >
                      {{ items.statusCounts[1].value }}
                    </div>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col cols="2" class="card1 rounded-5 text-left">
                <v-row>
                  <v-col cols="3" class="text-center">
                    <v-avatar size="45" color="red" class="text-center">
                      <v-icon size="35" class="pa-2" style="color: #fff"
                        >mdi-account-multiple-remove</v-icon
                      >
                    </v-avatar>
                  </v-col>
                  <v-col class="text-left pa-2" cols="9">
                    <h3>Rejected</h3>
                    <div
                      class="bold"
                      style="font-size: 30px; margin-top: -5px; color: red"
                    >
                      {{ items.statusCounts[3].value }}
                    </div>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col cols="2" class="card1 rounded-5 text-left">
                <v-row>
                  <v-col cols="3" class="text-center">
                    <v-avatar size="45" color="#9b9b00" class="text-center">
                      <v-icon size="35" class="pa-2" style="color: #fff"
                        >mdi-account-question</v-icon
                      >
                    </v-avatar>
                  </v-col>
                  <v-col class="text-left pa-2" cols="9">
                    <h3>Pending</h3>
                    <div
                      class="bold"
                      style="font-size: 30px; margin-top: -5px; color: #9b9b00"
                    >
                      {{ items.statusCounts[2].value }}
                    </div>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-col md="12">
          <v-card class="pa-2" style="height: 354px; overflow: hidden">
            <v-row background fill>
              <v-col
                lg="3"
                md="3"
                sm="3"
                xs="3"
                class="d-xs-flex"
                style="flex: auto"
              >
                <VisitorPieChart :items="items"></VisitorPieChart>
              </v-col>
              <!-- <v-col
                  lg="6"
                  md="6"
                  sm="6"
                  xs="6"
                  class="d-xs-flex pa-2 pt-5"
                  style="border-left: 1px solid #ddd"
                >
                  <v-row class="pa-10">
                    <v-col
                      cols="3"
                      class="card1 rounded-5"
                      v-for="(i, index) in items.visitorCounts"
                      :key="'v' + index"
                    >
                      <v-row @click="viewPopupInfo(i.title)">
                        <v-col cols="4" class="text-end">
                          <v-avatar size="30" :color="i.color">
                            <v-icon
                              size="20"
                              class="pa-2"
                              style="color: #fff"
                              >{{ i.icon }}</v-icon
                            >
                          </v-avatar>
                        </v-col>
                        <v-col class="text-left pa-0">
                          <div class="bold" style="font-size: 40px">
                            {{ i.value }}
                          </div>
                          {{ i.title }}
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>

                  <v-row class="pa-10 pt-5">
                    <v-col
                      cols="3"
                      class="card1 rounded-5"
                      v-for="(i, index) in items.statusCounts"
                      :key="'v' + index"
                    >
                      <v-row @click="viewPopupInfo(i.title)">
                        <v-col cols="4" class="text-end">
                          <v-avatar size="30" :color="i.color">
                            <v-icon
                              size="20"
                              class="pa-1"
                              style="color: #fff"
                              >{{ i.icon }}</v-icon
                            >
                          </v-avatar>
                        </v-col>
                        <v-col class="text-left pa-0">
                          <div class="bold" style="font-size: 40px">
                            {{ i.value }}
                          </div>
                          {{ i.title }}
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>
                </v-col> -->

              <v-col
                lg="9"
                md="9"
                sm="9"
                xs="9"
                class="d-xs-flex"
                style="border-left: 1px solid #ddd"
              >
                <VisitorHourChart
                  :name="'visitor'"
                  :branch_id="null"
                  :height="300"
                ></VisitorHourChart>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col lg="12" md="12" sm="12" xs="12">
          <v-card
            class="py-2"
            style="height: 600px; overflow-x: hidden; overflow-y: scroll"
          >
            <VisitorLogs />
            <!-- <VisitorReport></VisitorReport> -->
          </v-card>
        </v-col>
      </v-row>
      <!-- <v-row>
          <v-col lg="12" md="12" sm="12" xs="12">
            <v-card class="py-2" style="overflow: hidden">
              <VisitorHourChart
                :name="'visitor'"
                :branch_id="null"
                :height="300"
              ></VisitorHourChart>
            </v-card>
          </v-col>
        </v-row> -->
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
import VisitorList from "../../components/Visitor/VisitorRequestsList.vue";
import VisitorReport from "../../components/Visitor/VisitorReport.vue";
import VisitorLogs from "../../components/Visitor/VisitorLogs.vue";
import VisitorHourChart from "../../components/Visitor/DashboardVisitorHourChart.vue";
import VisitorPieChart from "../../components/Visitor/DashboardVisitorPieChart.vue";
import VisitorRequestsList from "../../components/Visitor/VisitorRequestsList.vue";

export default {
  components: {
    VisitorList,
    VisitorPieChart,
    VisitorHourChart,
    VisitorReport,
    VisitorLogs,
    VisitorRequestsList,
  },

  data() {
    return {
      keyId: 1,
      counter: 1,
      loading: false,
      dialogGeneralreport: false,
      iframeDisplay: false,
      iframeUrl: "",
      items: [],
      filterTitle: "",
      branch_id: null,
      dialogInformation: false,
      statisticsFilter: "",
    };
  },
  created() {
    this.initialize();
  },
  mounted() {},
  computed: {
    getCurrentDate() {
      // Get the current date
      const date = new Date();

      // Get the year, month, and day from the date object
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");

      return `${year}-${month}-${day}`;
    },
  },
  filters: {
    get_decimal_value: function (value) {
      if (!value) return "";
      return (Math.round(value * 100) / 100).toFixed(2);
    },
    get_comma_seperator: function (x) {
      if (!x) return "";
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    viewPopupInfo(status) {
      this.statisticsFilter = status;

      console.log("this.statisticsFilter", this.statisticsFilter);
      this.keyId++;
      this.dialogInformation = true;
    },
    changeBranch1(branch_id) {
      console.log("branch_id", branch_id);
      this.branch_id = branch_id;

      // this.initialize();
    },
    filterStatus(status) {
      this.filterTitle = status;
      //this.counter++;
    },

    openDialog(announcement) {
      this.dialogData = announcement;
      this.dialog = true;
    },

    updateLink(url) {
      if (
        this.$axios.defaults.baseURL !=
        "https://stagingbackend.ideahrms.com/api"
      ) {
        url = url.replace(
          "https://stagingbackend.ideahrms.com/api",
          this.$axios.defaults.baseURL
        );
      }

      return url;
    },
    closeDialogGeneralreport() {
      this.iframeDisplay = false;
      this.dialogGeneralreport = false;
      //this.iframeUrl = "#";
    },
    showDialogGeneralreport(iframeUrl) {
      this.iframeDisplay = false;
      this.iframeUrl = this.updateLink(iframeUrl);
      this.dialogGeneralreport = true;

      this.iframeDisplay = true;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    initialize() {
      this.loading = true;
      let options = {
        company_id: this.$auth.user.company_id,
        branch_id: this.branch_id > 0 ? this.branch_id : null,
      };

      this.$axios.get(`visitor-count`, { params: options }).then(({ data }) => {
        this.items = data;

        this.loading = false;

        // if (this.items.length > 0) {
        //   this.loading = false;
        // }
      });
    },
  },
};
</script>
<style scoped src="@/assets/dashboard.css"></style>
