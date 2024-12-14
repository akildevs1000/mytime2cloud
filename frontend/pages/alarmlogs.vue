<template>
  <div>
    <v-row>
      <v-col>
        <v-card class="mb-5" elevation="0">
          <v-toolbar flat>
            <v-toolbar-title>
              <span> Alarm Reports </span>
            </v-toolbar-title>

            <span>
              <v-btn
                x-small
                :ripple="false"
                text
                title="Reload"
                @click="clearFilters()"
              >
                <v-icon dark @click="clearFilters()">mdi-reload</v-icon>
              </v-btn>
            </span>
            <span>
              <!-- <v-btn
                  x-small
                  :ripple="false"
                  text
                  title="Filter"
                  @click="attendancFilters = true"
                >
                  <v-icon dark @click="attendancFilters = true"
                    >mdi-filter</v-icon
                  >
                </v-btn> -->
            </span>

            <v-spacer></v-spacer>
            <span>
              <Calender
                style="width: 100%"
                @filter-attr="filterAttr"
                :defaultFilterType="1"
                :height="'28px '"
              />

              <!-- <v-btn
                  x-small
                  :ripple="false"
                  text
                  title="Render"
                  @click="renderVisitorDialog = true"
                >
                  <v-icon dark>mdi-cached</v-icon>
                </v-btn> -->
            </span>
          </v-toolbar>

          <v-data-table
            :headers="log_headers"
            :items="log_list"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }"
            class="elevation-1"
            model-value="data.id"
            :server-items-length="totalRowsCount"
          >
            <template v-slot:item.sno="{ item, index }">
              {{
                currentPage
                  ? (currentPage - 1) * perPage +
                    (cumulativeIndex + log_list.indexOf(item))
                  : ""
              }}
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script>
function getCurrentDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}
import Calender from "../components/Calender.vue";

