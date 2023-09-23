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
    <div v-if="!loading">
      <v-dialog persistent v-model="dialogCropping" width="500">
        <v-card style="padding-top: 20px">
          <v-card-text>
            <VueCropper
              v-show="selectedFile"
              ref="cropper"
              :src="selectedFile"
              alt="Source Image"
              :aspectRatio="1"
              :autoCropArea="0.9"
              :viewMode="3"
            ></VueCropper>
          </v-card-text>

          <v-card-actions>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
              <v-btn
                class="danger btn btn-danger text-left"
                text
                @click="closePopup()"
                style="float: left"
                >Cancel</v-btn
              >
            </div>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
              <v-btn
                class="primary btn btn-danger text-right"
                @click="saveCroppedImageStep2(), (dialog = false)"
                >Crop</v-btn
              >
            </div>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="employeeDialog" width="900">
        <v-card>
          <v-card-title dark class="popup_background">
            {{ formTitle }} {{ Model }}
            <v-spacer></v-spacer>
            <v-icon @click="employeeDialog = false" outlined dark>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12" md="3">
                  <div class="text-center mt-7">
                    <v-img
                      style="
                        width: 150px;
                        height: 150px;
                        border-radius: 50%;
                        margin: 0 auto;
                      "
                      :src="previewImage || '/no-business_profile.png'"
                    ></v-img>
                    <v-btn
                      class="mt-2"
                      style="width: 100%"
                      small
                      @click="onpick_attachment"
                      >{{ !upload.name ? "Upload" : "Change" }}
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
                <v-col md="9" sm="12" cols="12" dense>
                  <v-row>
                    <v-col md="6" cols="12" sm="12" dense>
                      <label>Branch Name</label>
                      <v-text-field
                        dense
                        outlined
                        type="text"
                        v-model="employee.name"
                        hide-details
                      ></v-text-field>
                      <span
                        v-if="errors && errors.name"
                        class="text-danger mt-2"
                        >{{ errors.name[0] }}</span
                      >
                    </v-col>
                    <v-col md="6" cols="12" sm="12" dense>
                      <label>Manager</label>
                      <v-text-field
                        dense
                        outlined
                        type="text"
                        v-model="employee.employee_id"
                        hide-details
                      ></v-text-field>
                      <span
                        v-if="errors && errors.employee_id"
                        class="text-danger mt-2"
                        >{{ errors.employee_id[0] }}</span
                      >
                    </v-col>

                    <v-col md="4" cols="12" sm="12" dense>
                      <label>Licence Number</label>
                      <v-text-field
                        dense
                        outlined
                        type="text"
                        v-model="employee.licence_number"
                        hide-details
                      ></v-text-field>
                      <span
                        v-if="errors && errors.licence_number"
                        class="text-danger mt-2"
                        >{{ errors.licence_number[0] }}</span
                      >
                    </v-col>
                    <v-col md="4" cols="12" sm="12" dense>
                      <label>Licence Issued By</label>
                      <v-text-field
                        dense
                        outlined
                        type="text"
                        v-model="employee.licence_issue_by_department"
                        hide-details
                      ></v-text-field>
                      <span
                        v-if="errors && errors.licence_issue_by_department"
                        class="text-danger mt-2"
                        >{{ errors.licence_issue_by_department[0] }}</span
                      >
                    </v-col>
                    <v-col md="4" sm="12" cols="12">
                      <label
                        >Licence Expiry Date
                        <span class="text-danger">*</span></label
                      >
                      <div>
                        <v-menu
                          v-model="joiningDateMenuOpen"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              hide-details
                              v-model="employee.licence_expiry"
                              persistent-hint
                              append-icon="mdi-calendar"
                              readonly
                              outlined
                              dense
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                            <span
                              v-if="errors && errors.licence_expiry"
                              class="text-danger mt-2"
                              >{{ errors.licence_expiry[0] }}</span
                            >
                          </template>
                          <v-date-picker
                            style="min-height: 320px"
                            v-model="employee.licence_expiry"
                            no-title
                            @input="joiningDateMenuOpen = false"
                          ></v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>

                    <v-col md="6" cols="12" sm="12" dense>
                      <label>Lat</label>
                      <v-text-field
                        dense
                        outlined
                        type="text"
                        v-model="employee.lat"
                        hide-details
                        :error="errors.lat"
                        :error-messages="
                          errors && errors.lat ? errors.lat[0] : ''
                        "
                      ></v-text-field>
                    </v-col>
                    <v-col md="6" cols="12" sm="12" dense>
                      <label>Lon</label>
                      <v-text-field
                        dense
                        outlined
                        type="text"
                        v-model="employee.po_box"
                        :hide-details="!errors.po_box"
                        :error="errors.po_box"
                        :error-messages="
                          errors && errors.po_box ? errors.po_box[0] : ''
                        "
                      ></v-text-field>
                    </v-col>
                    <v-col md="12" cols="12" sm="12" dense>
                      <label>Address</label>
                      <v-textarea
                        dense
                        outlined
                        type="text"
                        rows="3"
                        v-model="employee.address"
                        :hide-details="!errors.address"
                        :error="errors.address"
                        :error-messages="
                          errors && errors.address ? errors.address[0] : ''
                        "
                      >
                      </v-textarea>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <!-- <v-btn small color="grey white--text" @click="employeeDialog = false">
              Close
            </v-btn> -->

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

      <div class="text-center">
        <v-dialog
          persistent
          v-model="viewDialog"
          width="1200"
          :key="employeeId"
        >
          <BranchDetails
            @close-parent-dialog="closeViewDialog"
            :employeeObject="employeeObject"
          />
        </v-dialog>
      </div>
      <!-- <v-dialog persistent v-model="dialog" max-width="500px">
        <v-card>
          <v-card-title dense class="primary white--text background">
            Import Employee
            <v-spacer></v-spacer>
            <v-icon @click="dialog = false" outlined dark color="white">
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
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
              @click="importEmployee"
              >Save</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog> -->

      <div v-if="can(`employee_view`)">
        <v-container>
          <!-- <Back class="primary white--text" /> -->

          <v-card elevation="0" class="mt-2">
            <v-toolbar class="mb-2" dense flat>
              <v-toolbar-title>
                <span> {{ Model }}es </span></v-toolbar-title
              >
              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                dense
                class="ma-0 px-0"
                x-small
                :ripple="false"
                text
                title="Reload"
              >
                <v-icon class="ml-2" @click="clearFilters" dark
                  >mdi mdi-reload</v-icon
                >
              </v-btn>
              <!-- </template>
                <span>Reload</span>
              </v-tooltip> -->
              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
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
              <!-- </template>
                <span>Filter</span>
              </v-tooltip> -->

              <v-spacer></v-spacer>

              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dense
                    x-small
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                    @click="dialog = true"
                  >
                    <v-icon color="white" right dark size="x-large"
                      >mdi-cloud-upload</v-icon
                    >
                  </v-btn>
                </template>
                <span>Import</span>
              </v-tooltip>
              <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dense
                    x-small
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                    @click="export_submit"
                  >
                    <v-icon color="white" right size="x-large" dark
                      >mdi-cloud-download</v-icon
                    >
                  </v-btn>
                </template>
                <span>Download</span>
              </v-tooltip> -->
              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                dense
                x-small
                class="ma-0 px-0"
                :ripple="false"
                text
                title="Add Employee"
                @click="OpenDialog('Create')"
              >
                <v-icon right size="x-large" dark v-if="can('employee_create')"
                  >mdi-plus-circle</v-icon
                >
              </v-btn>
              <!-- </template>
                <span>Add New Employee</span>
              </v-tooltip> -->
            </v-toolbar>
            <v-data-table
              dense
              v-model="selectedItems"
              :headers="headers_table"
              :items="data"
              model-value="data.id"
              :loading="loadinglinear"
              :options.sync="options"
              :footer-props="{
                itemsPerPageOptions: [100, 500, 1000],
              }"
              class="elevation-1"
              :server-items-length="totalRowsCount"
            >
              <template
                v-slot:item.branch="{ item, index }"
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
                      :src="item.logo ? item.logo : '/no-profile-image.jpg'"
                    >
                    </v-img>
                  </v-col>
                  <v-col style="padding: 10px">
                    <strong> {{ item.branch_name }}</strong>
                    <div>
                      {{ item.branch_code }}
                    </div>
                  </v-col>
                </v-row>
              </template>
              <template v-slot:item.location_address="{ item }">
                {{ item.location }}
                <br />
                {{ item.address }}
              </template>

              <template v-slot:item.manager_mobile="{ item }">
                {{ (item.user && item.user.name) || "---" }}
                <br />
                {{ item.telephone }}
              </template>

              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item @click="viewItem(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-eye </v-icon>
                        View
                      </v-list-item-title>
                    </v-list-item>
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
              </template>
            </v-data-table>
          </v-card>
        </v-container>
      </div>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
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
import Back from "../../components/Snippets/Back.vue";
import headers_table from "../../menus/branch.json";

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
    Back,
  },

  data: () => ({
    departments: [],
    shifts: [],
    timezones: [],
    joiningDate: null,
    joiningDateMenuOpen: false,
    totalRowsCount: 0,
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
    search: "",
    loading: false,
    //total: 0,
    next_page_url: "",
    prev_page_url: "",
    current_page: 1,
    per_page: 1000,
    ListName: "",
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    employee: {},
    upload: {
      name: "",
    },
    previewImage: null,
    payload: {},
    personalItem: {},
    contactItem: {},
    emirateItems: {},
    setting: {},

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    options: {},
    Model: "Branch",
    endpoint: "branch",
    search: "",
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
    headers_table,
    formTitle: "Create",
  }),

  async created() {
    this.loading = false;
    this.boilerplate = true;

    this.payloadOptions = {
      params: {
        per_page: 10,
        company_id: this.$auth.user.company_id,
      },
    };

    this.getDataFromApi();
    this.getDepartments();
    this.getShifts();
    this.getTimezone();
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
    OpenDialog(action) {
      this.formTitle = action;
      this.employee = {};
      this.employeeDialog = true;
    },
    getCurrentShift(item) {
      // Define an array of day names
      const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
      const dayName = daysOfWeek[new Date().getDay()];
      const { shift_name } =
        item && item.roster.json.find((e) => e.day == dayName);

      return shift_name || "---";
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

    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
      //this.datatable_search_textbox = '';
    },
    closePopup() {
      //croppingimagestep5
      this.$refs.attachment_input.value = null;
      this.dialogCropping = false;
    },
    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;

      this.dialogCropping = false;
    },
    getComponent() {
      return this.compList[this.tab];
    },
    close() {
      this.dialog = false;
      this.errors = [];
      setTimeout(() => {}, 300);
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
    export_submit() {
      if (this.data.length == 0) {
        this.snackbar = true;
        this.response = "No record to download";
        return;
      }

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
    importEmployee() {
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
      return true;
    },
    onPageChange() {
      this.getDataFromApi();
    },
    applyFilters() {
      this.getDataFromApi();
    },
    toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};

      this.isFilter = false;
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint) {
      //this.loading = true;
      this.loadinglinear = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          department_id: this.department_filter_id,
          department_ids: this.$auth.user.assignedDepartments,
          ...this.filters,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.data = data.data;
        // this.data = [
        //   {
        //     branch_code: "NAME01",
        //     branch_name: "value2",
        //     location_address:"test",
        //     manager_name:"test",
        //     department: { name: { id: "value3" } },
        //     phone_number: "value4",
        //     user: { email: "value5" },
        //     timezone: { name: "value6" },
        //     options: "value7",
        //   },
        //   {
        //     branch_code: "NAME02",
        //     branch_name: "value9",
        //     location_address:"test",
        //     manager_name:"test",
        //     department: { name: { id: "value10" } },
        //     phone_number: "value11",
        //     user: { email: "value12" },
        //     timezone: { name: "value13" },
        //     options: "value14",
        //   },
        // ];
        //this.server_datatable_totalItems = data.total;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;

        this.totalRowsCount = data.total;

        this.data.length == 0
          ? (this.displayErrormsg = true)
          : (this.displayErrormsg = false);

        this.loadinglinear = false;
      });
    },

    getDepartments() {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
          department_ids: this.$auth.user.assignedDepartments,
        },
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
        // this.departments.unshift({ name: "All Departments", id: "" });
      });
    },
    getShifts() {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
      };
      this.$axios.get("shift", { params: options }).then(({ data }) => {
        this.shifts = data.data;
        //this.shifts.unshift({ name: "All Shifts", id: "" });
      });
    },
    getTimezone() {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
      };
      this.$axios.get("timezone", { params: options }).then(({ data }) => {
        this.timezones = data.data;
        // this.timezones.unshift({ name: "All Timezones", id: "" });
        //this.timezones.unshift({ timezone_name: "24HOURS", id: "1", timezone_id: '1' });
      });
    },
    editItem(item) {
      this.employee = item;
      this.employeeId = item.id;
      this.employee.name = item.user.name;
      this.employee.email = item.user.email;
      this.formTitle = "Update";
      this.employeeDialog = true;
    },
    viewItem(item) {
      this.employeeId = item.id;

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
    save() {
      let payload = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company_id,
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
          //croppedimage step6
          // this.previewImage = e.target.result;

          this.selectedFile = event.target.result;

          this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        this.dialogCropping = true;
      }
    },
    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        employee.append(x, obj[x]);
      }
      employee.append("profile_picture", this.upload.name);
      employee.append("company_id", this.$auth.user.company_id);

      return employee;
    },
    store_data() {
      let final = Object.assign(this.employee);
      let employee = this.mapper(final);

      //croppedimageStep3
      if (this.$refs.attachment_input.files[0]) {
        this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          // Create a FormData object and append the Blob as a file
          //const formData = new FormData();
          employee.append("profile_picture", blob, "cropped_image.jpg");

          //croppedimagesptep4 //push to API in blob method only
          this.saveToAPI(employee);
        }, "image/jpeg");
      } else {
        this.saveToAPI(employee);
      }
    },
    saveToAPI(employee) {
      this.$axios
        .post("/branch", employee)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Branch inserted successfully";
            this.getDataFromApi();
            this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
