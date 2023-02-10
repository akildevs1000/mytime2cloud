<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col md="2">
        <v-text-field
          v-model="log_payload.user_id"
          label="User Id"
        ></v-text-field>
        <span v-if="errors && errors.user_id" class="text-danger mt-2">{{
          errors.user_id[0]
        }}</span>
      </v-col>
      <v-col md="2">
        <v-text-field
          v-model="log_payload.device_id"
          label="Device Id"
        ></v-text-field>
        <span v-if="errors && errors.device_id" class="text-danger mt-2">{{
          errors.device_id[0]
        }}</span>
      </v-col>
      <v-col md="2">
        <v-menu
          ref="menu"
          v-model="menu"
          :close-on-content-click="false"
          :return-value.sync="date"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              v-model="log_payload.date"
              label="Date"
              readonly
              v-bind="attrs"
              v-on="on"
            ></v-text-field>
          </template>
          <v-date-picker v-model="log_payload.date" no-title scrollable>
            <v-spacer></v-spacer>
            <v-btn text color="primary" @click="menu = false">
              Cancel
            </v-btn>
            <v-btn
              text
              color="primary"
              @click="$refs.menu.save(log_payload.date)"
            >
              OK
            </v-btn>
          </v-date-picker>
        </v-menu>
      </v-col>
      <v-col md="2">
        <v-menu
          ref="time_menu_ref"
          v-model="time_menu"
          :close-on-content-click="false"
          :nudge-right="40"
          :return-value.sync="log_payload.time"
          transition="scale-transition"
          offset-y
          max-width="290px"
          min-width="290px"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              v-model="log_payload.time"
              label="Time In"
              readonly
              v-bind="attrs"
              v-on="on"
            ></v-text-field>
          </template>
          <v-time-picker
            v-if="time_menu"
            v-model="log_payload.time"
            full-width
            format="24hr"
          >
            <v-spacer></v-spacer>
            <v-btn x-small color="primary" @click="time_menu = false">
              Cancel
            </v-btn>
            <v-btn
              x-small
              color="primary"
              @click="$refs.time_menu_ref.save(log_payload.time)"
            >
              OK
            </v-btn>
          </v-time-picker>
        </v-menu>
        <span v-if="errors && errors.time" class="text-danger mt-2">{{
          errors.time[0]
        }}</span>
      </v-col>
      <v-col md="1">
        <v-btn small color="primary" @click="store_schedule">
          Submit
        </v-btn>
      </v-col>
        <!-- <v-col cols="12">
          <v-data-table
            v-model="ids"
            item-key="id"
            :headers="headers"
            :items="data"
            :server-items-length="total"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [50, 100, 500, 1000]
            }"
            class="elevation-1 mt-5"
          ></v-data-table>
        </v-col> -->
    </v-row>
  </div>
</template>

<script>
export default {
  data: () => ({
    Model: "Generate Log",
    endpoint: "attendance_logs",

    date: null,
    menu: false,

    loading: false,
    time_menu: false,

    log_payload: {
      user_id: 3116,
      device_id: "OX-8662022010289",
      date: null,
      time: null
    },
    headers: [
      {
        text: "UserID",
        align: "center",
        sortable: false,
        value: "UserID"
      },
      { text: "DeviceID", align: "center", sortable: false, value: "DeviceID" },
      { text: "LogTime", align: "center", sortable: false, value: "LogTime" }
    ],
    ids: [],

    data: [],
    total: 0,
    options: {},
    errors: [],
    response: "",
    snackbar: false
  }),
  created() {
    this.loading = true;
    let options = {
      params: {
        per_page: this.options.itemsPerPage,
        company_id: this.$auth.user.company.id
      }
    };
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true
    }
  },
  methods: {
    getDataFromApi(url = this.endpoint) {
      this.loading = true;
      const { page, itemsPerPage } = this.options;
      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id
        }
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
        this.loading = false;
      });
    },
    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
      }
    },
    store_schedule() {
      let { user_id, date, time, device_id } = this.log_payload;
      let log_payload = {
        UserID: user_id,
        LogTime: date + " " + time + ":00",
        DeviceID: device_id,
        company_id: this.$auth.user.company.id
      };
      this.loading = true;

      this.$axios
        .post(`/generate_log`, log_payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.getDataFromApi();
            // this.processAttendance();
            this.snackbar = true;
            this.response = "Log generate successfully";
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    processAttendance() {
       this.$axios.get(`/ProcessAttendance`)
        .then(({ data }) => {});
    },
  }
};
</script>
