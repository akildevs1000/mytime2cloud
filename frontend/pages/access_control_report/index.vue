<template>
  <div v-if="can(`attendance_report_view`)">
    <v-card elevation="0" class="mt-2">
      <v-toolbar dense flat>
        <span class="headline black--text"> Access Control Reports </span>
      </v-toolbar>

      <v-card-text class="py-3">
        <v-row>
          <v-col md="2" sm="2" v-if="isCompany">
            Branch
            <v-select
              placeholder="Branch"
              class="mt-2"
              outlined
              dense
              v-model="payload.branch_id"
              x-small
              :items="[{ id: ``, branch_name: `Select All` }, ...branches]"
              item-value="id"
              item-text="branch_name"
              :hide-details="true"
            ></v-select>
          </v-col>
          <v-col md="2" sm="4">
            Employee ID
            <v-autocomplete
              density="comfortable"
              class="mt-2"
              outlined
              dense
              v-model="payload.UserID"
              x-small
              :items="[
                { system_user_id: ``, name_with_user_id: `Select All` },
                ...employees,
              ]"
              item-value="system_user_id"
              item-text="name_with_user_id"
              :hide-details="true"
            ></v-autocomplete>
          </v-col>
          <v-col md="2" sm="4">
            Devices
            <v-select
              class="mt-2"
              outlined
              dense
              v-model="payload.DeviceID"
              x-small
              :items="[{ device_id: ``, name: `Select All` }, ...devices]"
              item-value="device_id"
              item-text="name"
              :hide-details="true"
            ></v-select>
          </v-col>
          <v-col md="2" sm="5">
            <div class="mb-2">Date</div>
            <CustomFilter
              @filter-attr="filterAttr"
              :defaultFilterType="1"
              :height="'45px '"
            />
          </v-col>
          <v-col md="2" sm="2">
            <div class="mb-2">&nbsp;</div>
            <v-btn @click="getDataFromApi()" color="primary" primary fill
              >Generate
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <v-card class="mb-5 mt-5" elevation="0">
      <div v-if="can(`attendance_report_access`)">
        <div class="text-center">
          <v-snackbar
            v-model="snackbar"
            top="top"
            color="secondary"
            elevation="24"
          >
            {{ response }}
          </v-snackbar>
          <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
            {{ snackText }}

            <template v-slot:action="{ attrs }">
              <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
            </template>
          </v-snackbar>
        </div>
        <v-card class="mb-5" elevation="0">
          <v-toolbar class="backgrounds" dense flat>
            <v-toolbar-title> </v-toolbar-title>

            <v-spacer></v-spacer>
            <v-menu bottom right>
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon color="violet">mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list width="200" dense>
                <v-list-item @click="process_file('print_pdf')">
                  <v-list-item-title style="cursor: pointer">
                    <img src="/icons/icon_print.png" class="iconsize" />
                    Print
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="process_file('download_pdf')">
                  <v-list-item-title style="cursor: pointer">
                    <img src="/icons/icon_pdf.png" class="iconsize" />
                    PDF
                  </v-list-item-title>
                </v-list-item>

                <!-- <v-list-item @click="process_file(report_type + '_download_csv')">
              <v-list-item-title style="cursor: pointer">
                <img src="/icons/icon_excel.png" class="iconsize" />
                EXCEL
              </v-list-item-title>
            </v-list-item> -->
              </v-list>
            </v-menu>
          </v-toolbar>

          <v-data-table
            dense
            :headers="headers"
            :items="data"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }"
            class="elevation-1"
            model-value="data.id"
            :server-items-length="totalRowsCount"
            fixed-header
            :height="tableHeight"
          >
            <template v-slot:item.UserID="{ item }" style="padding: 0px">
              <v-row no-gutters>
                <v-col
                  md="2"
                  style="
                    padding: 3px;
                    padding-left: 0px;
                    width: 30px;
                    max-width: 30px;
                  "
                >
                  <v-img
                    style="
                      border-radius: 50%;
                      height: auto;
                      width: 30px;
                      max-width: 30px;
                    "
                    :src="
                      item.employee && item.employee.profile_picture
                        ? item.employee.profile_picture
                        : '/no-profile-image.jpg'
                    "
                  >
                  </v-img>
                </v-col>
                <v-col style="padding: 3px" md="8">
                  <strong>
                    {{ item.employee ? item.employee.full_name : "---" }}
                  </strong>
                  <div class="secondary-value">
                    {{ item.employee ? item.employee.employee_id : "---" }}
                  </div>
                </v-col>
              </v-row>
            </template>
          </v-data-table>
        </v-card>
      </div>
    </v-card>
  </div>

  <NoAccess v-else />
