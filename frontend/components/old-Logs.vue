<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-toolbar dense class="primary" dark>
      <div>
        Today Logs
      </div>
      <v-spacer />
      <v-tooltip left>
        <template v-slot:activator="{ on, attrs }">
          <v-icon dark v-bind="attrs" v-on="on" @click="fetch_logs"
            >mdi-history</v-icon
          >
        </template>
        <span>Refresh</span>
      </v-tooltip>
    </v-toolbar>
    <v-data-table
      :headers="headers"
      :items="data"
      :server-items-length="total"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [5, 10, 15]
      }"
      class="elevation-1"
    >
      <template v-slot:item.status="{ item }">
        <v-icon v-if="item.status == 'A'" color="error">mdi-close</v-icon>

        <v-icon v-else-if="item.status == 'P'" color="success darken-1"
          >mdi-check</v-icon
        >
        <v-icon v-else-if="item.status == 'H'" color="grey darken-1"
          >mdi-check</v-icon
        >
        <span v-else>{{ item.status }}</span>
      </template>
      <template v-slot:item.shift="{ item }">
        <v-tooltip v-if="item && item.shift" top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <div class="primary--text" v-bind="attrs" v-on="on">
              {{ (item.shift && item.shift.name) || "---" }}
            </div>
          </template>
          <div v-for="(iterable, index) in item.shift_type" :key="index">
            <span v-if="index !== 'id'">
              Shift Type: {{ caps(iterable) || "---" }}</span
            >
          </div>
          <div v-for="(iterable, index) in item.shift" :key="index">
            <span v-if="index !== 'id'">
              {{ caps(index) }}: {{ iterable || "---" }}</span
            >
          </div>

          <div v-for="(iterable, index) in item.time_table" :key="index">
            <span v-if="index !== 'id'">
              {{ caps(index) }}: {{ iterable || "---" }}</span
            >
          </div>
        </v-tooltip>
        <span v-else>---</span>
      </template>

      <template v-slot:item.device_in="{ item }">
        <v-tooltip v-if="item && item.device_in" top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <div class="primary--text" v-bind="attrs" v-on="on">
              {{ (item.device_in && item.device_in.short_name) || "---" }}
            </div>
          </template>
          <div v-for="(iterable, index) in item.device_in" :key="index">
            <span v-if="index !== 'id'">
              {{ caps(index) }}: {{ iterable || "---" }}</span
            >
          </div>
        </v-tooltip>
        <span v-else>---</span>
      </template>

      <template v-slot:item.device_out="{ item }">
        <v-tooltip v-if="item && item.device_out" top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <div class="primary--text" v-bind="attrs" v-on="on">
              {{ (item.device_out && item.device_out.short_name) || "---" }}
            </div>
          </template>
          <div v-for="(iterable, index) in item.device_out" :key="index">
            <span v-if="index !== 'id'">
              {{ caps(index) }}: {{ iterable || "---" }}</span
            >
          </div>
        </v-tooltip>
        <span v-else>---</span>
      </template>
    </v-data-table>
  </div>
</template>
<script>
export default {
  data: () => ({
    overtime: false,
    options: {},
    Model: "Attendance",
    endpoint: "manual_report",
    search: "",
    snackbar: false,
    dialog: false,
    from_date: null,
    from_menu: false,
    to_date: null,
    to_menu: false,
    ids: [],
    departments: [],
    scheduled_employees: [],

    loading: false,
    total: 0,
    headers: [
      { text: "Date", align: "left", sortable: false, value: "date" },
      { text: "E.ID", align: "left", sortable: false, value: "employee_id" },
      {
        text: "First Name",
        align: "left",
        sortable: false,
        value: "employee.first_name"
      },
      {
        text: "Department",
        align: "left",
        sortable: false,
        value: "employee.department.name"
      },
      // { text: "Shift", align: "left", sortable: false, value: "shift" },
      { text: "Status", align: "left", sortable: false, value: "status" }
      // { text: "In", align: "left", sortable: false, value: "in" },
      // { text: "Out", align: "left", sortable: false, value: "out" },
      // {
      //   text: "Total Hrs",
      //   align: "left",
      //   sortable: false,
      //   value: "total_hrs"
      // },
      // { text: "OT", align: "left", sortable: false, value: "ot" },
      // {
      //   text: "Late coming",
      //   align: "left",
      //   sortable: false,
      //   value: "late_coming"
      // },
      // {
      //   text: "Early Going",
      //   align: "left",
      //   sortable: false,
      //   value: "early_going"
      // },
      // {
      //   text: "Device (in)",
      //   align: "left",
      //   sortable: false,
      //   value: "device_in"
      // },
      // {
      //   text: "Device (out)",
      //   align: "left",
      //   sortable: false,
      //   value: "device_out"
      // }
    ],
    payload: {
      per_page: 5,
      from_date: null,
      to_date: null,
      employee_id: null,
      department_id: -1,
      status: "Select All",
      late_early: "Select All"
    },
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: []
  }),
  custom_options: {},

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true
    }
  },
  created() {
    this.loading = true;

    let dt = new Date();
    let y = dt.getFullYear();
    let m = dt.getMonth() + 1;
    m = m < 10 ? "0" + m : m;
    let today = dt.getDate();
    today = today < 10 ? "0" + today : today;

    this.payload.from_date = `${y}-${m}-${today}`;
    this.payload.to_date = `${y}-${m}-${today}`;

    this.custom_options = {
      params: {
        per_page: 1000,
        shift_type: "manual_shift",
        company_id: this.$auth.user.company.id
      }
    };
  },

  methods: {
    fetch_logs() {
      this.getDataFromApi();
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let status = this.payload.status;
      let late_early = this.payload.late_early;

      switch (late_early) {
        case "Select All":
          late_early = "SA";
          break;

        default:
          late_early = late_early.charAt(0);
          break;
      }

      switch (status) {
        case "Select All":
          status = "SA";
          break;

        case "Missing":
          status = "---";
          break;

        default:
          status = status.charAt(0);
          break;
      }

      let options = {
        params: {
          per_page: itemsPerPage,
          page: page,
          company_id: this.$auth.user.company.id,
          ...this.payload,
          status,
          late_early,
          ot: this.overtime ? 1 : 0
        }
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
        this.loading = false;
      });
    }
  }
};
</script>
