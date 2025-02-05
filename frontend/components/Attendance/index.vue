<template>
  <v-dialog :key="attendanceDialogKey" persistent v-model="attendanceDialog" max-width="1000px">
    <WidgetsClose left="990" @click="() => {
        attendanceDialog = false;
        attendanceDialogKey += 1;
    }" />
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
          :key="shift_type_id"
          style="height: 30px"
          href="#tab-1"
          class="black--text slidegroup1"
        >
          Single
        </v-tab>

        <v-tab
          v-if="showTabs.double == true"
          :key="shift_type_id"
          @click="commonMethod(2)"
          style="height: 30px"
          href="#tab-2"
          class="black--text slidegroup1"
        >
          Double
        </v-tab>

        <v-tab
          v-if="showTabs.multi == true"
          :key="shift_type_id"
          @click="commonMethod(3)"
          style="height: 30px"
          href="#tab-3"
          class="black--text slidegroup1"
        >
          Multi
        </v-tab>
      </v-tabs>
      <v-tabs-items v-model="tab">
        <v-tab-item value="tab-1">
          <AttendanceTable
            ref="attendanceReportRef"
            :key="shift_type_id"
            title="General Reports"
            :shift_type_id="shift_type_id"
            :headers="generalHeaders"
            :report_template="report_template"
            :payload1="payload11"
            process_file_endpoint=""
            render_endpoint="render_general_report"
          />
        </v-tab-item>
        <v-tab-item value="tab-2">
          <AttendanceTable
            ref="attendanceReportRef"
            title="Split Reports"
            :shift_type_id="shift_type_id"
            :headers="doubleHeaders"
            :report_template="report_template"
            :payload1="payload11"
            process_file_endpoint="multi_in_out_"
            render_endpoint="render_multi_inout_report"
            :key="shift_type_id"
          />
        </v-tab-item>
        <v-tab-item value="tab-3">
          <AttendanceTable
            ref="attendanceReportRef"
            :key="shift_type_id"
            title="Multi In/Out Reports"
            :shift_type_id="shift_type_id"
            :headers="multiHeaders"
            :report_template="report_template"
            :payload1="payload11"
            process_file_endpoint="multi_in_out_"
            render_endpoint="render_multi_inout_report"
          />
        </v-tab-item>
      </v-tabs-items>
    </v-card>
    <NoAccess v-else />
  </v-dialog>
</template>
<script>

import generalHeaders from "../../headers/general.json";
import multiHeaders from "../../headers/multi.json";
import doubleHeaders from "../../headers/double.json";

