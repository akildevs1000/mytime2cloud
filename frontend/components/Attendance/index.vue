<template>
  <v-dialog
    :key="attendanceDialogKey"
    persistent
    v-model="attendanceDialog"
    max-width="1200px"
  >
    <WidgetsClose
      left="1190"
      @click="
        () => {
          attendanceDialog = false;
          attendanceDialogKey += 1;
        }
      "
    />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on"> View Logs </span>
    </template>
    <v-card v-if="can(`attendance_report_access`)">
      <v-tabs
        class="slidegroup1"
        v-model="tab"
        background-color="popup_background"
        dark
      >
        <v-tabs-slider
          class="violet slidegroup1"
          style="height: 3px"
        ></v-tabs-slider>

        <v-tab
          v-if="showTabs.single == true"
          style="height: 30px"
          href="#tab-1"
          class="black--text slidegroup1"
        >
          Single
        </v-tab>

        <v-tab
          v-if="showTabs.double == true"
          style="height: 30px"
          href="#tab-2"
          class="black--text slidegroup1"
        >
          Double
        </v-tab>

        <v-tab
          v-if="showTabs.multi == true"
          style="height: 30px"
          href="#tab-3"
          class="black--text slidegroup1"
        >
          Multi
        </v-tab>
      </v-tabs>
      <v-tabs-items v-model="tab">
        <v-tab-item value="tab-1">
          <AttendanceGeneral
            :key="branch_id"
            :statuses="statuses"
            :branch_id="branch_id"
            :from_date="from_date"
            :to_date="to_date"
          />
        </v-tab-item>
        <v-tab-item value="tab-2"
          ><AttendanceSplit
            :key="branch_id"
            :statuses="statuses"
            :branch_id="branch_id"
            :from_date="from_date"
            :to_date="to_date"
          />
        </v-tab-item>
        <v-tab-item value="tab-3"
          ><AttendanceMulti
            :key="branch_id"
            :statuses="statuses"
            :branch_id="branch_id"
            :from_date="from_date"
            :to_date="to_date"
          />
        </v-tab-item>
      </v-tabs-items>
    </v-card>
    <NoAccess v-else />
  </v-dialog>
</template>
<script>
export default {
  props: [
    "title",
    "render_endpoint",
    "process_file_endpoint",
    "statuses",
    "branch_id",
  ],

  data: () => ({
    attendanceDialogKey: 1,
    attendanceDialog: false,
    missingLogsDialog: false,
    selectFile: null,
    key: 1,
    payload: {},
    selectAllDepartment: false,
    selectAllEmployees: false,
    branches: [],
    tab: null,
    filters: {},
    attendancFilters: false,
    isFilter: false,
    datatable_search_textbox: "",
    datatable_filter_date: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    menu: false,
    selectedItems: [],
    time_table_dialog: false,
    log_details: false,
    overtime: false,
    options: {},
    date: null,
    menu: false,
    loading: false,
    time_menu: false,
    manual_time_menu: false,
    Model: "Attendance Reports",
    endpoint: "report",
    search: "",
    snackbar: false,
    add_manual_log: false,
    dialog: false,
    generateLogsDialog: false,
    reportSync: false,
    from_date: new Date().toISOString().slice(0, 10),
    to_date: new Date().toISOString().slice(0, 10),
    ids: [],
    departments: [],
    scheduled_employees: [],
    DateRange: true,
    devices: [],
    valid: true,
    nameRules: [(v) => !!v || "reason is required"],
    timeRules: [(v) => !!v || "time is required"],
    deviceRules: [(v) => !!v || "device is required"],
    daily_menu: false,
    dailyDate: false,
    editItems: {
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      time: null,
      manual_entry: false,
    },
    loading: false,
    total: 0,

    report_template: "Template1",
    report_type: "monthly11111111",

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
    filter_type_items: [
      {
        id: 1,
        name: "Today",
      },
      {
        id: 2,
        name: "Yesterday",
      },
      {
        id: 3,
        name: "This Week",
      },
      {
        id: 4,
        name: "This Month",
      },
      {
        id: 5,
        name: "Custom",
      },
    ],
    isCompany: true,
    showTabs: { single: true, double: true, multi: true },
  }),

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    watch: {
      options: {
        handler() {
          if (this.attendanceDialog == true) {
          }
        },
        deep: true,
      },
    },
  },
  mounted() {},
  async created() {
    this.loading = true;
    // this.setMonthlyDateRange();

    this.getAttendanceTabs();

    setTimeout(() => {
      this.tab = "tab-2";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-3";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-1";
    }, 1000);
  },

  methods: {
    getAttendanceTabs() {
      this.$axios
        .get("get_attendance_tabs", {
          params: {
            per_page: 10,
            company_id: this.$auth.user.company_id,
            from_date: this.from_date,
            to_date: this.to_date,
          },
        })
        .then(({ data }) => {
          this.showTabs = data;
          this.payload.showTabs = data;

          const valuesMap = {
            multi: 2,
            dual: 5,
            single: 6,
          };
          // Find the first key in `json` that is true and retrieve its value from the map
          const result = Object.entries(data).find(
            ([key, value]) => value
          )?.[0];

          this.shift_type_id = valuesMap[result] || 2;
        });
    },

    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },

    setStatusLabel(status) {
      const statuses = {
        A: "Absent",
        P: "Present",
        M: "Missing",
        LC: "Late In",
        EG: "Early Out",
        O: "Week Off",
        L: "Leave",
        H: "Holiday",
        V: "Vaccation",
      };
      return statuses[status];
    },
  },
};
</script>
