<template>

  <v-app>

    <v-row>

      <v-col cols="12" md="4">

        <v-card class="ma-5 pa-2">

          <div class="text-center">

            <img width="35%" src="ideaHRMS-final-green.svg" />

          </div>

          <v-text-field outlined dense v-model="url"></v-text-field>

          <v-data-table
            :headers="headers"
            :items="logs"
            :items-per-page="5"
            dense
          ></v-data-table>

        </v-card>

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
        align: "center",
        sortable: false,
        value: "UserID"
      },
      { text: "DeviceID", align: "center", value: "DeviceID" },
      { text: "LogTime", align: "center", value: "LogTime" }
      //   { text: "SerialNumber", value: "SerialNumber" }
    ],

    loading: false,
    snackbar: false,
    response: "",

    url: process.env.SOCKET_ENDPOINT,
    Hash: process.env.Hash,
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
      this.socket = new WebSocket(this.url);
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

