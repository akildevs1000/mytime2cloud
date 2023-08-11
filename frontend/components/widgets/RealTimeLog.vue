<template>
  <v-card class="mb-5 rounded-md" elevation="1">
    <v-toolbar class="rounded-md" color="background" dense flat dark>
      <v-toolbar-title><span> RealTime Log List</span></v-toolbar-title>
      <v-tooltip top color="primary">
        <template v-slot:activator="{ on, attrs }">
          <v-btn
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            v-bind="attrs"
            v-on="on"
          >
            <v-icon color="white" class="ml-2" @click="getDataFromApi()" dark
              >mdi mdi-reload</v-icon
            >
          </v-btn>
        </template>
        <span>Reload</span>
      </v-tooltip>
      <v-spacer></v-spacer>
      <v-tooltip top color="primary">
        <template v-slot:activator="{ on, attrs }">
          <v-btn
            to="/devicelogs"
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            v-bind="attrs"
            v-on="on"
          >
            <v-icon color="white" class="ml-2" dark>mdi mdi-eye-outline</v-icon>
          </v-btn>
        </template>
        <span>Raw Log List</span>
      </v-tooltip>
    </v-toolbar>
    <div class="center-both" style="min-height: 300px">
      <ComonPreloader icon="face-scan" v-if="loading" />
      <div v-else-if="!logs.length">No record found</div>
      <v-slide-group v-else center-active multiple show-arrows>
        <v-slide-item class="ma-5" v-for="(item, i) in logs" :key="i">
          <div
            style="
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
            "
          >
            <v-img
              :src="
                (item.employee && item.employee.profile_picture) ||
                '/no-profile-image.jpg'
              "
              lazy-src="no-profile-image.jpg"
              style="
                max-width: 150px;
                max-height: 150px;
                object-fit: contain;
                border-radius: 50%;
              "
            />
            <div style="margin-top: 10px">
              {{ item.employee && item.employee.first_name }}
            </div>
            <div>
              <span>{{ item && item.time }}</span>
            </div>
          </div>
        </v-slide-item>
      </v-slide-group>
    </div>
  </v-card>
</template>
<script>
export default {
  data() {
    return {
      loading: false,
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
  },

  created() {
    this.getDataFromApi();
  },
  methods: {
    getDataFromApi() {
      this.loading = true;
      this.$axios
        .get(
          `device/getLastRecordsByCount/${this.$auth.user.company.id}/${this.number_of_records}`
        )
        .then(({ data }) => {
          this.loading = false;
          this.logs = data;
        });
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
      item.company_id = this.$auth.user.company.id;

      this.$axios.post(`/device/details`, item).then(({ data }) => {
        if (data.device) {
          if (data.device.company_id == this.$auth.user.company.id) {
            data.employee.profile_picture =
              "data:image;base64," + item.RecordImage;
            this.logs.unshift(data);
            console.log(data);
          }
        }
      });
    },
  },
};
</script>
<style scoped>
.center-both {
  height: 31vh; /* Adjust the height as needed */
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
