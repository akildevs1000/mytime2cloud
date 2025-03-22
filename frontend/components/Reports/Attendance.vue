<template>
  <div v-if="can(`attendance_report_access`)">
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
        </v-toolbar-title>
        <v-select
          style="width: 150px"
          class="mx-1"
          label="Type"
          outlined
          dense
          v-model="payload.statuses"
          multiple
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
            class="white--text"
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
              :key="0"
              style="height: 30px"
              href="#tab-1"
              class="black--text slidegroup1"
              @click="
                () => {
                  shift_type_id = 0;
                  commonMethod(0);
                }
              "
            >
              Single
            </v-tab>

            <v-tab
              v-if="showTabs.double == true"
              :key="5"
              @click="
                () => {
                  shift_type_id = 5;
                  commonMethod(2);
                }
              "
              style="height: 30px"
              href="#tab-2"
              class="black--text slidegroup1"
            >
              Double
            </v-tab>

            <v-tab
              v-if="showTabs.multi == true"
              :key="2"
              @click="
                () => {
                  shift_type_id = 2;
                  commonMethod(3);
                }
              "
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
        <v-dialog v-model="regenerateDialog" max-width="680px">
          <WidgetsClose
            left="670"
            @click="
              () => {
                regenerateDialog = false;
                loading = false;
              }
            "
          />
          <v-card>
            <v-alert dark dense flat class="primary">Regenerate Report</v-alert>
            <v-card-text>
              <v-container>
                <div class="mx-1">
                  <CustomFilter
                    @filter-attr="filterAttrForRegenerate"
                    :defaultFilterType="1"
                    height="40px"
                    width="90%"
                  />
                </div>
                <div class="px-1 mt-3" style="width: 100%">
                  <v-autocomplete
                    style="width: 100%"
                    label="Employee ID"
                    outlined
                    dense
                    v-model="form.employee_ids"
                    :items="scheduled_employees"
                    multiple
                    item-value="system_user_id"
                    item-text="name_with_user_id"
                    placeholder="Employees"
                    :hide-details="true"
                  >
                    <template v-if="scheduled_employees.length" #prepend-item>
                      <v-list-item
                        @click="toggleEmployeesSelectionForRegenerate"
                      >
                        <v-list-item-action>
                          <v-checkbox
                            @click="toggleEmployeesSelectionForRegenerate"
                            v-model="selectAllEmployeesForRegenerate"
                            :indeterminate="
                              isIndeterminateEmployeeForRegenerate
                            "
                            :true-value="true"
                            :false-value="false"
                          ></v-checkbox>
                        </v-list-item-action>
                        <v-list-item-content>
                          <v-list-item-title>
                            {{
                              selectAllEmployeesForRegenerate
                                ? "Unselect All"
                                : "Select All"
                            }}
                          </v-list-item-title>
                        </v-list-item-content>
                      </v-list-item>
                    </template>
                    <template v-slot:selection="{ item, index }">
                      <span
                        v-if="index === 0 && form.employee_ids.length == 1"
                        >{{ item.name_with_user_id }}</span
                      >
                      <span
                        v-else-if="
                          index === 1 &&
                          form.employee_ids.length == scheduled_employees.length
                        "
                        class=" "
                        >All Selected
                      </span>
                      <span v-else-if="index === 1" class=" ">
                        {{ form.employee_ids.length }} Employee(s)
                      </span>
                    </template>
                  </v-autocomplete>
                </div>

                <v-card
                  v-if="message"
                  outlined
                  class="ma-1 pa-1"
                  style="max-width: 100%"
                >
                  <v-sheet
                    style="max-height: 400px; overflow: auto; padding: 8px"
                  >
                    <pre>
      {{ message }}
    </pre
                    >
                  </v-sheet>
                </v-card>

                <div class="text-right mt-3">
                  <v-btn
                    small
                    color="grey white--text"
                    @click="
                      () => {
                        regenerateDialog = false;
                        loading = false;
                      }
                    "
                    >Cancel</v-btn
                  >
                  <v-btn
                    small
                    color="primary"
                    :loading="loading"
                    @click="regnerateReport"
                    >Submit</v-btn
                  >
                </div>
              </v-container>
            </v-card-text>
          </v-card>
        </v-dialog>
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

            <v-tab
              style="height: 30px"
              class="black--text slidegroup1"
              @click="
                () => {
                  regenerateDialog = true;
                  message = '';
                }
              "
            >
              <span style="font-size: 12px"
                ><v-icon small>mdi-download</v-icon> Regererate Report</span
              >
            </v-tab>

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
              :key="1"
              :shift_type_id="shift_type_id"
              title="General Reports"
              :headers="generalHeaders"
              :report_template="report_template"
              :payload1="payload11"
              render_endpoint="render_general_report"
            />
          </v-tab-item>
          <v-tab-item value="tab-2">
            <AttendanceReport
              ref="attendanceReportRef"
              title="Split Reports"
              :key="5"
              :shift_type_id="shift_type_id"
              :headers="doubleHeaders"
              :report_template="report_template"
              :payload1="payload11"
              render_endpoint="render_multi_inout_report"
            />
          </v-tab-item>
          <v-tab-item value="tab-3">
            <AttendanceReport
              ref="attendanceReportRef"
              :key="2"
              :shift_type_id="shift_type_id"
              title="Multi In/Out Reports"
              :headers="multiHeaders"
              :report_template="report_template"
              :payload1="payload11"
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

  props: ["title", "render_endpoint"],

  data: () => ({
    missingLogsDialog: false,
    key: 1,
    shift_type_id: 0,
    payload11: null,
    selectAllDepartment: false,
    selectAllEmployees: false,
    selectAllEmployeesForRegenerate: [],
    branches: [],
    tab: null,
    generalHeaders,
    multiHeaders,
    doubleHeaders,
    date: null,
    menu: false,
    loading: false,
    Model: "Attendance Reports",
    endpoint: "report",
    search: "",
    dialog: false,
    from_date: null,
    from_menu: false,
    to_date: null,
    departments: [],
    scheduled_employees: [],

    report_template: "Template1",
    report_type: "Monthly",
    regenerateDialog: false,
    form: {
      company_id: "",
      employee_ids: "",
      from_date: "",
      to_date: "",
    },
    message: "",
    alertType: "success",
    payload: {
      from_date: null,
      to_date: null,
      employee_id: [],
      department_ids: [{ id: "-1", name: "" }],
      statuses: [],
      branch_id: null,
    },
    log_payload: {
      user_id: null,
      device_id: "",
      date: null,
      time: null,
    },
    data: [],
    statuses: [],
    isCompany: true,
    showTabs: { single: true, double: true, multi: true },
  }),

  computed: {
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
    isIndeterminateEmployeeForRegenerate() {
      return (
        this.form.employee_ids.length > 0 &&
        this.form.employee_ids.length < this.scheduled_employees.length
      );
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
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
    selectAllEmployeesForRegenerate(value) {
      if (value) {
        this.form.employee_ids = this.scheduled_employees.map(
          (e) => e.system_user_id
        );
      } else {
        this.form.employee_ids = [];
      }
    },
  },
  async created() {
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
    setTimeout(() => {
      this.tab = "tab-2";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-3";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-1";
    }, 1000);
    await this.getStatuses();
  },

  methods: {
    async getStatuses() {
      let { data } = await this.$axios.get(`attendance-statuses`);
      this.statuses = data;
    },
    openMissingPopup() {
      this.missingLogsDialog = true;
    },
    process_file_in_child_comp(val) {
      if (this.payload.employee_id && this.payload.employee_id.length == 0) {
        alert("Employee not selected");
        return;
      }

      let type = val.toLowerCase();

      let process_file_endpoint = "";

      if (this.shift_type_id == 2 || this.shift_type_id == 5) {
        process_file_endpoint = "multi_in_out_";
      }

      let path = this.$backendUrl + "/" + process_file_endpoint + type;

      let qs = ``;

      qs += `${path}`;
      qs += `?report_template=${this.report_template}`;
      qs += `&main_shift_type=${this.shift_type_id}`;

      if (parseInt(this.payload.branch_id) > 0)
        qs += `&branch_id=${this.payload.branch_id}`;

      qs += `&shift_type_id=${this.shift_type_id}`;
      qs += `&company_id=${this.$auth.user.company_id}`;
      // qs += `&status=${this.payload.status & this.payload.status || "-1"}`;
      if (
        this.payload.department_ids &&
        this.payload.department_ids.length > 0
      ) {
        qs += `&department_ids=${this.payload.department_ids.join(",")}`;
      }
      qs += `&employee_id=${this.payload.employee_id}`;
      qs += `&report_type=${this.report_type}`;

      qs += `&from_date=${this.from_date}&to_date=${this.to_date}`;

      // Convert showTabs object into a URL-friendly format
      if (this.payload.showTabs) {
        qs += `&showTabs=${encodeURIComponent(
          JSON.stringify(this.payload.showTabs)
        )}`;
      }
      console.log(qs);
      let report = document.createElement("a");
      report.setAttribute("href", qs);
      report.setAttribute("target", "_blank");
      report.click();
    },
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeesSelection() {
      this.selectAllEmployees = !this.selectAllEmployees;
    },
    toggleEmployeesSelectionForRegenerate() {
      this.selectAllEmployeesForRegenerate =
        !this.selectAllEmployeesForRegenerate;
    },

    filterAttrForRegenerate(data) {
      this.form.from_date = data.from;
      this.form.to_date = data.to;
      this.form.company_id = this.$auth.user.company_id;
    },
    filterAttr(data) {
      this.from_date = data.from;
      this.to_date = data.to;
      this.filterType = "Monthly"; // data.type;
    },
    commonMethod(id = 0) {
      if (this.$auth.user.user_type == "department") {
        this.payload.department_ids = [this.$auth.user.department_id];
      }

      this.payload11 = {
        ...this.payload,
        report_type: "Monthly",
        tabselected: id, //this.tab
        from_date: this.from_date,
        to_date: this.to_date,
        filterType: this.filterType,
        showTabs: JSON.stringify(this.showTabs),
        key: this.key++,
      };

      this.getScheduledEmployees();

      this.getAttendanceTabs();
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
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    regnerateReport() {
      this.loading = true;
      let payload = {
        params: {
          dates: [this.form.from_date, this.form.to_date],
          company_ids: [this.$auth.user.company_id],
          user_id: this.$auth.user.id,
          updated_by: this.$auth.user.id,
          reason: "",
          employee_ids: this.form.employee_ids,
          shift_type_id: this.shift_type_id,
          company_id: this.$auth.user.company_id,
        },
      };
      console.log("🚀 ~ render_report ~ payload:", payload);
      // return;
      this.$axios
        .get("render_logs", payload)
        .then(({ data }) => {
          this.loading = false;

          let message = "";
          data.forEach((element) => {
            message = message + " \n " + element;
          });
          this.message = message;
          this.loading = false;

          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
