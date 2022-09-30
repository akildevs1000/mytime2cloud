<template>
  <div>
    <v-sheet class="mx-auto" elevation="8" max-width="1050">
      <v-slide-group class="pa-4" center-active show-arrows>
        <v-slide-item v-for="(log, index) in logs" :key="index" v-slot="{ toggle }">
          <v-card class="ma-4 primary " height="150" width="150" @click="toggle">
            <v-row class="fill-height" align="center" justify="center">
              <v-avatar size="70">
                <img src="https://cdn.vuetifyjs.com/images/john.jpg" alt="John">
              </v-avatar>
            </v-row>
            <div class="text-center">{{log.UserID}}</div>


          </v-card>
        </v-slide-item>
      </v-slide-group>
    </v-sheet>
  </div>
</template>

<script>
export default {
  data: () => ({
    // logo : '/LTLOGO.png',
    // logo: "https://smarthr.dreamguystech.com/orange/assets/img/logo2.png",

    headers: [
      // {
      //   text: "Image",
      //   align: "center",
      //   sortable: false,
      //   value: "RecordImage"
      // },
      {
        text: "UserID",
        align: "center",
        sortable: false,
        value: "UserCode"
      },
      { text: "DeviceID", align: "center", value: "DeviceID" },
      { text: "LogTime", align: "center", value: "RecordDate" },
      { text: "SerialNumber", value: "RecordNumber" }
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
        if (json.Status == 200 && json.Data.UserCode !== 0) {
          this.getDetails(json.Data);
        }
      };
    },
    getDetails(item) {
      this.$axios.get(`/device/${item.DeviceID}/details`).then(({ data }) => {
        if (data.company_id == this.$auth.user.company.id) {
          this.logs.unshift(item);
        }
      });
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