</template>
<script>
export default {
  props: [],

  data: () => ({
    tableHeight: 750,
    status: "",
    department_ids: "",
    employee_id: "",
    daily_date: "",
    to_date: "",
    report_type: "Monthly",

    isFilter: false,
    totalRowsCount: 0,
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    menu: false,
    options: {},
    date: null,
    menu: false,
    loading: false,
    time_menu: false,
    Model: "Attendance Reports",
    endpoint: "access_control_report",
    search: "",
    snackbar: false,
    add_manual_log: false,
    dialog: false,
    generateLogsDialog: false,
    reportSync: false,
    from_menu: false,
    to_menu: false,
    ids: [],
    departments: [],
    employees: [],
    DateRange: true,
    devices: [],

    loading: false,
    total: 0,

    payload: {
      from_date: null,
      to_date: null,
      daily_date: null,
      UserID: "",
      department_ids: [],
      status: "-1",
      DeviceID: "",
      branch_id: "",
      include_device_types: ["all", "Access Control"],
    },

    response: "",
    data: [],
    errors: [],
    report_template: "Template1",
    headers: [
      {
        text: "User Id /Emp.Id ",
        align: "left",
        sortable: true,
        key: "UserID",
        value: "UserID",
        width: "150px",
      },
      {
        text: "Date Range",
        align: "left",
        sortable: false,
        key: "date_range",
        value: "LogTime",
        fieldType: "date_range_picker",
      },
      {
        text: "Device Name",
        align: "left",
        sortable: true,
        key: "device",
        value: "device.name",
      },
      {
        text: "Device Location",
        align: "left",
        sortable: true,
        key: "device",
        value: "device.location",
      },
    ],
    max_date: null,

    isCompany: true,
    branches: [],
  }),

  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  mounted() {
    this.tableHeight = window.innerHeight - 370;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 370;
    });
  },
  created() {
    let branch_header = [
      {
        text: "Branch",
        align: "left",
        sortable: true,
        key: "branch_id", //sorting
        value: "employee.branch.branch_name", //edit purpose

        filterable: true,
        filterSpecial: true,
      },
    ];
    this.headers.splice(1, 0, ...branch_header);
    this.setFromDate();
    this.getBranches();
    this.getScheduledEmployees();
    this.getDeviceList();
  },
  methods: {
    filterAttr(data) {
      this.payload.from_date = data.from;
      this.payload.to_date = data.to;
      this.getDataFromApi();
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

    getScheduledEmployees() {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
          shift_type_id: this.shift_type_id,
        },
      };

      this.$axios
        .get(`/scheduled_employees_with_type`, options)
        .then(({ data }) => {
          this.employees = data;
        });
    },

    getDeviceList() {
      this.$axios
        .get(`/device_list`, {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.devices = data.filter((e) => !e.name.includes("Mobile"));
        });
    },

    setFromDate() {
      if (this.payload.from_date == null) {
        const dt = new Date();
        const y = dt.getFullYear();
        const m = dt.getMonth() + 1;
        const formattedMonth = m < 10 ? "0" + m : m;
        this.payload.from_date = `${y}-${formattedMonth}-01`;
      }
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

    getFirstAndLastDay() {
      const currentDate = new Date();
      const day = currentDate.getDate();
      const month = (currentDate.getMonth() + 1).toString().padStart(2, "0");
      const year = currentDate.getFullYear();
      const last = new Date(year, month, 0)
        .getDate()
        .toString()
        .padStart(2, "0");

      let firstDay = `${year}-${month}-0${1}`;

      let lastDayFirst = last > 9 ? `${last}` : `0${last}`;

      let lastDay = `${year}-${month}-${lastDayFirst}`;

      return [firstDay, lastDay];
    },

    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    async getDataFromApi() {
      if (!this.payload.from_date) return false;
      this.loading = true;
      const { data, total } = await this.$store.dispatch("fetchData", {
        key: "access_control_report",
        options: this.options,
        refresh: true,
        endpoint: this.endpoint,
        filters: this.payload,
      });
      this.data = data;
      this.totalRowsCount = total;
      this.loading = false;
      return;

      if (!this.payload.from_date) return false;
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      url = "access_control_report";
      this.loading = true;
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          ...this.payload,
        },
      };

      if (filter_column != "") options.params[filter_column] = filter_value;

      this.$axios.get(url, options).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }

        this.data = data.data;
        this.total = data.total;
        this.loading = false;
        this.totalRowsCount = data.total;
      });
    },

    pdfDownload() {
      let path = process.env.BACKEND_URL + "/pdf";
      let pdf = document.createElement("a");
      pdf.setAttribute("href", path);
      pdf.setAttribute("target", "_blank");
      pdf.click();
    },

    async process_file(type) {
      try {
        if (!this.data || !this.data.length) {
          alert("No data found");
          return;
        }

        const backendUrl = process.env.BACKEND_URL;
        const queryParams = {
          company_id: this.$auth.user.company_id,
          branch_id: this.payload.branch_id,
          UserID: this.payload.UserID,
          DeviceID: this.payload.DeviceID,
          from_date: this.payload.from_date,
          to_date: this.payload.to_date,
        };

        const queryString = Object.keys(queryParams)
          .map(
            (key) =>
              `${encodeURIComponent(key)}=${encodeURIComponent(
                queryParams[key]
              )}`
          )
          .join("&");

        const reportUrl = `${backendUrl}/access_control_report_${type.toLowerCase()}?${queryString}&include_device_types[]=all&include_device_types[]=Access Control`;

        const report = document.createElement("a");
        report.setAttribute("href", reportUrl);
        report.setAttribute("target", "_blank");
        report.click();
      } catch (error) {
        console.error("Error processing file:", error.message);
        // Handle the error (e.g., show an error message to the user)
      }
    },
  },
};
</script>
