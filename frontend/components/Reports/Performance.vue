<template>
  <span>
    <v-card class="mt-5 pa-2" elevation="0">
      <v-toolbar flat dense>
        <v-toolbar-title
          style="font-size: 18px; font-weight: 600; width: 200px"
        >
          Performance Reports
        </v-toolbar-title>

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

        <div class="mx-1">
          <v-menu
            ref="menu"
            v-model="menu"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="auto"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-text-field
                label="Select Month"
                hide-details
                :value="formattedMonths"
                persistent-hint
                append-icon="mdi-calendar"
                readonly
                outlined
                dense
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              color="primary"
              style="min-height: 320px"
              v-model="months"
              no-title
              type="month"
              multiple
              range
              @input="addFirstAndLastDay(months)"
            ></v-date-picker>
          </v-menu>
        </div>
        <div class="text-right">
          <v-btn
            style="border-radius: 5px"
            @click="getDataFromApi"
            color="primary"
            class="white--text"
            >Generate
          </v-btn>
        </div>
      </v-toolbar>
    </v-card>

    <v-row no-gutters>
      <v-col cols="12">
        <v-card elevation="0">
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

            <v-tab style="height: 30px" class="black--text slidegroup1">
              <v-dialog ref="dialog" width="1000px">
                <WidgetsClose
                  left="990"
                  @click="
                    () => {
                      $refs.dialog.isActive = false;
                    }
                  "
                />
                <template v-slot:activator="{ on, attrs }">
                  <v-icon left> mdi-star-outline </v-icon>
                  <span style="font-size: 12px" v-bind="attrs" v-on="on"
                    >Rating Info</span
                  >
                </template>
                <PerformanceRatingDescription />
              </v-dialog>
            </v-tab>
          </v-tabs>
        </v-card>
      </v-col>
      <v-col cols="12">
        <v-data-table
          dense
          :headers="performanceHeader"
          :items="data"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [10, 30, 50, 100, 500, 1000],
            page: true,
          }"
          class="elevation-1"
          model-value="data.id"
          :server-items-length="totalRowsCount"
          fixed-header
          :height="750"
          no-data-text="No Data available. Click 'Generate' button to see the results"
        >
          <template v-slot:item.employee_name="{ item }">
            <div
              class="pa-1"
              style="display: flex; align-items: center; gap: 10px"
            >
              <v-avatar size="45">
                <v-img
                  :src="
                    item?.employee?.profile_picture || '/no-profile-image.jpg'
                  "
                ></v-img>
              </v-avatar>
              <div>
                <div style="font-size: 13px">
                  {{ item?.employee?.first_name || "---" }}
                  {{ item?.employee?.last_name || "---" }}
                </div>
                <small style="font-size: 12px; color: #6c7184">
                  {{ item?.employee?.employee_id || "---" }}
                </small>
              </div>
            </div>
          </template>
          <template v-slot:item.a_count="{ item }">
            {{ item.a_count - $utils.getRemainingDays() }}
          </template>
          <template v-slot:item.rating="{ item }">
            <div
              style="
                display: flex;
                justify-content: space-between;
                max-width: 200px;
              "
            >
              <v-rating
                dense
                hide-details
                :value="$utils.getRating(item.p_count, from_date, to_date)"
                background-color="green lighten-3"
                color="green"
                half-increments
              ></v-rating>
              <div>
                <v-chip small class="green white--text"
                  >{{ $utils.getRating(item.p_count, from_date, to_date) }}
                  / 5
                </v-chip>
              </div>
            </div>
          </template>
          <template v-slot:item.options="{ item }">
            <v-menu bottom left>
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list dense>
                <v-list-item>
                  <v-list-item-title style="cursor: pointer">
                    <PerformanceSingle
                      :item="{
                        leave_group_id: item?.employee?.leave_group_id,
                        company_id: $auth.user.company_id,
                        p_count: getPresentCount(item),
                        a_count: getAbsentCount(item),
                        o_count: item?.o_count || 0,
                        other_count: getOtherCount(item),

                        rating: $utils.getRating(
                          item.p_count,
                          from_date,
                          to_date
                        ),
                      }"
                      :employee="{
                        name: `${item?.employee?.title} ${item?.employee?.full_name}`,
                        profile_picture: `${item?.employee?.profile_picture}`,
                        employee_id: item.employee_id,
                        employee_id_for_payroll: item?.employee?.employee_id,
                        employee_id_for_leave: item?.employee?.employee_id,
                        designation: item.employee?.designation?.name,
                        branch: item.employee?.branch?.branch_name,
                        company: $auth?.user?.company?.name,
                        email: item?.employee?.local_email,
                        whatsapp_number: item?.employee?.whatsapp_number,
                        home_country: item?.employee?.home_country,
                        reporting_manager:
                          item?.employee?.reporting_manager?.first_name,
                        joining_date: item?.employee?.show_joining_date,
                      }"
                    />
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </span>
</template>
<script>
import performanceHeader from "../../headers/performance.json";

