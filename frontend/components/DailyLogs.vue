<template>
  <div v-if="logs && logs.length == 0" style="min-height: 400px">
    <div style="text-align: center; color: red">{{ emptyLogmessage }}</div>
  </div>
  <v-row v-else>
    <v-col xs="12" sm="12" cols="12" md="12" lg="12" xl="12">
      <v-card style="height: 500px">
        <v-toolbar color="primary" dark flat>
          <v-toolbar-title>Recent Logs</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-tooltip v-if="logs && logs.length > 0" top color="primary">
            <template v-slot:activator="{ on, attrs }">
              <v-btn dense class="mr-2 px-0" x-small :ripple="false" text v-bind="attrs" v-on="on">
                <v-icon color="white" class="ml-2" @click="goToemployeelog()" dark>mdi-grid</v-icon>
              </v-btn>
            </template>
            <span>Logs List</span>
          </v-tooltip>

          <v-select @change="getRecords" v-model="number_of_records" :hide-details="true" outlined dense
            placeholder="Select Number of Records" :items="[10, 20, 50, 100]"
            style="max-width: 200px !important"></v-select>

        </v-toolbar>

        <!-- <v-toolbar flat class="mb-15 pt-1">
          <h5>
            Recent Logs
            <v-icon v-if="logs && logs.length > 0" @click="goToemployeelog()">mdi-grid</v-icon>
          </h5>
          <v-spacer />

          <v-select @change="getRecords" v-model="number_of_records" :hide-details="true" outlined dense
            placeholder="Select Number of Records" :items="[10, 20, 50, 100]"
            style="max-width: 200px !important"></v-select>
        </v-toolbar> -->
        <v-slide-group class="px-4 pb-3 mb-5" active-class="success" show-arrows>
          <div></div>

          <v-slide-item v-for="(item, index) in logs" :key="index">
            <v-card style="border-radius: 2rem !important;  " class="custom-card mx-2 my-2">
              <div style="size: cover" class="d-flex justify-center primary banner pt-5">
                <v-img class="image-box" :src="(item.employee && item.employee.profile_picture) ||
                  '/no-profile-image.jpg'
                  "></v-img>
              </div>
              <div class="menu">
                <div class="opener"></div>
              </div>
              <h2 class="text-center pa-1" style="font-size: 15px">
                {{ item.employee && item.employee.display_name }}
              </h2>
              <div class="title" style="font-size: 12px !important">
                EID: {{ item.UserID }}
              </div>
              <div class="title" style="font-size: 12px !important"></div>
              <div class="actions">
                <div class="follow-info">
                  <h2>
                    <a href="#"><span>{{ item && item.time }} </span><small>Time</small></a>
                  </h2>
                  <h2>
                    <a href="#"><span>{{
                      (item.device && item.device.short_name) || "---"
                    }}</span><small>Device</small></a>
                  </h2>

                </div>
                <!-- <div class="title" style="font-size: 9px !important">{{ item.LogTime }}</div> -->

              </div>
            </v-card>
          </v-slide-item>
        </v-slide-group>
      </v-card>
    </v-col>
    <!-- <v-col xs="12" sm="12" cols="12" md="6" lg="6" xl="6">
      <v-card style="height: 500px">
        <PIE :items="items" />
      </v-card>
    </v-col> -->
  </v-row>
</template>

<script>
export default {
  data() {
    return {
      items: [],
      emptyLogmessage: "",
      number_of_records: 10,
      logs: [],
      url: process.env.SOCKET_ENDPOINT,
      socket: null,
    };
  },
  mounted() {
    this.socketConnection();

    this.getRecords();
  },

  created() {
    this.initialize();
    // this.getRecords();
  },
  methods: {
    initialize() {
      let options = {
        company_id: this.$auth.user.company_id,
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        this.items = data;

        this.total_employees_count_api = data[0].total_employees_count;

        if (this.items.length > 0) {
          this.loading = false;
        }
      });
    },
    getRecords() {
      this.$axios
        .get(
          `device/getLastRecordsByCount/${this.$auth.user.company_id}/${this.number_of_records}`
        )
        .then((res) => {
          this.logs = res.data;
          // console.log("this.logs", this.logs.length);
          if (this.logs.length == 0) {
            this.emptyLogmessage = "No Log records are available";
          }
        });
    },
    goToemployeelog() {
      this.$router.push("/devicelogs");
    },
    getShortName(item) {
      if (!item) {
        return false;
      }
      return item.split(" ").slice(0, 2).join(" ");
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);

      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data).Data;
        if (json && json.UserCode > 0) {
          this.getDetails(json);
        }
      };
    },
    getDetails(item) {
      item.company_id = this.$auth.user.company_id;

      this.$axios.post(`/device/details`, item).then(({ data }) => {
        if (data.device) {
          if (data.device.company_id == this.$auth.user.company_id) {
            data.employee.profile_picture =
              "data:image;base64," + item.RecordImage;
            this.logs.unshift(data);
          }
        }
      });
    },
  },
};
</script>

<style scoped src="@/assets/dashboard_style.css"></style>
