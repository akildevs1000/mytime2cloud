<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col md="6">
        <v-text-field
          v-model="log_payload.user_id"
          label="Employee Device Id"
        ></v-text-field>
        <span v-if="errors && errors.user_id" class="text-danger mt-2">{{
          errors.user_id[0]
        }}</span>
      </v-col>
      <v-col md="6">
        <!-- <v-text-field
          v-model="log_payload.device_id"
          label="Device Id"
        ></v-text-field> -->
        <v-autocomplete
          label="Select Device"
          v-model="log_payload.device_id"
          :items="devices"
          item-text="name"
          item-value="device_id"
        >
        </v-autocomplete>
        <span v-if="errors && errors.device_id" class="text-danger mt-2">{{
          errors.device_id[0]
        }}</span>
        <span v-if="errors && errors.device_id" class="text-danger mt-2">{{
          errors.device_id[0]
        }}</span>
      </v-col>
      <v-col md="6">
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
            <v-btn text color="primary" @click="menu = false"> Cancel </v-btn>
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
      <v-col md="6">
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
      <v-col cols="12">
        <v-textarea
          filled
          label="Reason"
          v-model="reason"
          auto-grow
          required
        ></v-textarea>
        <span v-if="errors && errors.reason" class="error--text">
          {{ errors.reason[0] }}
        </span>
      </v-col>
      <v-col col="2" class="text-end">
        <v-btn small color="primary" @click="store_schedule"> Submit </v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["endpoint"],
  data: () => ({
    Model: "Generate Log",

    date: null,
    menu: false,

    loading: false,
    time_menu: false,

    reason: null,

    log_payload: {
      user_id: null,
      device_id: "",
      date: null,
      time: null,
    },
    headers: [
      {
        text: "UserID",
        align: "center",
        sortable: false,
        value: "UserID",
      },
      { text: "DeviceID", align: "center", sortable: false, value: "DeviceID" },
      { text: "LogTime", align: "center", sortable: false, value: "LogTime" },
    ],
    ids: [],
    devices: [],
    data: [],
    total: 0,
    options: {},
    errors: [],
    response: "",
    snackbar: false,
  }),
  created() {
    this.loading = true;
    let options = {
      params: {
        per_page: this.options.itemsPerPage,
        company_id: this.$auth.user.company_id,
      },
    };

    this.getDeviceList();
  },
  methods: {
    getDeviceList() {
      this.$axios
        .get(`/device_list?company_id=${this.$auth.user.company_id}`)
        .then(({ data }) => {
          this.devices = data;
        });
    },
    store_schedule() {
      let { user_id, date, time, device_id } = this.log_payload;
      let log_payload = {
        UserID: user_id,
        LogTime: date + " " + time,
        DeviceID: device_id,
        company_id: this.$auth.user.company_id,
      };
      this.loading = true;

      if (!user_id || !date || !device_id || !time) {
        alert("Please enter required fields");
        return;
      }

      this.$axios
        .post(`/generate_log`, log_payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.render_report();

            this.snackbar = true;
            this.response = data.message;
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    render_report() {
      let payload = {
        params: {
          date: this.log_payload.date,
          UserID: this.log_payload.user_id,
          company_id: this.$auth.user.company_id,
          user_id: this.$auth.user.id,
          updated_by : this.$auth.user.id,
          reason: this.reason,
        },
      };
      this.$axios
        .get(this.endpoint, payload)
        .then(({ data }) => {
          this.loading = false;
          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