export default {

  props: ["title", "render_endpoint", "process_file_endpoint","statuses"],

  data: () => ({
    attendanceDialogKey: 1,
    attendanceDialog: false,
    missingLogsDialog: false,
    selectFile: null,
    key: 1,
    payload11: null,
    selectAllDepartment: false,
    selectAllEmployees: false,
    branches: [],
    tab: null,
    generalHeaders,
    multiHeaders,
    doubleHeaders,
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
    from_date: null,
    from_menu: false,
    to_date: null,
    to_menu: false,
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
      manual_entry: false,
    },
    loading: false,
    total: 0,

    report_template: "Template1",
    report_type: "monthly11111111",
    payload: {
      from_date: null,
      to_date: null,
      daily_date: null,
      employee_id: [],

      department_ids: [{ id: "-1", name: "" }],
      status: "-1",
      branch_id: null,
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

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    },
    isIndeterminateDepartment() {
      return (
        this.payload.department_ids.length > 0 &&
        this.payload.department_ids.length < this.departments.length
      );
    },
    isIndeterminateEmployee() {
      return (
        this.payload.employee_id.length > 0 &&
        this.payload.employee_id.length < this.scheduled_employees.length
      );
    },
  },

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
          this.commonMethod();
        }
      },
      deep: true,
    },
  },
    selectAllDepartment(value) {
      if (value) {
        this.payload.department_ids = this.departments.map((e) => e.id);
      } else {
        this.payload.department_ids = [];
      }
    },
    selectAllEmployees(value) {
      if (value) {
        this.payload.employee_id = this.scheduled_employees.map(
          (e) => e.system_user_id
        );
      } else {
        this.payload.employee_id = [];
      }
    },
    // tab(value) {
    //   this.payload11 = {
    //     ...this.payload,
    //     tabid: value,
    //   };
    // },
  },
  mounted() {},
  async created() {
    this.loading = true;
    // this.setMonthlyDateRange();
    this.payload.daily_date = new Date().toJSON().slice(0, 10);
    this.payload.department_ids = [];

    this.getAttendanceTabs();

    setTimeout(() => {
      this.getBranches();
      this.getScheduledEmployees();
    }, 3000);

    let dt = new Date();
    let y = dt.getFullYear();
    let m = dt.getMonth() + 1;
    let dd = new Date(dt.getFullYear(), m, 0);

    m = m < 10 ? "0" + m : m;

    this.payload.from_date = `${y}-${m}-01`;
    this.payload.from_date = `${y}-${m}-${dd.getDate()}`;
    this.payload.to_date = `${y}-${m}-${dd.getDate()}`;
    // setTimeout(() => {
    //  this.getDepartments();
    //}, 1000);

    setTimeout(() => {
      this.tab = "tab-2";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-3";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-1";
    }, 1000);

    setTimeout(() => {
      this.commonMethod();
    }, 5000);
  },

  methods: {
    async sendYesterdayReport() {
      confirm("Are you sure want to send Yesterday report?");
      {
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            company_name: this.$auth.user.company.name,
            report_template: "Template1",
            shift_type_id: "1",
            report_type: "Daily",
            status: "-1",
            daily_date: this.getYesterdayDate(),
          },
        };

        const { data } = await this.$axios.get("daily_generate_pdf", options);

        this.snackbar = true;
        this.response = "Yesterday PDF Report is sent to whatsapp successfully";
      }
    },
    openRegeneratePopup() {
      this.$refs.attendanceReportRef.reportSync = true;
    },
    openGenerateLogPopup() {
      this.$refs.attendanceReportRef.generateLogsDialog = true;
    },
    openMissingPopup() {
      this.missingLogsDialog = true;
    },

    process_file_in_child_comp(val) {
      if (this.payload.employee_id && this.payload.employee_id.length == 0) {
        alert("Employee not selected");
        return;
      }

      this.$refs.attendanceReportRef.process_file(val);
    },

    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeesSelection() {
      this.selectAllEmployees = !this.selectAllEmployees;
    },
    filterAttr(data) {
      this.from_date = data.from;
      this.to_date = data.to;
      this.filterType = "Monthly"; // data.type;
    },

    commonMethod(id = 0) {
      let filterDay = this.filter_type_items.filter(
        (e) => e.id == this.filterType
      );
      if (filterDay[0]) {
        if (filterDay[0].name == "Today") this.report_type = "Daily";
        else filterDay = filterDay[0].name;
      }

      if (filterDay == "") {
        filterDay = "Daily";
      }

      if (this.$auth.user.user_type == "department") {
        this.payload.department_ids = [this.$auth.user.department_id];
      }

      this.payload11 = {
        ...this.payload,
        statuses:this.statuses,
        report_type: "Monthly", //filterDay,
        tabselected: id, //this.tab
        from_date: this.from_date,
        to_date: this.to_date,
        filterType: this.filterType,
        key: this.key++,
      };

      this.getScheduledEmployees();

      this.getAttendanceTabs();
    },
    getYesterdayDate() {
      const today = new Date();
      today.setDate(today.getDate() - 1); // Subtract one day to get yesterday's date

      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, "0"); // Months are 0-indexed, so add 1
      const day = String(today.getDate()).padStart(2, "0"); // Pad single-digit day with zero

      return `${year}-${month}-${day}`;
    },
    week() {
      const today = new Date();
      const dayOfWeek = today.getDay(); // Sunday = 0, Monday = 1, ..., Saturday = 6
      const startOfWeek = new Date(
        today.getFullYear(),
        today.getMonth(),
        today.getDate() - dayOfWeek
      );
      const endOfWeek = new Date(
        today.getFullYear(),
        today.getMonth(),
        startOfWeek.getDate() + 6
      );

      return [
        startOfWeek.toISOString().slice(0, 10),
        endOfWeek.toISOString().slice(0, 10),
      ];
    },

    getScheduledEmployees() {
      let options = {
        params: {
          per_page: 1000,
          branch_id: this.payload.branch_id,
          company_id: this.$auth.user.company_id,
          department_ids: this.payload.department_ids,
          shift_type_id: this.shift_type_id,
        },
      };

      this.$axios
        .get(`/scheduled_employees_with_type`, options)
        .then(({ data }) => {
          this.scheduled_employees = data;
          // this.scheduled_employees.unshift({
          //   system_user_id: "",
          //   name_with_user_id: "All Employees",
          // });
        });
    },
    setSevenDays(selected_date) {
      const date = new Date(selected_date);

      date.setDate(date.getDate() + 6);

      let datetime = new Date(date);

      let d = datetime.getDate();
      d = d < "10" ? "0" + d : d;
      let m = datetime.getMonth() + 1;
      m = m < 10 ? "0" + m : m;
      let y = datetime.getFullYear();

      this.max_date = `${y}-${m}-${d}`;
      this.payload.to_date = `${y}-${m}-${d}`;
    },

    setThirtyDays(selected_date) {
      const date = new Date(selected_date);

      date.setDate(date.getDate() + 29);

      let datetime = new Date(date);

      let d = datetime.getDate();
      d = d < "10" ? "0" + d : d;
      let m = datetime.getMonth() + 1;
      m = m < 10 ? "0" + m : m;
      let y = datetime.getFullYear();

      this.max_date = `${y}-${m}-${d}`;
      this.payload.to_date = `${y}-${m}-${d}`;
    },

    set_date_save(from_menu, field) {
      from_menu.save(field);

      if (this.report_type == "Weekly") {
        this.setSevenDays(this.payload.from_date);
      } else if (
        this.report_type == "Monthly" ||
        this.report_type == "Custom"
      ) {
        this.setThirtyDays(this.payload.from_date);
      }
    },

    getBranches() {
      if (this.$auth.user.branch_id) {
        this.payload.branch_id = this.$auth.user.branch_id;

        this.isCompany = false;
        return;
      }

      this.$axios
        .get("branch", {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.branches = data.data;
        });
    },
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
    async getDepartments() {
      let config = {
        params: {
          branch_id: this.payload.branch_id,
          company_id: this.$auth.user.company_id,
        },
      };
      try {
        const { data } = await this.$axios.get(`department-list`, config);
        this.departments = data;
        this.toggleDepartmentSelection();
        setTimeout(() => {
          this.commonMethod();
        }, 3000);
      } catch (error) {
        console.error("Error fetching departments:", error);
      }
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
<!-- <style>

.slidegroup1 .v-slide-group {
  height: 34px !important;
}
</style> -->
