<template>
  <span>
    <style scoped>
      .v-data-table {
        padding: 5px !important;
        border: none;
      }
      .v-data-table-header th {
        border-top: 1px solid #bdbdbd !important; /* Fix color code */
        border-bottom: 1px solid #bdbdbd !important; /* Fix color code */
        color: #4390fc !important; /* Change text color */
        font-weight: normal !important;
        padding-top: 10px !important; /* Add space above text */
        padding-bottom: 10px !important; /* Add space below text */
      }
    </style>
    <v-card class="mt-5 pa-2" elevation="0">
      <v-toolbar flat dense>
        <v-toolbar-title
          style="font-size: 18px; font-weight: 600; width: 200px"
        >
          Summary Reports
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
            primary
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
            <v-tab
              @click="downloadPDF"
              style="height: 30px"
              class="black--text slidegroup1"
            >
              Download
            </v-tab>
          </v-tabs>
        </v-card>
      </v-col>
      <v-col cols="12">
        <v-data-table
          dense
          :headers="summaryReportHeaders"
          :items="data"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [100, 200, 500],
            page: true,
          }"
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
                <div style="font-size: 13px">
                  <small style="font-size: 12px; color: #6c7184">
                    {{ item?.employee?.department?.name || "---" }}
                  </small>
                </div>
              </div>
            </div>
          </template>
          <template v-slot:item.p_count="{ item }">
            <span class="green--text">{{ item.p_count }}</span>
          </template>
          <template v-slot:item.a_count="{ item }">
            <span class="red--text">{{ item.p_count }}</span>
          </template>
          <template v-slot:item.l_count="{ item }">
            <span class="orange--text">{{ item.p_count }}</span>
          </template>
          <template v-slot:item.average_in_time_array="{ item }">
            {{ calculateAverageTime(item.average_in_time_array) }}
          </template>

          <template v-slot:item.average_out_time_array="{ item }">
            {{ calculateAverageTime(item.average_out_time_array) }}
          </template>
          <template v-slot:item.average_working_hrs_array="{ item }">
            {{
              calculatePerDayHours(
                item.total_hrs_array,
                item?.employee?.schedule_all || []
              )
            }}
          </template>
          <template v-slot:item.total_hrs_array="{ item }">
            <b> {{ calculateTotalHrs(item.total_hrs_array) }}</b> /
            {{ calculateHrsToPerform(item?.employee?.schedule_all || []) }}
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </span>
</template>

