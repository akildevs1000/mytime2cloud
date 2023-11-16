<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
    </div>
    <v-dialog persistent v-model="editDialog" width="1250" :key="employeeId">
      <v-card>
        <v-tabs
          v-model="tab"
          class="popup_background"
          centered
          icons-and-text
          color="violet"
        >
          <v-tabs-slider></v-tabs-slider>

          <v-tab
            v-for="(item, index) in tabMenu"
            :key="index"
            :href="item.value"
          >
            {{ item.text }}
            <v-icon>{{ item.icon }}</v-icon>
          </v-tab>
          <v-icon
            @click="editDialog = false"
            style="margin-right: 4px"
            text-right
            outlined
            dark
            color="black"
          >
            mdi mdi-close-circle
          </v-icon>
        </v-tabs>

        <v-card-text>
          <v-tabs-items v-model="tab">
            <v-tab-item
              v-for="(tb, index) in tabMenu"
              :key="index"
              :value="`${index}`"
            >
              <component
                :is="getComponent(tab)"
                :employeeId="employeeId"
                @close-popup="editDialog = false"
                @eventFromchild="getDataFromApi()"
              />
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div v-if="!loading">
      <div class="text-center">
        <v-dialog v-model="viewDialog" width="80%" :key="employeeId">
          <v-card>
            <v-card-title dense>
              Employee Information
              <v-spacer></v-spacer>
              <v-icon @click="viewDialog = false" outlined dark color="black">
                mdi mdi-close-circle
              </v-icon>
            </v-card-title>
            <v-card-text>
              <EmployeeProfileView
                :table_id="employeeId"
                :employee_id="employee_id"
                :system_user_id="system_user_id"
              />
            </v-card-text>
          </v-card>
        </v-dialog>
      </div>

      <v-card elevation="0" class="mt-2">
        <v-toolbar class="mb-2 white--text" color="white" dense flat>
          <v-toolbar-title>
            <span style="color: black"> {{ Model }}s </span></v-toolbar-title
          >
          <v-btn
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            title="Filter"
          >
            <v-icon @click="toggleFilter" class="mx-1 ml-2"
              >mdi mdi-filter</v-icon
            >
          </v-btn>
          <v-spacer></v-spacer>
          <ImportEntity @success="(e) => getDataFromApi()" />
          <ExportEntity :data="data.data" />
          <CreateEntity @success="(e) => getDataFromApi()" />
        </v-toolbar>
        <v-data-table
          dense
          v-model="selectedItems"
          :headers="headers_table"
          :items="data.data"
          model-value="data.id"
          :loading="loadinglinear"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [100, 500, 1000],
          }"
          class="elevation-1"
          :server-items-length="data.total"
        >
          <template v-slot:header="{ props: { headers } }">
            <tr v-if="isFilter">
              <td v-for="header in headers" :key="header.text">
                <v-container style="padding-left: 0px !important">
                  <v-text-field
                    clearable
                    @click:clear="
                      filters[header.value] = '';
                      getDataFromApi();
                    "
                    :hide-details="true"
                    v-if="header.filterable && !header.filterSpecial"
                    v-model="filters[header.value]"
                    :id="header.value"
                    @input="getDataFromApi()"
                    outlined
                    dense
                    autocomplete="off"
                  ></v-text-field>

                  <v-select
                    clearable
                    @click:clear="
                      filters[header.value] = '';
                      getDataFromApi();
                    "
                    :id="header.key"
                    :hide-details="true"
                    v-if="
                      header.filterSpecial &&
                      header.value == 'department_name_id'
                    "
                    outlined
                    dense
                    small
                    v-model="filters[header.key]"
                    item-text="name"
                    item-value="id"
                    :items="[
                      { name: `All Departments`, id: `` },
                      ...departments,
                    ]"
                    placeholder="Department"
                    solo
                    flat
                    @change="getDataFromApi()"
                  ></v-select>
                  <v-select
                    clearable
                    @click:clear="
                      filters[header.value] = '';
                      getDataFromApi();
                    "
                    :id="header.key"
                    :hide-details="true"
                    v-if="
                      header.filterSpecial &&
                      header.value == 'branch.branch_name'
                    "
                    outlined
                    dense
                    small
                    v-model="filters[header.key]"
                    item-text="name"
                    item-value="id"
                    :items="[
                      { name: `All Branches`, id: `` },
                      ...branches_list,
                    ]"
                    placeholder="All Branches"
                    solo
                    flat
                    @change="applyFilters(filters[header.key])"
                  ></v-select>
                  <v-select
                    clearable
                    @click:clear="
                      filters[header.value] = '';
                      getDataFromApi();
                    "
                    :id="header.key"
                    :hide-details="true"
                    v-if="
                      header.filterSpecial && header.value == 'timezone.name'
                    "
                    outlined
                    dense
                    small
                    v-model="filters[header.key]"
                    item-text="name"
                    item-value="id"
                    :items="[
                      {
                        name: `All Timezones`,
                        id: ``,
                      },
                      ...timezones,
                    ]"
                    placeholder="Timezone"
                    solo
                    flat
                    @change="getDataFromApi()"
                  ></v-select>
                </v-container>
              </td>
            </tr>
          </template>
          <template v-slot:item.employee_id="{ item }">
            <strong>{{ item.employee_id }} </strong><br /><span
              style="font-size: 12px"
              >{{ item.system_user_id }}</span
            >
          </template>

          <template
            v-slot:item.first_name="{ item, index }"
            style="width: 300px"
          >
            <v-row no-gutters>
              <v-col
                style="
                  padding: 5px;
                  padding-left: 0px;
                  width: 50px;
                  max-width: 50px;
                "
              >
                <v-img
                  style="
                    border-radius: 50%;
                    height: auto;
                    width: 50px;
                    max-width: 50px;
                  "
                  :src="
                    item.profile_picture
                      ? item.profile_picture
                      : '/no-profile-image.jpg'
                  "
                >
                </v-img>
              </v-col>
              <v-col style="padding: 10px">
                <strong>
                  {{ item.first_name ? item.first_name : "---" }}
                  {{ item.last_name ? item.last_name : "---" }}</strong
                >
                <div class="secondary-value">
                  {{ item.designation ? caps(item.designation.name) : "---" }}

                  {{
                    item.user.role && item.user.role.name != "---"
                      ? "(Role:" + caps(item.user.role.name) + ")"
                      : ""
                  }}

                  <!-- {{
                    item.user.branch_login &&
                    "(" + item.user.branch_login.branch_name + ")"
                  }} -->
                </div>
              </v-col>
            </v-row>
          </template>

          <template v-slot:item.branch.branch_name="{ item }">
            {{ caps(item.branch && item.branch.branch_name) }}
            <div class="secondary-value">
              {{ item.user.branch_login && "(Branch Owner)" }}
            </div>
          </template>
          <template v-slot:item.department_name_id="{ item }">
            <strong>{{ caps(item.department.name) }}</strong>
            <div>{{ caps(item.sub_department.name) }}</div>
          </template>
          <template v-slot:item.phone_number="{ item }">
            {{ item.phone_number }}
          </template>
          <template v-slot:item.user.email="{ item }" style="width: 200px">
            {{ item.user.email }}
          </template>
          <template v-slot:item.timezone.name="{ item }">
            {{ item.timezone ? item.timezone.timezone_name : "" }}
          </template>
          <template v-slot:item.options="{ item }">
            <v-menu bottom left>
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list width="120" dense>
                <v-list-item
                  v-if="can('employee_profile_view')"
                  @click="viewItem(item)"
                >
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="secondary" small> mdi-eye </v-icon>
                    View
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  v-if="can('employee_edit')"
                  @click="editItem(item)"
                >
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="secondary" small> mdi-pencil </v-icon>
                    Edit
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  v-if="can('employee_delete')"
                  @click="deleteItem(item)"
                >
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="error" small> mdi-delete </v-icon>
                    Delete
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-data-table>
      </v-card>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