export default {
  components: { Calender },
  data: () => ({
    keyId: 1,
    purposeList: [],
    branchesList: [],
    hostList: [],
    cumulativeIndex: 1,
    perPage: 10,
    currentPage: 1,
    renderVisitorDialog: false,
    users: [],
    formData: {
      max: 10,
      date: getCurrentDate(),
      company_id: 0,
    },
    attendancFilters: false,
    filters: {},
    isFilter: false,
    totalRowsCount: 0,
    datatable_search_textbox: "",
    datatable_filter_date: "",
    filter_visitor_id: "",
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    filter_visitor_id: "",
    generateLogsDialog: false,
    reportSync: false,
    isCompany: true,
    time_table_dialog: false,
    log_details: false,
    overtime: false,
    options: {},
    date: null,
    menu: false,
    loading: false,
    time_menu: false,
    manual_time_menu: false,
    Model: "Visitor Reports",
    endpoint: "alarm_logs",
    search: "",
    snackbar: false,
    add_manual_log: false,
    dialog: false,
    from_date: null,
    from_menu: false,
    to_date: null,
    to_menu: false,
    ids: [],
    visitors: [],
    DateRange: true,
    devices: [],
    valid: true,
    nameRules: [(v) => !!v || "reason is required"],
    timeRules: [(v) => !!v || "time is required"],
    deviceRules: [(v) => !!v || "device is required"],
    daily_menu: false,
    daily_date: null,
    dailyDate: false,
    editItems: {
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      time: null,
    },
    loading: false,
    total: 0,
    log_headers: [
      {
        text: "#",
        align: "left",
        sortable: false,
        filterable: false,
        value: "sno",
      },

      {
        text: "Device",
        align: "left",
        sortable: false,
        filterable: false,
        value: "devices.name",
      },
      {
        text: "Location",
        align: "left",
        sortable: false,
        filterable: false,
        value: "devices.location",
      },
      {
        text: "Time",
        align: "left",
        sortable: false,
        filterable: false,
        value: "log_time",
      },
    ],
    headers: [
      {
        text: "#",
        align: "left",
        sortable: false,
        filterable: false,
        value: "sno",
      },
      {
        text: "Visitor  ",
        align: "left",
        sortable: false,
        filterable: true,
        filterSpecial: false,
        value: "visitor_first_name",
        key: "item.visitor",
      },
      {
        text: "Date",
        align: "left",
        sortable: true,
        filterable: false,
        filterSpecial: true,
        value: "date",
      },

      {
        text: "Host",
        align: "left",
        sortable: false,
        filterable: true,
        filterSpecial: true,
        value: "host_id",
        key: "host_id",
      },
      {
        text: "Purpose",
        align: "left",
        sortable: true,
        filterable: true,
        filterSpecial: true,
        value: "purpose_id",
      },

      {
        text: "Check In",
        align: "left",
        sortable: true,
        filterable: true,
        filterSpecial: false,
        value: "in",
      },
      {
        text: "Check Out",
        align: "left",
        sortable: true,
        filterable: true,
        filterSpecial: false,
        value: "out",
      },
      {
        text: "Duration",
        align: "left",
        sortable: true,
        filterable: true,
        filterSpecial: false,
        value: "total_hrs",
      },
      {
        text: "OverStay",
        align: "left",
        sortable: true,
        filterable: false,
        filterSpecial: true,
        value: "overstay",
      },
      { text: "Options", value: "options", sortable: false },
    ],
    frequency: "Monthly",

    payload: {
      from_date: null,
      to_date: null,
      daily_date: null,
      visitor_id: "",
      status: "All",
    },
    log_payload: {
      user_id: null,
      device_id: "",
      date: null,
      time: null,
    },
    log_list: [],
    snackbar: false,
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    shifts: [],
    errors: [],
    custom_options: {},
    max_date: null,
    item: null,
    viewDialog: false,
    attendance: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    },
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
      deep: true,
    },
  },
  created() {
    this.loading = true;
    this.getVisitors();
    // this.setMonthlyDateRange();
    this.payload.daily_date = new Date().toJSON().slice(0, 10);
    this.custom_options = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
      },
    };
    this.getDeviceList();

    let dt = new Date();
    let y = dt.getFullYear();
    let m = dt.getMonth() + 1;
    let dd = new Date(dt.getFullYear(), m, 0);

    m = m < 10 ? "0" + m : m;

    this.payload.from_date = `${y}-${m}-01`;
    this.payload.to_date = `${y}-${m}-${dd.getDate()}`;

    //this.getDataFromApi();

    if (this.$auth.user.branch_id == null || this.$auth.user.branch_id == 0) {
      let branch_header = [
        {
          text: "Branch",
          align: "left",
          sortable: true,
          value: "branch_id",
          filterable: true,
          filterName: "branch_id",
          filterSpecial: true,
        },
      ];
      this.headers.splice(2, 0, ...branch_header);
    }
  },

  methods: {
    viewInfo(item) {
      this.keyId++;
      this.item = item.visitor;
      this.attendance = item;
      this.viewDialog = true;
    },
    filterAttr(data) {
      this.payload.from_date = data.from;
      this.payload.to_date = data.to;
      this.getDataFromApi();
    },
    addUser() {
      this.users.push({ visitor_id: "" });
    },
    removeUser(index) {
      this.users.splice(index, 1);
    },
    submitData() {
      this.formData.userIds = this.users.map((e) => e.visitor_id);
      this.formData.company_id = this.$auth.user.company_id;

      this.$axios
        .post(`/render_daily_report`, this.formData)
        .then(({ data }) => {
          this.getDataFromApi();
          this.snackbar = true;
          this.response = data.message;
          this.dialog = false;
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
    },

    processFilterDates() {
      const date = new Date(this.payload.from_date);

      date.setDate(date.getDate() + (this.frequency == "Weekly" ? 6 : 30));

      const d = date.getDate().toString().padStart(2, "0");
      const m = (date.getMonth() + 1).toString().padStart(2, "0");
      const y = date.getFullYear();

      if (this.frequency !== "Custom") {
        this.payload.to_date = `${y}-${m}-${d}`;
      }

      this.max_date = `${y}-${m}-${d}`;

      this.getDataFromApi();
    },

    set_date_save(from_menu, field) {
      from_menu.save(field);
      this.processFilterDates();
    },
    changeReportFrequency() {
      this.processFilterDates();
    },

    applyFilters(name, value) {
      //if (value && value.length < 2) return false;

      this.getDataFromApi();
    },
    async toggleFilter() {
      this.isFilter = !this.isFilter;

      if (this.isFilter) {
        this.refresh = true;
        this.handleChangeEvent();
      }
    },
    async handleChangeEvent() {
      this.branchesList = await this.$store.dispatch("fetchDropDowns", {
        key: "branchList",
        endpoint: "branch-list",
      });
      this.hostList = await this.$store.dispatch("fetchDropDowns", {
        key: "hostList",
        endpoint: "host_list",
      });
      this.purposeList = await this.$store.dispatch("fetchDropDowns", {
        key: "purposeList",
        endpoint: "purpose_list",
      });
    },
    clearFilters() {
      this.filters = {};
      this.isFilter = false;
      this.getDataFromApi();
    },
    getDeviceList() {
      let payload = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/device_list`, payload).then(({ data }) => {
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

      this.$axios
        .post(`/generate_log`, log_payload)
        .then(({ data }) => {
          this.getDataFromApi();
          this.add_manual_log = false;
          this.loading = false;
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    setVisitorId(id) {
      this.$store.commit("visitor_id", id);
    },

    async getVisitors() {
      // return;
      let payload = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      await this.$axios.get(`/visitors_with_type`, payload).then(({ data }) => {
        this.visitors = data;

        this.visitors.unshift({
          system_user_id: "",
          name_with_user_id: "Select All",
        });
      });
    },
    // getDevices(options) {
    //   this.$axios.get(`/device`, options).then(({ data }) => {
    //     this.devices = data.data;
    //   });
    // },

    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      // if (this.filters) {
      //   page = 1;
      // }

      if (this.frequency == "Custom") {
        if (this.payload.from_date == null) {
          return false;
        }
        if (this.payload.to_date == null) {
          return false;
        }
      }

      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          ...this.payload,
          frequency: this.frequency,
          // status: this.getStatus(this.payload.status),
          ...this.filters,
        },
      };

      this.currentPage = page;
      this.perPage = itemsPerPage;
      if (filter_column != "") options.params[filter_column] = filter_value;
      this.$axios.get(url, options).then(async ({ data }) => {
        this.log_list = data.data;

        this.loading = false;

        this.totalRowsCount = data.total;
      });
    },

    editItem(item) {
      this.dialog = true;
      this.editItems.UserID = item.visitor_id;
      this.editItems.date = item.edit_date;
    },

    update() {
      if (this.$refs.form.validate()) {
        let payload = {
          UserID: this.editItems.UserID,
          LogTime: this.editItems.date + " " + this.editItems.time,
          DeviceID: this.editItems.device_id,
          user_id: this.editItems.UserID,
          company_id: this.$auth.user.company_id,
          reason: this.editItems.reason,
        };

        this.$axios
          .post("/generate_manual_log", payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = data.errors;
              // this.msg = data.message;
            } else {
              this.snackbar = true;
              this.response = data.message;
              // this.editItems = [];
              // this.renderByType("render_general_report");
              this.close();
            }
          })
          .catch((e) => console.log(e));
      }
    },
    renderByType(type) {
      const UserID = this.editItems.UserID;
      const date = this.editItems.date;

      if (!UserID || !date) {
        alert("System User Id and Date field is required");
        return;
      }

      let payload = {
        params: {
          date: this.editItems.date,
          UserID: this.editItems.UserID,
          updated_by: this.$auth.user.id,
          company_id: this.$auth.user.company_id,
          manual_entry: true,
          reason: this.editItems.reason,
        },
      };

      this.$axios
        .get("/" + type, payload)
        .then(({ data }) => {
          this.loading = false;
          this.snackbar = true;
          this.response = data.message;
          this.getDataFromApi();
        })
        .catch((e) => console.log(e));
    },
    viewItem(item) {
      this.log_list = [];
      let options = {
        params: {
          per_page: 500,
          UserID: item.system_user_id,
          LogTime: item.edit_date,
          company_id: this.$auth.user.company_id,
        },
      };
      this.log_details = true;

      this.$axios.get("visitor_log_list", options).then(({ data }) => {
        this.log_list = data.data;
      });

      // this.editedIndex = this.data.indexOf(item);
      // this.editedItem = Object.assign({}, item);
      // this.dialog = true;
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    process_file(action) {
      if (!this.data.length) {
        alert("No record found");
        return;
      }
      const { visitor_id, daily_date, from_date, to_date, status } =
        this.payload;
      const frequency = this.frequency;
      const company_id = this.$auth.user.company_id;
      let { itemsPerPage } = this.options;
      let path = process.env.BACKEND_URL + "/visitor_attendance_report";

      let qs = ``;

      qs += `${path}`;
      qs += `?company_id=${company_id}`;
      qs += `&status=${status}`;
      qs += `&visitor_id=${visitor_id}`;
      qs += `&frequency=${frequency}`;
      qs += `&action=${action}`;
      qs += `&per_page=${20}`;

      if (frequency == "Daily") {
        qs += `&daily_date=${daily_date}`;
      } else {
        qs += `&from_date=${from_date}&to_date=${to_date}`;
      }

      let report = document.createElement("a");
      report.setAttribute("href", qs);
      report.setAttribute("target", "_blank");
      report.click();

      this.getDataFromApi();
      return;
    },
  },
};
</script>