export default {
  props: ["title", "render_endpoint", "process_file_endpoint"],

  data: () => ({
    key: 1,
    selectAllDepartment: false,
    selectAllEmployees: false,
    branches: [],
    tab: null,
    performanceHeader,
    date: null,
    menu: false,
    month: null,

    from_date: null,
    to_date: null,

    months: [],

    options: {},
    date: null,
    Model: "Attendance Reports",
    endpoint: "report",
    ids: [],
    departments: [],
    scheduled_employees: [],
    loading: false,
    total: 0,
    totalRowsCount: 0,
    payload: {
      employee_id: [],
      department_ids: [{ id: "-1", name: "" }],
      branch_id: null,
    },
    data: [],
    isCompany: true,
    showTabs: { single: true, double: true, multi: true },
  }),

  computed: {
    formattedMonths() {
      if (this.months.length === 0) return "";

      // Format each month in the array
      const formatted = this.months.map((month) => {
        return this.formatMonth(month);
      });

      // Join the formatted months with ' to '
      return formatted.join(" to ");
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
  },
  async created() {
    // this.setMonthlyDateRange();
    this.payload.department_ids = [];

    setTimeout(() => {
      this.getBranches();
      this.getScheduledEmployees();
    }, 3000);

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
    getPresentCount(item) {
      return [item?.p_count, item?.lc_count, item?.eg_count].reduce(
        (sum, val) => sum + (val || 0),
        0
      );
    },
    getAbsentCount(item) {
      return (
        [item?.a_count, item?.m_count].reduce(
          (sum, val) => sum + (val || 0),
          0
        ) - this.$utils.getRemainingDays()
      );
    },
    getOtherCount(item) {
      return [item?.l_count, item?.v_count, item?.h_count].reduce(
        (sum, val) => sum + (val || 0),
        0
      );
    },
    addFirstAndLastDay(months) {
      // Check if the user has selected exactly 2 months
      if (months.length === 1) {
        months = [months[0], months[0]];
      }

      // Get the first month
      const firstMonth = months[0];
      const [firstYear, firstMonthNum] = firstMonth.split("-");

      // Add the first day of the first month
      this.from_date = `${firstYear}-${firstMonthNum}-01`;

      // Get the last month
      const lastMonth = months[months.length - 1];
      const [lastYear, lastMonthNum] = lastMonth.split("-");

      // Calculate the last day of the last month
      const lastDayOfMonth = new Date(lastYear, lastMonthNum, 0).getDate();
      this.to_date = `${lastYear}-${lastMonthNum}-${lastDayOfMonth}`;
    },
    formatMonth(monthString) {
      // Split the string into year and month
      const [year, month] = monthString.split("-");

      // Create a Date object (use the first day of the month)
      const date = new Date(year, month - 1, 1);

      // Format the month name (e.g., "Jan", "Feb")
      const monthName = date.toLocaleString("default", { month: "short" });

      // Return the formatted string (e.g., "Jan 2025")
      return `${monthName} ${year}`;
    },
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeesSelection() {
      this.selectAllEmployees = !this.selectAllEmployees;
    },

    getDataFromApi() {
      if (this.$auth.user.user_type == "department") {
        this.payload.department_ids = [this.$auth.user.department_id];
      }

      let { page, itemsPerPage } = this.options;

      this.loading = true;

      this.$axios
        .post(`performance-report`, {
          ...this.payload,
          from_date: this.from_date,
          to_date: this.to_date,
          page: page,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          report_type: "monthly",
          filterType: this.filterType,
        })
        .then(({ data }) => {
          this.data = data.data;
          this.total = data.total;
          this.loading = false;
          this.totalRowsCount = data.total;
        });
    },

    getScheduledEmployees() {
      let options = {
        params: {
          per_page: 1000,
          branch_id: this.payload.branch_id,
          company_id: this.$auth.user.company_id,
          department_ids: this.payload.department_ids,
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
          this.getDataFromApi();
        }, 3000);
      } catch (error) {
        console.error("Error fetching departments:", error);
      }
    },
  },
};
</script>
