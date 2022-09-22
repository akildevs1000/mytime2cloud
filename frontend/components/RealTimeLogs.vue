<template>
  <v-app>
    <v-row class="pa-5">
      <v-col>
        <v-toolbar dark flat class="primary">
          <h3 class="pt-5 pb-5">Logs</h3>
        </v-toolbar>
        <v-data-table
          :headers="headers"
          :items="logs"
          :items-per-page="5"
          class="elevation-3"
          dense
        ></v-data-table>
        <!-- <v-simple-table dense>
          <template v-slot:default>
            <thead>
              <tr>
                <th><h3 class="pb-5">Logs</h3></th>
              </tr>
              <tr>
                <th class="text-left">
                  UserID
                </th>
                <th class="text-left">
                  DeviceID
                </th>
                <th class="text-left">
                  LogTime
                </th>
                <th class="text-left">
                  SerialNumber
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in logs" :key="index">
                <td>{{ item.UserID }}</td>
                <td>{{ item.DeviceID }}</td>
                <td>{{ item.LogTime }}</td>
                <td>{{ item.SerialNumber }}</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table> -->
      </v-col>
    </v-row>
  </v-app>
</template>

<script>
export default {
  layout: "login",
  auth: false,
  data: () => ({
    // logo : '/LTLOGO.png',
    // logo: "https://smarthr.dreamguystech.com/orange/assets/img/logo2.png",

    headers: [
      {
        text: "UserID",
        align: "start",
        sortable: false,
        value: "UserID"
      },
      { text: "DeviceID", value: "DeviceID" },
      { text: "LogTime", value: "LogTime" }
      //   { text: "SerialNumber", value: "SerialNumber" }
    ],

    loading: false,
    snackbar: false,
    response: "",

    // url: "ws://localhost:2222/WebSocket",
    Hash: "$2y$10$RQ0d7Yo1ad/aTm2pEx3QvuGatA6t0qqH76m7VXYGkNjzVYqNGAQ.K",
    logs: [],
    socket: null,

    msg: "",
    errors: []
  }),
  mounted() {
    this.socketConnection();
  },
 
  methods: {
    socketConnection() {
      console.log(process.env.SOCKET_ENDPOINT);
      this.socket = new WebSocket(process.env.SOCKET_ENDPOINT);
      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data);
        if (json.Status == 200) {
          let payload = {
            UserID: json.Data.UserCode,
            DeviceID: json.Data.DeviceID,
            LogTime: json.Data.RecordDate,
            Hash: this.Hash
          };
          this.logs.push(payload);  
          this.store(payload);
        }
      };
    },
    store(payload) {
      let config = {
        headers: {
          Authorization: "Bearer " + this.Hash
        }
      };

      this.$axios.post("generate_log", payload, config).then(res => {
        console.log(res);
      });
    }
  }
};
</script>
<style scoped>
@media (min-width: 768px) {
  .gradient-form {
    height: 100vh !important;
  }
}
@media (min-width: 769px) {
  .primary {
    border-top-right-radius: 0.3rem;
    border-bottom-right-radius: 0.3rem;
  }
}
</style>