<script>
export default {
  props: ["title", "render_endpoint", "process_file_endpoint"],

  data: () => ({
    key: 1,
    selectAllDepartment: false,
    selectAllEmployees: false,
    branches: [],
    tab: null,
    summaryReportHeaders: [
      {
        text: "Employee",
        align: "left",
        sortable: false,
        filterable: true,
        value: "employee_name",
        key: "employee_name",
      },
      {
        text: "Present",
        align: "center",
        sortable: false,
        filterable: true,
        value: "p_count",
      },
      {
        text: "Absent",
        align: "center",
        sortable: false,
        filterable: true,
        value: "a_count",
      },
      {
        text: "Leave",
        align: "center",
        sortable: false,
        filterable: true,
        value: "l_count",
      },
      {
        text: "Avg CheckIn",
        align: "center",
        sortable: false,
        filterable: true,
        value: "average_in_time_array",
      },
      {
        text: "Avg CheckOut",
        align: "center",
        sortable: false,
        filterable: true,
        value: "average_out_time_array",
      },
      {
        text: "Late In",
        align: "center",
        sortable: false,
        filterable: true,
        value: "lc_count",
      },
      {
        text: "Early Out",
        align: "center",
        sortable: false,
        filterable: true,
        value: "eg_count",
      },
      {
        text: "Avg Working Hrs",
        align: "center",
        sortable: false,
        filterable: true,
        value: "average_working_hrs_array",
      },
      {
        text: "Working Hrs",
        align: "center",
        sortable: false,
        filterable: true,
        value: "total_hrs_array",
      },
    ],
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
    async downloadPDF() {
      try {
        // Ensure company_id is available
        const companyId = this.$auth?.user?.company_id;
        if (!companyId) {
          console.error("Company ID is missing.");
          return;
        }

        const queryParams = new URLSearchParams({
          ...this.payload,
          from_date: this.from_date,
          to_date: this.to_date,
          page: this.options.page,
          per_page: this.options.itemsPerPage,
          company_id: this.$auth.user.company_id,
          report_type: "monthly",
          months: JSON.stringify([...new Set(this.months)]),
        });

        // Construct the URL
        // const baseUrl = "http://127.0.0.1:5500/index.html";
        const baseUrl = "https://mytime2cloud-summary-report.netlify.app/";

        const url = `${baseUrl}?${queryParams.toString()}`;

        // Open in a new tab
        const newWindow = window.open(url, "_blank");

        // Handle blocked pop-ups
        if (!newWindow) {
          console.error("Popup blocked! Allow pop-ups for this site.");
        }
      } catch (error) {
        console.error("Error generating PDF URL:", error);
      }
    },
    calculatePerDayHours(timesString, schedules) {
      const times = JSON.parse(timesString);

      if (!Array.isArray(times) || times.length === 0) {
        return "00";
      }

      const totalWorkingDays =
        schedules?.reduce((acc, schedule) => {
          return (
            acc +
            this.countSelectedWeekdaysInMonth(
              [...new Set(this.months)],
              schedule?.shift?.days
            )
          );
        }, 0) ?? 0;

      // its giving error like map not function
      if (times && times.length) {
        let totalMinutes = times.map((time) => {
          let [hours, minutes] = time.split(":").map(Number);
          return hours * 60 + minutes;
        });

        // Calculate the average in minutes
        let avgMinutes = Math.floor(totalMinutes.reduce((a, b) => a + b, 0));

        // Convert back to HH:MM format
        let avgHours = Math.floor(avgMinutes / 60);
        return (avgHours / totalWorkingDays).toFixed(2); // Rounds to 2 decimal places
      }
    },
    calculateAverageTime(timesString) {
      const times = JSON.parse(timesString);

      if (!Array.isArray(times) || times.length === 0) {
        console.error(
          "🚨 Error: times is not an array or is empty:",
          " type " + typeof times,
          times
        );
        return "0"; // Default value when times is invalid
      }

      // its giving error like map not function
      if (times && times.length) {
        let totalMinutes = times.map((time) => {
          console.log("🚀 ~ totalMinutes ~ time:", time);
          let [hours, minutes] = time.split(":").map(Number);
          return hours * 60 + minutes;
        });

        // Calculate the average in minutes
        let avgMinutes = Math.floor(
          totalMinutes.reduce((a, b) => a + b, 0) / times.length
        );

        // Convert back to HH:MM format
        let avgHours = Math.floor(avgMinutes / 60);
        let avgMins = avgMinutes % 60;

        // Format to 2-digit HH:MM
        return `${avgHours.toString().padStart(2, "0")}:${avgMins
          .toString()
          .padStart(2, "0")}`;
      }
    },
    calculateTotalHrs(timesString) {
      const times = JSON.parse(timesString);

      if (!Array.isArray(times) || times.length === 0) {
        return "0";
      }

      // its giving error like map not function
      if (times && times.length) {
        let totalMinutes = times.map((time) => {
          let [hours, minutes] = time.split(":").map(Number);
          return hours * 60 + minutes;
        });

        // Calculate the average in minutes
        let avgMinutes = Math.floor(totalMinutes.reduce((a, b) => a + b, 0));

        // Convert back to HH:MM format
        let avgHours = Math.floor(avgMinutes / 60);
        // Format to 2-digit HH:MM
        return `${avgHours.toString().padStart(2, "0")}`;
      }
    },
    calculateHrsToPerform(schedules) {
      const result =
        schedules?.reduce((acc, schedule) => {
          const workingHours =
            parseInt(schedule?.shift?.working_hours, 10) || 0;

          return (
            acc +
            workingHours *
              this.countSelectedWeekdaysInMonth(
                [...new Set(this.months)],
                schedule?.shift?.days
              )
          );
        }, 0) ?? 0;

      return result;
    },
    countSelectedWeekdaysInMonth(months, weekdays) {
      const weekdaysMap = {
        Sun: 0,
        Mon: 1,
        Tue: 2,
        Wed: 3,
        Thu: 4,
        Fri: 5,
        Sat: 6,
      };

      // Count the selected weekdays across multiple months
      let totalCount = 0;

      months.forEach((month) => {
        const date = new Date(month + "-01"); // Use the first day of the month
        const year = date.getFullYear();
        const monthIndex = date.getMonth(); // Get month index (0 for January, 1 for February, etc.)

        // Get the number of days in the month
        const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();

        // Count how many of the selected weekdays occur in the month
        for (let day = 1; day <= daysInMonth; day++) {
          const currentDate = new Date(year, monthIndex, day);
          const dayOfWeek = currentDate.getDay(); // 0 = Sunday, 1 = Monday, etc.

          // Check if the current day's weekday is in the weekdays array
          if (weekdays.some((weekday) => weekdaysMap[weekday] === dayOfWeek)) {
            totalCount++;
          }
        }
      });

      return totalCount;
    },
    addFirstAndLastDay(months) {
      // Check if the user has selected exactly 2 months
      if (months.length !== 2) {
        // Do nothing and wait for the user to select 2 dates
        return;
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
