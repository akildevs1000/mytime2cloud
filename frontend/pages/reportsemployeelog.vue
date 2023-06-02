<template>
  <div>
    <v-skeleton-loader v-if="logs && !logs.length" type="card" />
    <div v-else>
      <v-toolbar flat>
        <h5>
          <b>
            Employees data(Recent Logs)
          </b>
        </h5>
        <v-spacer />
      </v-toolbar>

      <table>
        <tr>
          <th v-for="(item, index) in headers" :key="index">
            <span v-html="item.text"></span>
          </th>
        </tr>
        <tr v-for="(item, index) in logs" :key="index">
          <td class="ps-3">
            <b>{{ ++index }}</b>
          </td>
          <td>
            <v-img
              class="gg"
              viewBox="0 0 100 100"
              style="border-radius: 50%;  height: 80px; max-width: 80px !important"
              :src="
                (item.employee && item.employee.profile_picture) ||
                  '/no-profile-image.jpg'
              "
            ></v-img>
          </td>
          <td>{{ item.employee && item.employee.first_name }}</td>
          <td>EID: {{ item.UserID }}</td>
          <td>
            <span>{{ item && item.time }} </span><small>Time</small>
          </td>
          <td>
            <span>{{ (item.device && item.device.short_name) || "---" }}</span
            ><small>Device</small>
          </td>
        </tr>
      </table>

      <!-- <v-slide-group class="px-4 pb-3" active-class="success" show-arrows>
        <div></div>
        <v-slide-item v-for="(item, index) in logs" :key="index">
          <div class="card mx-2 my-2 w-25">
            <div class="banner">
              <v-img
                class="gg"
                viewBox="0 0 100 100"
                style="border-radius: 50%;  height: 80px; max-width: 80px !important"
                :src="
                  (item.employee && item.employee.profile_picture) ||
                    '/no-profile-image.jpg'
                "
              ></v-img>
            </div>
            <div class="menu">
              <div class="opener"></div>
            </div>
            <h2 class="text-center pa-1" style="font-size:15px">
              {{ item.employee && item.employee.first_name }}
            </h2>
            <div class="title" style="font-size:12px !important">
              EID: {{ item.UserID }}
            </div>
            <div class="title" style="font-size:12px !important"></div>
            <div class="actions">
              <div class="follow-info">
                <h2>
                  <a href="#"
                    ><span>{{ item && item.time }} </span><small>Time</small></a
                  >
                </h2>
                <h2>
                  <a href="#"
                    ><span>{{
                      (item.device && item.device.short_name) || "---"
                    }}</span
                    ><small>Device</small></a
                  >
                </h2>
              </div>
            </div>
          </div>
        </v-slide-item>
      </v-slide-group>-->
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    Model: "Device",
    pagination: {
      current: 1,
      total: 0,
      per_page: 10
    },
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    data: [],
    loading: false,
    total: 0,
    headers: [
      { text: "&nbsp #" },
      { text: "Pic" },
      { text: "Name" },
      { text: "Eid" },
      { text: "Last Active" },
      { text: "Device name" }
    ],
    editedIndex: -1,
    response: "",
    errors: [],

    number_of_records: 10,
    logs: [],
    url: process.env.SOCKET_ENDPOINT,
    socket: null
  }),
  // data() {
  //   return {
  //     number_of_records: 10,
  //     logs: [],
  //     url: process.env.SOCKET_ENDPOINT,
  //     socket: null
  //   };
  // },
  mounted() {
    this.socketConnection();

    this.getRecords();
  },
  created() {
    // this.getRecords();
  },
  methods: {
    getRecords() {
      this.$axios
        .get(
          `device/getLastRecordsByCount/${this.$auth.user.company.id}/${this.number_of_records}`
        )
        .then(res => {
          this.logs = res.data;
        });
    },
    getShortName(item) {
      if (!item) {
        return false;
      }
      return item
        .split(" ")
        .slice(0, 2)
        .join(" ");
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);

      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data);
        if (json.Status == 200 && json.Data.UserCode !== 0) {
          this.getDetails(json.Data);
        }
      };
    },
    getDetails(item) {
      item.company_id = this.$auth.user.company.id;

      this.$axios.post(`/device/details`, item).then(({ data }) => {
        if (
          data.device &&
          this.$auth.user &&
          data.device.company_id == this.$auth.user.company.id
        ) {
          this.logs.unshift(data);
        }
      });
    }
  }
};
</script>

<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  text-align: left;
  padding: 7px;
}

tr:nth-child(even) {
  background-color: #e9e9e9;
}
</style>
