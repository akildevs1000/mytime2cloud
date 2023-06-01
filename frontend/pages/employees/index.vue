<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!loading">
      <v-dialog v-model="employeeDialog" width="900">
        <v-card>
          <v-card-title class="text-h5 primary mb-5 white--text">
            Create {{ Model }}
          </v-card-title>

          <v-card-text>
            <v-row>
              <v-col md="6" sm="12" cols="12" dense>
                <v-row>
                  <v-col md="12" sm="12" cols="12">
                    <label class="col-form-label"
                      >Title <span class="text-danger">*</span></label
                    >
                    <v-select
                      v-model="employee.title"
                      :items="titleItems"
                      :hide-details="!errors.title"
                      :error="errors.title"
                      :error-messages="
                        errors && errors.title ? errors.title[0] : ''
                      "
                      dense
                      outlined
                    ></v-select>
                  </v-col>
                  <v-col md="12" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >Display Name <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.display_name"
                      type="text"
                      v-model="employee.display_name"
                      :error="errors.display_name"
                      :error-messages="
                        errors && errors.display_name
                          ? errors.display_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="12" cols="12" sm="12" dense>
                    <label class="col-form-label"
                      >Employee ID <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="text"
                      v-model="employee.employee_id"
                      :hide-details="!errors.employee_id"
                      :error="errors.employee_id"
                      :error-messages="
                        errors && errors.employee_id
                          ? errors.employee_id[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="12" cols="12" sm="12" dense>
                    <label class="col-form-label"
                      >Employee Device Id<span class="text-danger"
                        >*</span
                      ></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="text"
                      v-model="employee.system_user_id"
                      :hide-details="!errors.system_user_id"
                      :error="errors.system_user_id"
                      :error-messages="
                        errors && errors.system_user_id
                          ? errors.system_user_id[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-col>
              <v-col class="col-sm-6">
                <div
                  class="form-group pt-15"
                  style="margin: 0 auto; width: 50%"
                >
                  <v-img
                    style="
                      border: 1px solid #5fafa3;
                      border-radius: 50%;
                      margin: 0 auto;
                    "
                    :src="previewImage || '/no-profile-image.jpg'"
                  ></v-img>
                  <br />
                  <v-btn
                    small
                    class="form-control primary"
                    @click="onpick_attachment"
                    >{{ !upload.name ? "Upload" : "Change" }} Profile Image
                    <v-icon right dark>mdi-cloud-upload</v-icon>
                  </v-btn>
                  <input
                    required
                    type="file"
                    @change="attachment"
                    style="display: none"
                    accept="image/*"
                    ref="attachment_input"
                  />

                  <span
                    v-if="errors && errors.profile_picture"
                    class="text-danger mt-2"
                    >{{ errors.profile_picture[0] }}</span
                  >
                </div>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              small
              color="grey white--text"
              @click="employeeDialog = false"
            >
              Close
            </v-btn>

            <v-btn
              v-if="can('employee_create')"
              small
              :loading="loading"
              color="primary"
              @click="store_data"
            >
              Submit
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog v-model="editDialog" width="500">
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on">
            Click Me
          </v-btn>
        </template>

        <v-card>
          <v-tabs
            v-model="tab"
            background-color="background"
            centered
            dark
            icons-and-text
          >
            <v-tabs-slider></v-tabs-slider>

            <v-tab href="#tab-1">
              Profile
              <v-icon>mdi-account-box</v-icon>
            </v-tab>

            <v-tab href="#tab-3">
              Nearby
              <v-icon>mdi-phone</v-icon>
            </v-tab>
          </v-tabs>
          <v-card-text>
            <v-tabs-items v-model="tab">
              <v-tab-item v-for="i in 3" :key="i" :value="'tab-' + i">
                <v-card flat>
                  <v-card-text>{{ text }}</v-card-text>
                </v-card>
              </v-tab-item>
            </v-tabs-items>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-row class="mt-5">
        <v-col cols="6">
          <h3>{{ Model }}</h3>
          <div>Dashboard / {{ Model }}</div>
        </v-col>
        <v-col cols="6">
          <!-- <div class="text-left">
            <v-btn
              small
              class="primary--text pt-4 pb-4"
              to="/employees/employee_list"
            >
              <v-icon class="pa-0">mdi-menu</v-icon>
            </v-btn>
            <v-btn x-small class="primary pt-4 pb-4" to="/employees">
              <v-icon class="pa-0">mdi-grid</v-icon>
            </v-btn>
          </div> -->
          <div class="text-right mt-6">
            <v-btn
              small
              class="primary--text pt-4 pb-4"
              to="/employees/employee_list"
            >
              <v-icon class="pa-0">mdi-menu</v-icon>
            </v-btn>
            <v-btn x-small class="primary pt-4 pb-4" to="/employees">
              <v-icon class="pa-0">mdi-grid</v-icon>
            </v-btn>
            <v-btn
              v-if="can('employee_import_access')"
              small
              dark
              class="primary pt-4 pb-4"
              @click="dialog = true"
            >
              Import <v-icon right dark>mdi-cloud-upload</v-icon>
            </v-btn>

            <v-btn
              v-if="can('employee_export_access')"
              small
              dark
              class="primary pt-4 pb-4"
              @click="export_submit"
            >
              Export <v-icon right dark>mdi-cloud-download</v-icon>
            </v-btn>

            <v-dialog v-model="dialog" max-width="500px">
              <v-card>
                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="12" class="mb-2">
                        <span class="headline">Import Employee</span>
                      </v-col>
                      <v-col cols="12">
                        <v-file-input
                          accept="text/csv"
                          v-model="files"
                          placeholder="Upload your file"
                          label="File"
                          prepend-icon="mdi-paperclip"
                        >
                          <template v-slot:selection="{ text }">
                            <v-chip v-if="text" small label color="primary">
                              {{ text }}
                            </v-chip>
                          </template>
                        </v-file-input>
                        <br />
                        <a href="/employees.csv" download> Download Sample</a>
                        <br />
                        <span
                          v-if="errors && errors.length > 0"
                          class="error--text"
                          >{{ errors[0] }}</span
                        >
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn class="error" small @click="close"> Cancel </v-btn>

                  <v-btn
                    class="primary"
                    :loading="btnLoader"
                    small
                    @click="save"
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-dialog>

            <v-btn
              v-if="can('employee_create')"
              @click="createEmployee"
              small
              dark
              class="primary pt-4 pb-4"
              >{{ Model }} +
            </v-btn>

            <v-btn
              v-if="can('employee_create')"
              @click="employeeDialog = true"
              small
              dark
              class="primary pt-4 pb-4"
              >{{ Model }} + (NEW)
            </v-btn>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-row>
          <v-col xs="12" sm="12" md="3" cols="12">
            <v-select
              class="custom-text-box shadow-none"
              @change="getDataFromApi(`employee`)"
              v-model="pagination.per_page"
              :items="[50, 100, 500, 1000]"
              placeholder="Per Page Records"
              solo
              flat
              :hide-details="true"
            ></v-select>
          </v-col>
          <v-col xs="12" sm="12" md="3" cols="12">
            <v-select
              class="custom-text-box shadow-none"
              @change="getDataFromApi(`employee`)"
              v-model="department_id"
              item-text="name"
              item-value="id"
              :items="departments"
              placeholder="Department"
              solo
              flat
              :hide-details="true"
            ></v-select>
          </v-col>
          <v-col xs="12" sm="12" md="3" cols="12">
            <!-- <v-text-field
          class="rounded-md custom-text-box shadow-none"
          :hide-details="true"
          placeholder="Search..."
          solo
          flat
          @input="searchIt"
          v-model="search"
        ></v-text-field> -->
            <input
              class="form-control py-3 custom-text-box floating shadow-none"
              placeholder="Search..."
              @input="searchIt"
              v-model="search"
              type="text"
            />
          </v-col>
        </v-row>

        <div v-if="can(`employee_view`)">
          <v-card class="mb-5 rounded-md mt-3" elevation="0">
            <v-toolbar class="rounded-md" color="background" dense flat dark>
              <span> {{ Model }} List</span>
            </v-toolbar>
            <table
              style="
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
              "
            >
              <tr>
                <th
                  style="text-align: left; padding: 8px"
                  v-for="(item, index) in headers"
                  :key="index"
                >
                  {{ item.text }}
                </th>
              </tr>
              <v-progress-linear
                v-if="loading"
                :active="loading"
                :indeterminate="loading"
                absolute
                color="primary"
              ></v-progress-linear>
              <tr
                style="background-color: #fbfdff"
                v-for="(item, index) in data"
                :key="index"
              >
                <td style="text-align: left; padding: 8px" class="text-center">
                  <b>{{ ++index }}</b>
                </td>
                <td style="text-align: left; padding: 8px">
                  {{ item.employee_id || "---" }}
                </td>
                <td style="text-align: left; padding: 8px">
                  <v-img
                    style="border-radius: 50%; height: 40px; width: 40px"
                    :src="
                      item.profile_picture +
                        '?t=' +
                        Math.ceil(Math.random() * 1000000) ||
                      '/no-profile-image.jpg'
                    "
                  >
                  </v-img>
                </td>
                <td style="text-align: left; padding: 8px">
                  {{ item.display_name || "---" }}
                </td>
                <td style="text-align: left; padding: 8px">
                  {{ (item.department && item.department.name) || "---" }}
                </td>
                <td style="text-align: left; padding: 8px">
                  {{ item.designation && item.designation.name }}
                </td>
                <td style="text-align: left; padding: 8px">
                  {{ (item && item.user && item.user.email) || "---" }}
                </td>
                <td style="text-align: left; padding: 8px">
                  {{ (item && item.phone_number) || "---" }}
                </td>
                <td style="text-align: left; padding: 8px">
                  {{
                    item.schedule &&
                    item.schedule.shift_type &&
                    item.schedule.shift_type.name
                  }}
                </td>
                <td style="text-align: left; padding: 8px">
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
                      <v-list-item @click="deleteItem(item)">
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="error" small> mdi-delete </v-icon>
                          Delete
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </td>
                <td style="text-align: left; padding: 8px">
                  <v-btn
                    dark-2
                    icon
                    v-bind="attrs"
                    v-on="on"
                    @click="viewemployeedetails(item)"
                  >
                    <v-icon>info</v-icon>
                  </v-btn>
                </td>
              </tr>
            </table>
          </v-card>
          <div>
            <v-row>
              <v-col md="12" class="float-right">
                <div class="float-right">
                  <v-pagination
                    v-model="pagination.current"
                    :length="pagination.total"
                    @input="onPageChange"
                    :total-visible="12"
                  ></v-pagination>
                </div>
              </v-col>
            </v-row>
          </div>
        </div>
      </v-row>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
import WorkInfo from "../../components/employee/WorkInfo.vue";
import Personal from "../../components/employee/Personal.vue";
import Contact from "../../components/employee/Contact.vue";
import Passport from "../../components/employee/Passport.vue";
import Emirates from "../../components/employee/Emirates.vue";
import Visa from "../../components/employee/Visa.vue";
import Bank from "../../components/employee/Bank.vue";
import Document from "../../components/employee/Document.vue";
import Qualification from "../../components/employee/Qualification.vue";
import Setting from "../../components/employee/Setting.vue";
import Payroll from "../../components/employee/Payroll.vue";

const compList = [
  WorkInfo,
  Personal,
  Contact,
  Passport,
  Emirates,
  Visa,
  Bank,
  Document,
  Qualification,
  Setting,
  Payroll,
];

export default {
  components: { compList },
  data: () => ({
    attrs: [],
    dialog: false,
    editDialog: false,
    tab: null,
    text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
    employeeDialog: false,
    comp: "WorkInfo",
    m: false,
    expand: false,
    expand2: false,
    boilerplate: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,
    items: [
      {
        text: "Work information",
        icon: "mdi-briefcase ",
        permission: "employee_personal_access",
      },
      {
        text: "Personal information",
        icon: "mdi-account-circle ",
        permission: "employee_personal_access",
      },
      {
        text: "Contact information",
        icon: "mdi-account-box ",
        permission: "employee_contact_access",
      },
      {
        text: "Passport information",
        icon: "mdi-file-powerpoint-outline ",
        permission: "employee_passport_access",
      },
      {
        text: "Emirates information",
        icon: "mdi-city-variant",
        permission: "employee_emirate_access",
      },
      {
        text: "Visa information",
        icon: "mdi-file-document-multiple ",
        permission: "employee_visa_access",
      },
      {
        text: "Bank information",
        icon: "mdi-bank",
        permission: "employee_bank_access",
      },
      {
        text: "Documents",
        icon: "mdi-file",
        permission: "employee_document_access",
      },
      {
        text: "Qualification",
        icon: "mdi-file-sign",
        permission: "employee_qualification_access",
      },
      {
        text: "Setting",
        icon: "mdi-wrench",
        permission: "employee_setting_access",
      },
      {
        text: "Payroll",
        icon: "mdi-cash-multiple",
        permission: "employee_setting_access",
      },
      // {
      //   text: "Assign Reporter",
      //   icon: "mdi-account",
      //   permission: "employee_setting_access"
      // }
    ],
    on: "",
    color: "primary",
    files: "",
    Model: "Employee",
    endpoint: "employee",
    search: "",
    loading: false,
    total: 0,
    next_page_url: "",
    prev_page_url: "",
    current_page: 1,
    per_page: 8,
    response: "",
    ListName: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    employee: {
      display_name: "",
      employee_id: "",
      system_user_id: "",
      department_id: "",
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
    employeeId: "",

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    options: {},
    Model: "Employee",
    endpoint: "employee",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      {
        text: "#",
      },
      {
        text: "EID",
      },
      {
        text: "Profile",
      },
      {
        text: "Name",
      },
      {
        text: "Department",
      },
      {
        text: "Designation",
      },
      {
        text: "Email",
      },
      {
        text: "Mobile",
      },
      {
        text: "Shift Type",
      },
      { text: "Actions", align: "center", value: "action", sortable: false },
      {
        text: "View Info",
      },
    ],
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    departments: [],
    department_id: "",
  }),
  async created() {
    this.loading = false;
    this.boilerplate = true;
    this.getDataFromApi();

    // this.loading = true;
    this.getDepartments();
  },
  mounted() {
    //this.getDataFromApi();
  },
  watch: {
    dialog(val) {
      val || this.close();
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    getListItem(item, index) {
      this.comp = compList[index];
      this.ListName = item.text;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    close() {
      this.dialog = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },
    limitName(value) {
      if (!value) {
        return "---";
      }
      var string = value;
      var length = 14;
      return string.substring(0, length);
    },
    json_to_csv(json) {
      let data = json.map((e) => ({
        first_name: e.first_name,
        last_name: e.last_name,
        display_name: e.display_name,
        email: e.user.email,
        phone_number: e.phone_number,
        whatsapp_number: e.whatsapp_number,
        phone_relative_number: e.phone_relative_number,
        whatsapp_relative_number: e.whatsapp_relative_number,
        employee_id: e.employee_id,
        joining_date: e.show_joining_date,
        department_code: e.department_id,
        designation_code: e.designation_id,
        department: e.department.name,
        designation: e.designation.name,
      }));
      let header = Object.keys(data[0]).join(",") + "\n";
      let rows = "";
      data.forEach((e) => {
        rows += Object.values(e).join(",").trim() + "\n";
      });
      return header + rows;
    },
    res(id) {
      window.scrollTo(0, 0);
      this.boilerplate = true;
      this.$axios.get(`/employee/${id}`).then(({ data }) => {
        this.employeeId = id;
        this.work = {
          ...data,
        };
        this.boilerplate = false;
      });
    },
    export_submit() {
      if (this.data.length == 0) {
        this.snackbar = true;
        this.response = "No record to download";
        return;
      }

      console.log("this.data", this.data);
      let csvData = this.json_to_csv(this.data);
      let element = document.createElement("a");
      element.setAttribute(
        "href",
        "data:text/csv;charset=utf-8, " + encodeURIComponent(csvData)
      );
      element.setAttribute("download", "download.csv");
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    },
    save() {
      let payload = new FormData();
      payload.append("employees", this.files);
      payload.append("company_id", this.$auth?.user?.company?.id);
      let options = {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      };
      this.btnLoader = true;
      this.$axios
        .post("/employee/import", payload, options)
        .then(({ data }) => {
          this.btnLoader = false;
          if (!data.status) {
            this.errors = data.errors;
            payload.delete("employees");
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees imported successfully";
            this.getDataFromApi();
            this.close();
          }
        })
        .catch((e) => {
          if (e.toString().includes("Error: Network Error")) {
            this.errors = [
              "File is modified.Please cancel the current file and try again",
            ];
            this.btnLoader = false;
          }
        });
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    createEmployee() {
      if (this.total >= this.max_employee) {
        alert(`You cannot add more than ${this.max_employee} employees`);
        return;
      }
      this.$router.push(`/employees/create`);
    },
    goDetails(id) {
      this.$router.push(`/employees/details/${id}`);
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },
    getDataFromApi(url = this.endpoint) {
      let options = {
        params: {
          per_page: this.per_page === "Default" ? 8 : this.per_page,
          company_id: this.$auth?.user?.company?.id,
        },
      };
      this.$axios.get(`${url}`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.data.length;
        this.employeeId = this.data[0].id;

        this.contactItem = {
          ...this.data[0],
        };
        this.work = {
          ...this.data[0],
        };
        this.getListItem(this.items[0], 0);

        this.max_employee = this.$auth.user.company.max_employee;
        this.next_page_url = data.next_page_url;
        this.prev_page_url = data.prev_page_url;
        this.current_page = data.current_page;
        this.loading = false;
        this.boilerplate = false;
      });
    },
    deleteItem(item) {
      confirm("Are you sure you want to delete this item?") &&
        this.$axios.delete(this.endpoint + "/" + item.id).then((res) => {
          const index = this.data.indexOf(item);
          this.data.splice(index, 1);
          this.getDataFromApi();
        });
    },

    onPageChange() {
      this.getDataFromApi();
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
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
        this.departments.unshift({ name: "All", id: "" });
      });
    },
    getDataFromApi(url = this.endpoint) {
      this.loading = true;
      let page = this.pagination.current;
      let department_id = this.department_id;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          department_id: department_id,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },

    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
      }
    },

    editItem(item) {
      this.$router.push(`/employees/${item.id}`);
    },
    viewemployeedetails(item) {
      this.$router.push(`/employees/details/${item.id}`);
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
      let payload = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
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
          .post(this.endpoint, payload)
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
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      console.log("file", file);

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["profile_picture"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        employee.append(x, obj[x]);
      }
      employee.append("profile_picture", this.upload.name);
      employee.append("company_id", this.$auth.user.company.id);

      return employee;
    },
    store_data() {
      let final = Object.assign(this.employee);
      let employee = this.mapper(final);

      this.$axios
        .post("/employee-store", employee)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees inserted successfully";
            this.getDataFromApi();
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
