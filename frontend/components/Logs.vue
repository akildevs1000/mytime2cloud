<template>
  <div v-if="can(`logs_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row class="pt-2 mt-5">
      <v-col cols="12" sm="8" md="2">
        <v-menu
          ref="from_menu"
          v-model="from_menu"
          :close-on-content-click="false"
          :return-value.sync="payload.from_date"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ on, attrs }">
            <div class="mb-1">From Date</div>
            <v-text-field
              :hide-details="!payload.from_date"
              outlined
              dense
              @input="searchIt"
              v-model="payload.from_date"
              readonly
              v-bind="attrs"
              v-on="on"
              placeholder="Date"
            ></v-text-field>
          </template>
          <v-date-picker v-model="payload.from_date" no-title scrollable>
            <v-spacer></v-spacer>
            <v-btn class="blue-grey" small dark @click="from_menu = false">
              Cancel
            </v-btn>
            <v-btn
              class="blue-grey darken-3"
              small
              dark
              @click="$refs.from_menu.save(payload.from_date)"
            >
              OK
            </v-btn>
          </v-date-picker>
        </v-menu>
      </v-col>
      <v-col cols="12" sm="8" md="2">
        <div class="mb-1">To Date</div>
        <v-menu
          ref="to_menu"
          v-model="to_menu"
          :close-on-content-click="false"
          :return-value.sync="payload.to_date"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              :hide-details="!payload.to_date"
              outlined
              dense
              @input="searchIt"
              v-model="payload.to_date"
              readonly
              v-bind="attrs"
              v-on="on"
              placeholder="Date"
            ></v-text-field>
          </template>
          <v-date-picker v-model="payload.to_date" no-title scrollable>
            <v-spacer></v-spacer>
            <v-btn class="blue-grey" small dark @click="to_menu = false">
              Cancel
            </v-btn>
            <v-btn
              class="blue-grey darken-3"
              small
              dark
              @click="$refs.to_menu.save(payload.to_date)"
            >
              OK
            </v-btn>
          </v-date-picker>
        </v-menu>
      </v-col>
      <v-col cols="12" sm="6" md="2">
        <div class="mb-1">User ID</div>
        <v-text-field
          @input="searchIt"
          v-model="payload.UserID"
          outlined
          dense
          placeholder="Search..."
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6" md="2">
        <div class="mb-1">Device ID</div>
        <v-autocomplete
          outlined
          dense
          @change="searchIt"
          placeholder="Search..."
          v-model="payload.DeviceID"
          :items="devices"
          item-text="device_id"
          item-value="device_id"
        >
        </v-autocomplete>
      </v-col>
    </v-row>
    <v-row class="mt-5">
      <v-col cols="12">
        <v-data-table
          v-model="ids"
          item-key="id"
          :headers="headers"
          :items="data"
          :server-items-length="total"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [50, 100, 500, 1000],
          }"
        ></v-data-table>
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    Model: "Log",
    endpoint: "attendance_logs",

    from_date: null,
    from_menu: false,
    to_date: null,
    to_menu: false,

    payload: {},

    loading: true,

    date: null,
    menu: false,

    loading: false,
    time_menu: false,

    log_payload: {
      user_id: 41,
      device_id: "OX-8862021010100",
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
      { text: "Device Name", align: "center", sortable: false, value: "device.name" },
      { text: "LogTime", align: "center", sortable: false, value: "LogTime" },
    ],
    ids: [],

    data: [],
    devices: [],
    total: 0,
    options: {},
    payloadOptions: {},
    errors: [],
    response: "",
    snackbar: false,
  }),
  created() {
    this.loading = false;
    this.payload.from_date = this.getDate();
    this.payload.to_date = this.getDate();
    this.getDeviceList();
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    getDeviceList() {
      let payload = {
        params: {
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(`/device_list`, payload).then(({ data }) => {
        this.devices = data;
      });
    },
    getDate() {
      const date = new Date();
      const year = date.getFullYear();
      const month = (date.getMonth() + 1).toString().padStart(2, "0");
      const day = date.getDate().toString().padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },

    getDataFromApi(url = this.endpoint) {
      this.payloadOptions = {
        params: {
          per_page: this.options.itemsPerPage,
          company_id: this.$auth.user.company.id,
          ...this.payload,
        },
      };
      this.loading = true;
      this.$axios
        .get(`${url}?page=${this.options.page}`, this.payloadOptions)
        .then(({ data }) => {
          this.data = data.data;
          this.total = data.total;
          this.loading = false;
        });
    },
    searchIt() {
      let UserID = this.payload.UserID;
      let DeviceID = this.payload.DeviceID;

      if (UserID && UserID.length == 0 && DeviceID && DeviceID.length == 0) {
        this.getDataFromApi();
      } else {
        this.getDataFromApi(
          `${this.endpoint}/search/${this.$auth.user.company.id}`
        );
      }
    },
  },
};
</script>