import CreateEntity from "../../components/Snippets/Host/Create.vue";
import ExportEntity from "../../components/Snippets/Host/Export.vue";
import ImportEntity from "../../components/Snippets/Host/Import.vue";

import SearchEntity from "../../components/Snippets/Common/Search.vue";

import EmployeeEdit from "../../components/employee/EmployeeEdit.vue";
import Contact from "../../components/employee/Contact.vue";
import Passport from "../../components/employee/Passport.vue";
import Emirates from "../../components/employee/Emirates.vue";
import Visa from "../../components/employee/Visa.vue";
import Bank from "../../components/employee/Bank.vue";
import Document from "../../components/employee/Document.vue";
import Qualification from "../../components/employee/Qualification.vue";
import Setting from "../../components/employee/Setting.vue";
import Payroll from "../../components/employee/Payroll.vue";
import Login from "../../components/employee/Login.vue";

import EmployeeProfileView from "../../components/EmployeesLogin/EmployeeLanding.vue";

import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
const compList = [
  EmployeeEdit,
  Contact,
  Passport,
  Emirates,
  Visa,
  Bank,
  Document,
  Qualification,
  Setting,
  Payroll,
  Login,
];

export default {
  components: {
    VueCropper,
    EmployeeProfileView,
    CreateEntity,
    ExportEntity,
    ImportEntity,
    SearchEntity,
  },

  data: () => ({
    refresh: true,
    id: "",
    employee_id: "",
    system_user_id: "",
    shifts: [],
    timezones: [],
    joiningDate: null,
    joiningDateMenuOpen: false,
    showFilters: false,
    filters: {},
    isFilter: false,
    sortBy: "employee_id",
    sortDesc: false,
    server_datatable_totalItems: 1000,
    snack: false,
    snackColor: "",
    snackText: "",
    selectedItems: [],
    datatable_search_textbox: "",
    datatable_searchById: "",
    loadinglinear: true,
    displayErrormsg: false,
    image: "",
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    compList,
    comp: "EmployeeEdit",
    tabMenu: [],
    tab: "0",
    employeeId: 0,
    employee_id: 0,
    employeeObject: {},
    attrs: [],
    dialog: false,
    editDialog: false,
    viewDialog: false,
    selectedFile: "",
    employeeDialog: false,
    m: false,
    expand: false,
    expand2: false,
    boilerplate: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,
    on: "",
    files: "",
    loading: false,
    //total: 0,
    per_page: 1000,
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    employee: {
      title: "Mr",
      display_name: "",
      employee_id: "",
      system_user_id: "",
    },
    upload: {
      name: "",
    },
    previewImage: null,
    payload: {},
    personalItem: {},
    contactItem: {},
    emirateItems: {},
    setting: {},
    options: {},
    Model: "Employee",
    endpoint: "employee",
    snackbar: false,
    ids: [],
    loading: false,
    //total: 0,
    headers: [],
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    departments: [],
    sub_departments: [],
    designations: [],
    roles: [],
    department_filter_id: "",
    dialogVisible: false,
    payloadOptions: {},
    headers_table: [
      {
        text: "Emp Id / Device Id",
        align: "left",
        sortable: true,
        key: "employee_id",
        value: "employee_id",
        filterable: true,
        width: "150px",
        filterSpecial: false,
      },
      {
        text: "Name",
        align: "left",
        sortable: true,
        key: "first_name",
        value: "first_name",
        width: "300px",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Department",
        align: "left",
        sortable: true,
        key: "department_name_id",
        value: "department_name_id", //template name should be match for sorting sub table should be the same
        width: "200px",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Mobile",
        align: "left",
        sortable: true,
        key: "mobile",
        value: "phone_number", // search and sorting enable if value matches with template name
        width: "150px",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Email",
        align: "left",
        sortable: true,
        key: "email",
        value: "user.email",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Timezone",
        align: "left",
        sortable: true,
        key: "timezone",
        value: "timezone.name",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Options",
        align: "left",
        sortable: false,
        key: "options",
        value: "options",
      },
    ],
    branches_list: [],
    branch_id: null,
    isCompany: true,
    import_branch_id: "",
  }),

  async created() {
    this.loading = false;
    this.boilerplate = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.employee.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      await this.getDepartments(this.branch_id);
      return;
    }
    this.headers_table.splice(2, 0, {
      text: "Branch",
      align: "left",
      sortable: true,
      key: "branch_id",
      value: "branch.branch_name",
      filterable: true,
      filterSpecial: true,
    });

    this.branches_list = await this.$store.dispatch("branches_list");
  },
  mounted() {
    //this.getDataFromApi();
    this.tabMenu = [
      {
        text: "Profile",
        icon: "mdi-account-box",
        value: "#0",
      },
      {
        text: "Contact",
        icon: "mdi-phone",
        value: "#1",
      },
      {
        text: "Passport",
        icon: "mdi-file-powerpoint-outline",
        value: "#2",
      },
      {
        text: "Emirates",
        icon: "mdi-city-variant",
        value: "#3",
      },
      {
        text: "Visa",
        icon: "mdi-file-document-multiple",
        value: "#4",
      },
      {
        text: "Bank",
        icon: "mdi-bank",
        value: "#5",
      },
      {
        text: "Documents",
        icon: "mdi-file",
        value: "#6",
      },
      {
        text: "Qualification",
        icon: "mdi-account-box",
        value: "#7",
      },
      {
        text: "Setting",
        icon: "mdi-phone",
        value: "#8",
      },
      {
        text: "Payroll",
        icon: "mdi-briefcase",
        value: "#9",
      },
      {
        text: "Login",
        icon: "mdi-lock",
        value: "#10",
      },
    ];
    this.headers = [
      // { text: "#" },
      { text: "E.ID" },
      { text: "Profile" },
      { text: "Name" },
      { text: "Email" },
      { text: "Timezone" },
      { text: "Dept" },
      { text: "Sub Dept" },
      { text: "Desgnation" },
      { text: "Role" },
      { text: "Mobile" },
      { text: "Shift" },
      { text: "Actions" },
    ];
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    handleSearch(e) {
      return e.data.length > 0 ? (this.data = e) : getDataFromApi();
    },
    async openNewPage() {
      this.employee = {};
      this.departments = [];
      this.employeeDialog = true;

      if (this.$auth.user.branch_id) {
        await this.getDepartments(this.$auth.user.branch_id);
      } else {
        await this.getDepartments(null);
      }
    },
    async filterDepartmentsByBranch(filterBranchId) {
      await this.getDepartments(filterBranchId);
      await this.getTimezone(filterBranchId);
    },
    async getDepartments(filterBranchId) {
      let options = {
        endpoint: "department-list",
        isFilter: this.isFilter,
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: filterBranchId,
        },
      };
      this.departments = await this.$store.dispatch("department_list", options);
    },

    async getTimezone(filterBranchId) {
      let options = {
        endpoint: "timezone-list",
        isFilter: this.isFilter,
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: filterBranchId,
        },
      };
      this.timezones = await this.$store.dispatch("timezone_list", options);
    },
    closeViewDialog() {
      this.viewDialog = false;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    getComponent() {
      return this.compList[this.tab];
    },
    close() {
      this.dialog = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },

    async applyFilters(id) {
      await this.getDataFromApi();
      await this.getDepartments(id);
      await this.getTimezone(id);
    },
    async getDataFromApi() {
      //this.loading = true;
      this.loadinglinear = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let options = {
        endpoint: this.endpoint,
        isFilter: this.isFilter,
        params: {
          page: page,
          sortBy: sortBy ? sortBy[0] : "",
          sortDesc: sortDesc ? sortDesc[0] : "",
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          department_id: this.department_filter_id,
          ...this.filters,
        },
      };

      this.data = await this.$store.dispatch("employees", options);

      this.loadinglinear = false;
    },

    editItem(item) {
      this.employeeId = item.id;
      this.editDialog = true;
    },
    viewItem(item) {
      this.employeeId = item.id;

      this.system_user_id = item.system_user_id;
      this.employee_id = item.employee_id;

      this.employeeObject = item;
      this.viewDialog = true;
    },
    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/${item.id}`)
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
  },
};
</script>
