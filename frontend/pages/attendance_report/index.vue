<template>
  <div v-if="can(`attendance_report_access`)">
    <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
      {{ response }}
    </v-snackbar>
    <v-dialog v-model="missingLogsDialog" width="auto">
      <v-card>
        <v-card-title dark class="popup_background">
          <span dense>Missing Device Logs </span>
          <v-spacer></v-spacer>
          <v-icon dark @click="missingLogsDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <missingrecords />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-card
      class="mt-5 pa-2"
      elevation="0"
      v-if="can(`attendance_report_view`)"
    >
      <v-toolbar flat dense>
        <v-toolbar-title
          style="font-size: 18px; font-weight: 600; width: 200px"
        >
          Attendance Reports
          <!-- <v-icon
            title="Click To Send Yesterday Report"
            color="green"
            @click="sendYesterdayReport()"
            >mdi-whatsapp</v-icon
          > -->
        </v-toolbar-title>
        <v-select
          style="width: 150px"
          class="mx-1"
          label="Type"
          outlined
          dense
          v-model="payload.status"
          x-small
          :items="statuses"
          item-value="id"
          item-text="name"
          :hide-details="true"
        ></v-select>
        <v-autocomplete
          style="width: 150px"
          class="mx-1"
          v-if="isCompany"
          label="Branch"
          @change="
            () => {
              getScheduledEmployees();
              getDepartments();
            }
          "
          placeholder="Branch"
          outlined
          dense
          v-model="payload.branch_id"
          x-small
          clearable
          :items="[{ id: null, branch_name: 'All Branches' }, ...branches]"
          item-value="id"
          item-text="branch_name"
          :hide-details="true"
        ></v-autocomplete>
        <v-autocomplete
          style="width: 150px"
          class="mx-1"
          label="Departments"
          @change="getScheduledEmployees"
          placeholder="Departments"
          outlined
          dense
          v-model="payload.department_ids"
          x-small
          clearable
          :items="departments"
          multiple
          item-text="name"
          item-value="id"
          :hide-details="true"
        >
          <template v-if="departments.length" #prepend-item>
            <v-list-item @click="toggleDepartmentSelection">
              <v-list-item-action>
                <v-checkbox
                  @click="toggleDepartmentSelection"
                  v-model="selectAllDepartment"
                  :indeterminate="isIndeterminateDepartment"
                  :true-value="true"
                  :false-value="false"
                ></v-checkbox>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>
                  {{ selectAllDepartment ? "Unselect All" : "Select All" }}
                </v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </template>
          <template v-slot:selection="{ item, index }">
            <span v-if="index === 0 && payload.department_ids.length == 1">{{
              item.name
            }}</span>
            <span
              v-else-if="
                index === 1 &&
                payload.department_ids.length == departments.length
              "
              class=" "
              >All Selected
            </span>
            <span v-else-if="index === 1" class=" ">
              {{ payload.department_ids.length }} Department(s)
            </span>
          </template>
        </v-autocomplete>
        <v-autocomplete
          style="width: 150px"
          class="mx-1"
          label="Employee ID"
          outlined
          dense
          v-model="payload.employee_id"
          :items="scheduled_employees"
          multiple
          item-value="system_user_id"
          item-text="name_with_user_id"
          placeholder="Employees"
          :hide-details="true"
        >
          <template v-if="scheduled_employees.length" #prepend-item>
            <v-list-item @click="toggleEmployeesSelection">
              <v-list-item-action>
                <v-checkbox
                  @click="toggleEmployeesSelection"
                  v-model="selectAllEmployees"
                  :indeterminate="isIndeterminateEmployee"
                  :true-value="true"
                  :false-value="false"
                ></v-checkbox>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>
                  {{ selectAllEmployees ? "Unselect All" : "Select All" }}
                </v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </template>
          <template v-slot:selection="{ item, index }">
            <span v-if="index === 0 && payload.employee_id.length == 1">{{
              item.name_with_user_id
            }}</span>
            <span
              v-else-if="
                index === 1 &&
                payload.employee_id.length == scheduled_employees.length
              "
              class=" "
              >All Selected
            </span>
            <span v-else-if="index === 1" class=" ">
              {{ payload.employee_id.length }} Employee(s)
            </span>
          </template>
        </v-autocomplete>
        <v-autocomplete
          style="width: 150px"
          class="mx-1"
          label="Report Templates"
          density="compact"
          outlined
          dense
          v-model="report_template"
          x-small
          :items="['Template1', 'Template2']"
          item-text="['Daily']"
          :hide-details="true"
        ></v-autocomplete>
        <div class="mx-1">
          <CustomFilter
            @filter-attr="filterAttr"
            :defaultFilterType="1"
            height="40px"
          />
        </div>
        <div class="text-right">
          <v-btn
            style="border-radius: 5px"
            @click="commonMethod"
            color="primary"
            primary
            >Generate
          </v-btn>
        </div>
      </v-toolbar>
    </v-card>

    <v-row no-gutters>
      <v-col cols="6">
        <v-card elevation="0" v-if="can(`attendance_report_view`)">
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
        </v-card>
      </v-col>

      <v-col cols="6">
        <v-card elevation="0" v-if="can(`attendance_report_view`)">
          <v-tabs
            class="slidegroup1"
            background-color="popup_background"
            right
            dark
          >
            <v-tabs-slider
              class="violet slidegroup1"
              style="height: 3px"
            ></v-tabs-slider>

            <!-- <v-tab
              @click="openRegeneratePopup"
              style="height: 30px"
              class="black--text slidegroup1"
            >
              <span style="font-size: 12px"
                ><v-icon small>mdi-cached</v-icon> Re-Generate Report</span
              >
            </v-tab> -->

            <v-tab
              @click="openGenerateLogPopup"
              style="height: 30px"
              class="black--text slidegroup1"
            >
              <span style="font-size: 12px"
                ><v-icon small>mdi-plus</v-icon> Manual Log</span
              >
            </v-tab>

            <!-- <v-tab style="height: 30px" class="black--text slidegroup1">
              <span style="font-size: 12px"
                ><v-icon small>mdi-mail</v-icon> Send</span
              >
            </v-tab> -->
            <v-tab
              style="height: 30px"
              class="black--text slidegroup1"
              @click="openMissingPopup"
            >
              <span style="font-size: 12px"
                ><v-icon small>mdi-download</v-icon> Missing Logs</span
              >
            </v-tab>

            <v-tab style="height: 30px" class="black--text slidegroup1">
              <v-menu bottom right>
                <template v-slot:activator="{ on, attrs }">
                  <span v-bind="attrs" v-on="on">
                    <v-icon dark-2 icon color="violet" small>mdi-file</v-icon>
                    Print/PDF
                  </span>
                </template>
                <v-list width="200" dense>
                  <v-list-item
                    v-if="can(`attendance_report_re_generate`)"
                    @click="openRegeneratePopup"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-cached </v-icon>
                      Re-Generate Report
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    v-if="can(`attendance_report_manual_entry_access`)"
                    @click="openGenerateLogPopup"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small>
                        mdi-plus-circle-outline
                      </v-icon>
                      Manual Log
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="process_file_in_child_comp(`Monthly`)">
                    <v-list-item-title style="cursor: pointer">
                      <img src="/icons/icon_print.png" class="iconsize" />
                      Print
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    @click="process_file_in_child_comp('Monthly_download_pdf')"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <img src="/icons/icon_pdf.png" class="iconsize" />
                      PDF
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item
                    @click="process_file_in_child_comp('Monthly_download_csv')"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <img src="/icons/icon_excel.png" class="iconsize" />
                      EXCEL
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-tab>
          </v-tabs>
        </v-card>
      </v-col>
      <v-col cols="12">
        <v-tabs-items v-model="tab">
          <v-tab-item value="tab-1">
            <AttendanceReport
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
            <AttendanceReport
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
            <AttendanceReport
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
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
import AttendanceReport from "../../components/attendance_report/reportComponent.vue";

import generalHeaders from "../../headers/general.json";
import multiHeaders from "../../headers/multi.json";
import doubleHeaders from "../../headers/double.json";
import missingrecords from "../../components/attendance_report/missingrecords.vue";

export default {
  components: { AttendanceReport, missingrecords },

  props: ["title", "render_endpoint", "process_file_endpoint"],

  data: () => ({
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
    statuses: [
      {
        name: `All Status`,
        id: `-1`,
      },
      {
        name: `Present`,
        id: `P`,
      },
      {
        name: `Absent`,
        id: `A`,
      },
      {
        name: `Missing`,
        id: `M`,
      },
      {
        name: `Late In`,
        id: `LC`,
      },
      {
        name: `Early Out`,
        id: `EG`,
      },
      {
        name: `Off`,
        id: `O`,
      },
      {
        name: `Leave`,
        id: `L`,
      },
      {
        name: `Holiday`,
        id: `H`,
      },
      {
        name: `Vaccation`,
        id: `V`,
      },
      {
        name: `Manual Entry`,
        id: `ME`,
      },
    ],
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
    //   this.commonMethod();
    // },
  },
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
