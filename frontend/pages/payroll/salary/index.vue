<template>
  <div v-if="can(`employee_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogPayslipsResults" :fullscreen="false" width="700px">
      <v-card elevation="0">
        <v-toolbar color="background" dense flat dark>
          <span> Payslips Results</span>
        </v-toolbar>
        <v-divider class="py-0 my-0"></v-divider>
        <v-card-text>
          <v-container>
            <template>
              <table style="width: 100%" v-if="payslipsResultsmessages.length">
                <thead>
                  <tr>
                    <th class="text-left">#</th>

                    <th class="text-left">Status</th>
                    <th class="text-right">Download</th>
                  </tr>
                </thead>
                <tbody>
                  <tr :style="{
                    color: item.status ? '' : 'red',
                  }" v-for="(item, index) in payslipsResultsmessages" :key="item.id">
                    <td>{{ index + 1 }}</td>

                    <td>{{ item.status_message }}</td>
                    <td class="text-right">
                      <a v-if="item.status" :href="getdownloadLink(item.employee_id)" style="
                          font-size: 25px;
                          vertical-align: inherit;
                          cursor: pointer;
                        ">
                        <v-icon small class="primary--text">mdi-download</v-icon>
                      </a>
                      <a v-if="!item.status" @click="navigatetoEmployeepage()" style="
                          font-size: 14px;
                          vertical-align: inherit;
                          cursor: pointer;
                        ">
                        Go to Employee Page
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-else>No Employees are available</div>
            </template>

            <!-- <v-list lines="one">
              <v-list-item
                v-for="(item, index) in payslipsResultsmessages"
                :key="item.id"
                :title="item.status_message"
              >
                <span v-if="item.status"
                  >{{ index + 1 }}: {{ item.status_message }}</span
                >
                <span style="color: red" v-else
                  >{{ index + 1 }}: {{ item.status_message }}</span
                >
              </v-list-item>
            </v-list> -->
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogVisible" max-width="500px">
      <v-card flat dense class="white--text">
        <v-card-title class="background">
          <span class="headline">Filter</span>
        </v-card-title>
        <v-progress-linear v-if="filterLoader" indeterminate color="primary"></v-progress-linear>

        <br />

        <v-card-text>
          <v-autocomplete placeholder="Department" outlined dense @change="getDataFromApi(`employee`)"
            v-model="department_id" x-small :items="departments" item-value="id" item-text="name"></v-autocomplete>
          <v-autocomplete outlined dense @change="handleFilters" x-small item-value="id" item-text="name"
            v-model="payslip_year" :items="dataYears" placeholder="Year"></v-autocomplete>
          <v-autocomplete outlined dense @change="handleFilters" x-small v-model="payslip_month"
            :items="fitleredMonthNames()" item-text="label" item-value="value" placeholder="Month"></v-autocomplete>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn dark color="background" @click="dialogVisible = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="generatePayslipDialog" :fullscreen="false" width="600px">
      <v-card elevation="0">
        <v-toolbar color="background" dense flat dark>
          <span> Generate Payslips</span>
        </v-toolbar>
        <v-divider class="py-0 my-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row class="mt-4">
              <v-col cols="5">
                <div class="">Departments</div>
                <v-select outlined dense x-small v-model="department_idPopup" item-text="name" item-value="id"
                  :items="departments" placeholder="Department" solo flat></v-select>
              </v-col>

              <v-col cols="3">
                <div class="">Year</div>
                <v-select outlined dense x-small v-model="payslip_year_Popup" :items="dataYears" placeholder="Year" solo
                  flat></v-select>
              </v-col>
              <v-col cols="3">
                <div class="">Month</div>
                <v-select outlined dense x-small v-model="payslip_month_Popup" :items="monthNames" item-text="label"
                  item-value="value" placeholder="Month" solo flat></v-select>
              </v-col>

              <v-card-actions>
                <v-col md="6" lg="6" style="padding: 0px">
                  <!-- <v-btn class="error" @click="closePopup">
                    Cancel
                  </v-btn>-->
                </v-col>
                <v-col md="6" lg="6" class="text-right" style="padding: 0px">
                  <v-btn class="primary" @click="generateNewpayslipsByDepartment" style>Generate Payslips</v-btn>
                </v-col>
              </v-card-actions>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <div v-if="can(`employee_view`)">
      <v-row>
        <v-col>
          <v-card class="mb-5" elevation="0">
            <v-toolbar class="rounded-md mb-2 white--text" color="background" dense flat>
              <v-col cols="8">

                <span> Payslips</span>
                <v-icon @click="getDataFromApi()" class="mx-1 white--text">mdi-reload</v-icon>
                <v-icon @click="dialogVisible = true" class="mx-1 white--text">mdi-filter</v-icon>
                <v-btn v-if="selectedItems.length" @click="generateNewpayslipsSelected" small
                  class="primary toolbar-button-design1" color="primary">

                  Payslips For selected
                </v-btn>
              </v-col>
              <v-col cols="4" class="text-right">
                <v-icon @click="openPayslipDialog" class="mx-1 white--text">mdi-receipt</v-icon>
                <v-btn v-if="downloadAllDisplayStatus" download :href="payslipsDownloadAllURL" small dark
                  class="primary mx-1 toolbar-button-design1" color="primary">
                  All Payslips
                  <v-icon @click="showFilters = !showFilters" class="mx-1 white--text">mdi mdi-download</v-icon>
                </v-btn>
              </v-col>
            </v-toolbar>

            <!-- <v-data-table
              v-model="data"
              show-select
              item-key="id"
              :headers="headers_table"
              :items="data"
            >
              <template v-slot:item.imgUrl="{ item }">
                <v-img
                  style="
                    border-radius: 50%;
                    height: 50px;
                    width: 50px !important;
                  "
                  :src="item.profile_picture || '/no-profile-image.jpg'"
                ></v-img>
              </template>
            </v-data-table>
            <v-row>
              <v-col lg="12" class="float-right">
                <div class="float-right">
                  <v-pagination
                    v-model="pagination.current"
                    :length="pagination.total"
                    @input="onPageChange"
                    :total-visible="5"
                  ></v-pagination>
                </div>
              </v-col>
            </v-row>
 -->

            <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
              {{ snackText }}

              <template v-slot:action="{ attrs }">
                <v-btn v-bind="attrs" text @click="snack = false">
                  Close
                </v-btn>
              </template>
            </v-snackbar>
            <v-data-table dense show-select v-model="selectedItems" :headers="headers_table" :items="data"
              model-value="data.id" :loading="loading" :options.sync="options" :footer-props="{
                itemsPerPageOptions: [10, 50, 100, 500, 1000],
              }" class="elevation-1" :server-items-length="totalRowsCount">
              <template v-slot:item.employee_id="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%"
                  :return-value.sync="item.employee_id" @save="getDataFromApi()" @open="datatable_open">
                  {{ item.employee_id }}
                  <template v-slot:input>
                    <v-text-field @input="datatable_searchById" v-model="datatable_search_textbox"
                      label="Search Employee Id"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <!-- <template v-slot:item.display_name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                  :return-value.sync="item.employee_id" @save="getDataFromApi()" @open="datatable_open">
                  {{ item.first_name }} {{ item.last_name }}
                  <template v-slot:input>
                    <v-text-field @input="datatable_searchByName" v-model="datatable_search_textbox"
                      label="Search Employee Name"></v-text-field>
                  </template>
                </v-edit-dialog>




              </template> -->
              <template v-slot:item.display_name="{ item, index }" style="width: 300px">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%" @save="getDataFromApi()"
                  @open="datatable_open">
                  <v-row no-gutters>
                    <v-col style="
                        padding: 5px;
                        padding-left: 0px;
                        width: 50px;
                        max-width: 50px;
                      ">
                      <v-img style="
                          border-radius: 50%;
                          height: auto;
                          width: 50px;
                          max-width: 50px;
                        " :src="item.profile_picture
                          ? item.profile_picture
                          : '/no-profile-image.jpg'
                          ">
                      </v-img>
                    </v-col>
                    <v-col style="padding: 10px">
                      <strong>
                        {{ item.first_name ? item.first_name : "---" }}
                        {{ item.last_name ? item.last_name : "---" }}</strong>
                      <div>
                        {{
                          item.designation ? caps(item.designation.name) : "---"
                        }}
                      </div>
                    </v-col>
                  </v-row>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterEmployeeName" v-model="datatable_search_textbox"
                      label="Type Employee Name"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>

              <template v-slot:item.department.name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%" @save="getDataFromApi()"
                  @open="datatable_open">
                  <strong>{{ caps(item.department.name) }}</strong>
                  <div>{{ caps(item.sub_department.name) }}</div>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterDepartmentName" v-model="datatable_search_textbox"
                      label="Search Department name"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.year_month="{ item }">
                {{ item.payroll_month }} / {{ item.payroll_year }}
              </template>
              <!-- <template v-slot:item.designation.name="{ item }">


                <v-edit-dialog large save-text="Reset" cancel-text="Ok" @save="getDataFromApi()" @open="datatable_open">
                  {{ caps(item.designation && item.designation.name) }}
                  <template v-slot:input>
                    <v-text-field @input="datatable_searchByDesignationName" v-model="datatable_search_textbox"
                      label="Search Department Name"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <template v-slot:item.department.name="{ item }">


                <v-edit-dialog large save-text="Reset" cancel-text="Ok" @save="getDataFromApi()" @open="datatable_open">
                  {{ caps(item.department && item.department.name) }}
                  <template v-slot:input>
                    <v-text-field @input="datatable_searchByDepartmentName" v-model="datatable_search_textbox"
                      label="Search Department Name"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template> -->
              <template v-slot:item.payroll.basic_salary="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" @save="getDataFromApi()" @open="datatable_open">
                  {{ item.payroll && item.payroll.basic_salary }}
                  <template v-slot:input>
                    <v-text-field @input="datatable_searchBybasic_salary" v-model="datatable_search_textbox"
                      label="Minimum Amount"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>

              <template v-slot:item.payroll.net_salary="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" @save="getDataFromApi()" @open="datatable_open">
                  {{ item.payroll && item.payroll.net_salary }}
                  <template v-slot:input>
                    <v-text-field @input="datatable_searchBynet_salary" v-model="datatable_search_textbox"
                      label="Minimum Amount"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.payslip="{ item }">
                <span v-if="item?.payroll?.basic_salary" @click="navigateToViewPDF(item.id)" style="
                    font-size: 25px;
                    vertical-align: inherit;
                    cursor: pointer;
                  ">
                  <v-icon small class="primary--text">mdi-eye</v-icon>
                </span>
                <a v-if="item.payslip_status" :href="getdownloadLink(item.employee_id)" style="
                    font-size: 25px;
                    vertical-align: inherit;
                    cursor: pointer;
                  ">
                  <v-icon small class="primary--text">mdi-download</v-icon>
                </a>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item @click="editItem(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <!-- <v-list-item @click="res(item.id)">
                        <v-list-item-title style="cursor:pointer">
                          <v-icon color="primary" small>
                            mdi-eye
                          </v-icon>
                          Select
                        </v-list-item-title>
                      </v-list-item> -->
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>

            <!-- <table class="employee-table">
              <tr>
                <th>
                  <v-checkbox v-model="allSelected" primary hide-details @click="toggleSelectAll"></v-checkbox>
                </th>
                <th v-for="(item, index) in headers" :key="index">
                  {{ item.text }}
                </th>
              </tr>
              <v-progress-linear v-if="loading" :active="loading" :indeterminate="loading" absolute
                color="primary"></v-progress-linear>
              <tr v-for="(item, index) in data" :key="index">
                <td>
                  <v-checkbox v-model="selectedItems" :value="item.id" :key="item.id" primary hide-details></v-checkbox>
                </td>
                <td class="text-center">
                  <b>{{ ++index }}</b>
                </td>
                <td>{{ item.employee_id || "---" }}</td>
                <td>{{ item.display_name || "---" }}</td>
                <td>{{ payslip_year }}/{{ payslip_month }}</td>
                <td>{{ caps(item && item.designation.name) }}</td>
                <td>{{ caps(item.department && item.department.name) }}</td>
                <td>{{ item.payroll && item.payroll.basic_salary }}</td>
                <td>{{ item.payroll && item.payroll.net_salary }}</td>
                <td>


                  <span @click="navigateToViewPDF(item.system_user_id, item.id)" style="
                      font-size: 25px;
                      vertical-align: inherit;
                      cursor: pointer;
                    ">
                    <v-icon small class="primary--text">mdi-eye</v-icon>
                  </span>
                  <a v-if="item.payslip_status" :href="getdownloadLink(item.employee_id)" style="
                      font-size: 25px;
                      vertical-align: inherit;
                      cursor: pointer;
                    ">
                    <v-icon small class="primary--text">mdi-download</v-icon>
                  </a>
                </td>
                <td>
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="editItem(item)">
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small> mdi-pencil </v-icon>
                          Edit
                        </v-list-item-title>
                      </v-list-item>

                    </v-list>
                  </v-menu>
                </td>
              </tr>
            </table> -->
          </v-card>
          <!-- <v-row>
            <v-col lg="12" class="float-right">
              <div class="float-right">
                <v-pagination v-model="pagination.current" :length="pagination.total" @input="onPageChange"
                  :total-visible="5"></v-pagination>
              </div>
            </v-col>
          </v-row> -->
        </v-col>
      </v-row>
      <div></div>
    </div>
    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    totalRowsCount: 0,
    //server_datatable_totalItems: 1000,
    dialogVisible: false,
    server_datatable_totalItems: 1000,
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    downloadAllDisplayStatus: true,
    dialogPayslipsResults: false,
    payslipsResultsmessages: [],
    payslipsDownloadAllEmployeeidsArray: [],
    payslipsDownloadAllURL: "",
    items: [],
    dataYears: [],
    tab: null,
    pagination: {
      current: 1,
      total: 0,
      per_page: 100,
    },
    options: {},
    Model: "Payroll",
    endpoint: "employee",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    paymentMethod: ["Bank Transfer", "Cash", "Cheque"],
    Allowance: [
      "Transport",
      "Travel",
      "Entertainment",
      "Housing",
      "Uniform",
      "Uniform",
      "Medical/health",
    ],
    headers: [
      { text: "#" },
      { text: "E.ID" },
      { text: "Name" },
      { text: "Month/Year" },
      { text: "Designation" },
      { text: "Department" },
      { text: "Basic Salary" },
      { text: "Net Salary" },
      { text: "Payslip" },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    headers_table: [
      {
        text: "Emp ID",
        align: "left",
        sortable: true,
        key: "employee_id",
        value: "employee_id",
      },
      {
        text: "Name",
        align: "left",
        sortable: false,
        key: "display_name",
        value: "display_name",
      },

      {
        text: "Department",
        align: "left",
        sortable: false,
        key: "department",
        value: "department.name", //template name should be match
      },

      {
        text: "Month/Year",
        align: "left",
        sortable: false,
        key: "year_month",
        value: "year_month",
      },
      {
        text: "Basic Salary",
        align: "left",
        sortable: false,
        key: "payrollbasic",
        value: "payroll.basic_salary",
      },
      {
        text: "Net Salary",
        align: "left",
        sortable: false,
        key: "net_salary",
        value: "payroll.net_salary",
      },
      {
        text: "Payslip",
        align: "left",
        sortable: false,
        key: "payslip",
        value: "payslip",
      },
      {
        text: "Actions",
        align: "left",
        sortable: false,
        key: "actions",
        value: "actions",
      },
    ],
    datatable_search_textbox: "",
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    departments: [],
    department_id: "",
    department_idPopup: "",
    work: {
      first_name: "",
      last_name: "",
      department: "",
      sub_department: "",
      designation: "",
      role: "",
      employee_id: "",
      system_user_id: "",
      user: "",
      profile_picture: "",
      phone_number: "",
      whatsapp_number: "",
      joining_date: "",
    },
    BankInfo: {
      bank_name: "",
      account_no: "",
      account_title: "",
      iban: "",
      address: "",
      remark: "",
      company_id: "",
      employee_id: "",
    },
    salary: {
      basic_salary: "",
      payment_method: "",
      remark: "",
    },
    allowance: {
      name: "",
      amount: "",
      remark: "",
    },
    generatePayslipDialog: false,
    payslip_year: new Date().getFullYear(),
    payslip_month: new Date().getMonth(),
    payslip_year_Popup: new Date().getFullYear(),
    payslip_month_Popup: new Date().getMonth(),

    selectedItems: [],
    allSelected: false,
    monthNames: [
      { value: 1, label: "January" },
      { value: 2, label: "February" },
      { value: 3, label: "March" },
      { value: 4, label: "April" },
      { value: 5, label: "May" },
      { value: 6, label: "June" },
      { value: 7, label: "July" },
      { value: 8, label: "August" },
      { value: 9, label: "September" },
      { value: 10, label: "October" },
      { value: 11, label: "November" },
      { value: 12, label: "December" },
    ],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    },
    selectAll() {
      return this.selectedItems.length === this.items.length;
    },
    indeterminate() {
      return (
        this.selectedItems.length > 0 &&
        this.selectedItems.length < this.items.length
      );
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    department_id() {
      this.pagination.current = 1;
      this.getDataFromApi();
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
    this.getDepartments();
    this.lastTenYears();
  },
  mounted() {
    this.getDataFromApi();
  },

  methods: {
    fitleredMonthNames() {
      let dt = new Date();
      let py = this.payslip_year;
      let mns = this.monthNames;
      return mns.slice(0, py == dt.getFullYear() ? dt.getMonth() : 12);
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
    navigatetoEmployeepage() {
      this.$router.push("/employees");
    },
    downloadAllPayslipsError() {
      this.snackbar = true;

      this.response =
        "Payslips are not available. Please generate and try again";
      return false;
    },
    getdownloadLink(employee_id) {
      const baseURL = this.$axios.defaults.baseURL;
      const id = this.$auth.user.company.id;
      const month = this.payslip_month;
      const year = this.payslip_year;

      return `${baseURL}/render-payslip-by-employee?company_id=${id}&employee_id=${employee_id}&month=${month}&year=${year}`;
    },
    handleFilters() {
      this.selectedItems = [];
      this.allSelected = false;
      this.fitleredMonthNames();
      this.getDataFromApi();
    },

    navigateToViewPDF(id) {
      let path = `/payroll/salary/${id}_${this.payslip_month}_${this.payslip_year}`;
      this.$router.push(path);
    },

    lastTenYears() {
      const year = new Date().getFullYear();
      this.dataYears = Array.from({ length: 10 }, (_, i) => year - i);
    },
    toggleSelectAll() {
      this.selectedItems = this.allSelected ? this.data.map((e) => e.id) : [];
    },
    onPageChange() {
      this.getDataFromApi();
    },
    caps(str) {
      if (str == "" || str == null) {
        return "";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    can(per) {
      let { permissions, is_master } = this.$auth.user;

      return permissions.some((e) => e.name == per || per == "/") || is_master;
    },
    res(id) {
      this.$axios.get(`employee/${id}`).then(({ data }) => {
        this.work = { ...data };
        this.getBankInfo(data.employee_id);
      });
    },
    getBankInfo(id) {
      this.$axios.get(`bankinfo/${id}`).then(({ data }) => {
        this.BankInfo = {
          ...data,
        };
      });
    },

    getDepartments() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
        this.departments.unshift({ name: "All Departments", id: "" });
      });
    },
    generateNewpayslipsByDepartment() {
      let url = this.endpoint;
      let options = {
        params: {
          company_id: this.$auth.user.company.id,
          department_id: this.department_idPopup,

          month: this.payslip_month_Popup,
          year: this.payslip_year_Popup,
        },
      };
      //localhost:8001/api/payslip-by-department/80

      this.$axios.get(`payslip-by-department`, options).then(({ data }) => {
        this.payslipsResultsmessages = [];
        this.data = data.data;
        this.dialogPayslipsResults = true;
        this.payslipsResultsmessages = data;
        this.snackbar = true;
        //data.status;
        //this.ids = [];
        this.response = "Payslips will be Genereated by selected department.";
        // this.pagination.current = data.current_page;
        // this.pagination.total = data.last_page;
        // this.loading = false;

        this.getDataFromApi();
      });
    },

    generateNewpayslipsSelected() {
      let checkedIdArray = [];
      this.selectedItems.forEach((element) => {
        checkedIdArray.push(element.id);
      });
      let url = this.endpoint;
      let options = {
        params: {
          company_id: this.$auth.user.company.id,
          employee_ids: checkedIdArray,

          year: this.payslip_year,
          month: this.payslip_month,
        },
      };
      this.$axios
        .get(`/generate-payslips-with-employeeids`, options)
        .then(({ data }) => {
          this.payslipsResultsmessages = [];
          this.dialogPayslipsResults = true;
          this.payslipsResultsmessages = data;

          this.snackbar = true;

          this.response = "Payslips Generated successfully";
          //this.selectedItems = [];
          //this.allSelected = false;
          this.getDataFromApi();
        });
    },
    getDataFromApi(url = this.endpoint, search_column_name = "") {
      this.loading = true;

      let department_id = this.department_id;
      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";


      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          department_id: department_id,
          year: this.payslip_year,
          month: this.payslip_month,
        },
      };

      if (search_column_name != "") {
        options.params.per_page = 1000;
      }

      if (search_column_name == "search_department_name") {
        options.params.search_department_name = "search_department_name";
      } else if (search_column_name == "search_designation_name") {
        options.params.search_designation_name = "search_designation_name";
      } else if (search_column_name == "searchBybasic_salary") {
        options.params.searchBybasic_salary = "searchBybasic_salary";
      } else if (search_column_name == "searchBynet_salary") {
        options.params.searchBynet_salary = "searchBynet_salary";
      } else options.params.search_column_name = search_column_name;

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        if (search_column_name != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }

        this.data = data.data;
        this.totalRowsCount = data.total;
        //this.server_datatable_totalItems = data.total;

        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
        this.toggleSelectAll();
        //this.allSelected = [];
        this.selectedItems = [];
        this.payslipsDownloadAllEmployeeidsArray = [];
        this.data.forEach((element) => {
          if (element.payslip_status)
            this.payslipsDownloadAllEmployeeidsArray.push(element.employee_id);
        });

        if (this.payslipsDownloadAllEmployeeidsArray.length > 0) {
          this.payslipsDownloadAllURL =
            this.$axios.defaults.baseURL +
            "/generate-payslips-zip?company_id=" +
            this.$auth.user.company.id +
            "&employee_ids=" +
            this.payslipsDownloadAllEmployeeidsArray +
            "&month=" +
            this.payslip_month +
            "&year=" +
            this.payslip_year;

          this.downloadAllDisplayStatus = true;
        } else {
          this.downloadAllDisplayStatus = false;
        }
      });

      this.loading = true;

      // options = {
      //   params: {
      //     per_page: this.pagination.per_page,
      //     company_id: this.$auth.user.company.id,
      //     department_id: department_id,
      //     year: this.payslip_year,
      //     month: this.payslip_month,
      //   },
      // };

      // this.$axios
      //   .get("generate-payslips-companyid/8", options)
      //   .then(({ data }) => {
      //     this.payslipsResultsmessages = data;
      //   });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },
    datatable_searchById(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(
          `${this.endpoint}/searchby_emp_table_salary/${e}`,
          "employee_id"
        );
      }
    },
    datatable_searchByName(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(
          `${this.endpoint}/searchby_emp_table_salary/${e}`,
          "display_name"
        );
      }
    },
    datatable_searchByDepartmentName(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(
          `${this.endpoint}/searchby_emp_table_salary/${e}`,
          "search_department_name"
        );
      }
    },
    datatable_searchByDesignationName(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(
          `${this.endpoint}/searchby_emp_table_salary/${e}`,
          "search_designation_name"
        );
      }
    },
    datatable_searchBybasic_salary(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(
          `${this.endpoint}/searchby_emp_table_salary/${e}`,
          "searchBybasic_salary"
        );
      }
    },

    datatable_searchBynet_salary(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(
          `${this.endpoint}/searchby_emp_table_salary/${e}`,
          "searchBynet_salary"
        );
      }
    },

    editItem(item) {
      //this.$router.push(`/employees/${item.id}?id=${item.id}`);

      this.$router.push({ path: "/employees?id=1", params: { id: item.id } });
    },

    delteteSelectedRecords() {
      let just_ids = this.ids.map((e) => e.id);
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: just_ids,
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
          })
          .catch((err) => console.log(err));
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      let salary = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, salary)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              const index = this.data.findIndex(
                (item) => item.id == this.editedItem.id
              );
              this.data.splice(index, 1, {
                id: this.editedItem.id,
                name: this.editedItem.name,
              });
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, salary)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((res) => console.log(res));
      }
    },

    openPayslipDialog() {
      this.generatePayslipDialog = true;
    },
    closePopup() { },
  },
};
</script>
