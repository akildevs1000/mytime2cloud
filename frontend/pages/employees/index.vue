<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!loading">
      <v-dialog v-model="dialogCropping" width="500">
        <v-card style="padding-top: 20px">
          <v-card-text>
            <!-- <img :src="imageUrl" alt="Preview Image" /> -->
            <!-- Cropping image step1 -->
            <VueCropper v-show="selectedFile" ref="cropper" :src="selectedFile" alt="Source Image" :aspectRatio="1"
              :autoCropArea="0.9" :viewMode="3"></VueCropper>

            <!-- <div class="cropper-preview"></div> -->
          </v-card-text>

          <v-card-actions>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
              <v-btn class="danger btn btn-danger text-left" text @click="closePopup()" style="float: left">Cancel</v-btn>
            </div>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
              <v-btn class="primary btn btn-danger text-right"
                @click="saveCroppedImageStep2(), (dialog = false)">Crop</v-btn>
            </div>
          </v-card-actions>
        </v-card>
      </v-dialog>
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
                    <label class="col-form-label">Title <span class="text-danger">*</span></label>
                    <v-select v-model="employee.title" :items="titleItems" :hide-details="!errors.title"
                      :error="errors.title" :error-messages="errors && errors.title ? errors.title[0] : ''
                        " dense outlined></v-select>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <label class="col-form-label">Employee ID <span class="text-danger">*</span></label>
                    <v-text-field dense outlined type="text" v-model="employee.employee_id"
                      :hide-details="!errors.employee_id" :error="errors.employee_id" :error-messages="errors && errors.employee_id
                        ? errors.employee_id[0]
                        : ''
                        "></v-text-field>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <label class="col-form-label">Employee Device Id<span class="text-danger">*</span></label>
                    <v-text-field dense outlined type="text" v-model="employee.system_user_id"
                      :hide-details="!errors.system_user_id" :error="errors.system_user_id" :error-messages="errors && errors.system_user_id
                        ? errors.system_user_id[0]
                        : ''
                        "></v-text-field>
                  </v-col>
                  <v-col md="12" sm="12" cols="12" dense>
                    <label class="col-form-label">Display Name <span class="text-danger">*</span></label>
                    <v-text-field dense outlined :hide-details="!errors.display_name" type="text"
                      v-model="employee.display_name" :error="errors.display_name" :error-messages="errors && errors.display_name
                        ? errors.display_name[0]
                        : ''
                        "></v-text-field>
                  </v-col>
                  <v-col md="12" cols="12" sm="12" dense>
                    <label class="col-form-label">Email (optional)</label>
                    <v-text-field dense outlined type="text" v-model="employee.email" :hide-details="!errors.email"
                      :error="errors.email" :error-messages="errors && errors.email ? errors.email[0] : ''
                        "></v-text-field>
                  </v-col>
                </v-row>
              </v-col>
              <v-col class="col-sm-6">
                <div class="form-group pt-15" style="margin: 0 auto; width: 200px">
                  <v-img style="
                      width: 100%;
                      height: 200px;
                      border: 1px solid #5fafa3;
                      border-radius: 50%;
                      margin: 0 auto;
                    " :src="previewImage || '/no-profile-image.jpg'"></v-img>
                  <br />
                  <v-btn small class="form-control primary" @click="onpick_attachment">{{ !upload.name ? "Upload" :
                    "Change" }} Profile Image
                    <v-icon right dark>mdi-cloud-upload</v-icon>
                  </v-btn>
                  <input required type="file" @change="attachment" style="display: none" accept="image/*"
                    ref="attachment_input" />

                  <span v-if="errors && errors.profile_picture" class="text-danger mt-2">{{ errors.profile_picture[0]
                  }}</span>
                </div>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn small color="grey white--text" @click="employeeDialog = false">
              Close
            </v-btn>

            <v-btn v-if="can('employee_create')" small :loading="loading" color="primary" @click="store_data">
              Submit
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog v-model="editDialog" width="1200" :key="employeeId">
        <v-card>
          <v-tabs v-model="tab" background-color="primary" centered dark icons-and-text>
            <v-tabs-slider></v-tabs-slider>

            <v-tab v-for="(item, index) in tabMenu" :key="index" :href="item.value">
              {{ item.text }}
              <v-icon>{{ item.icon }}</v-icon>
            </v-tab>
          </v-tabs>
          <v-card-text>
            <v-tabs-items v-model="tab">
              <v-tab-item v-for="(tb, index) in tabMenu" :key="index" :value="`${index}`">
                <component :is="getComponent(tab)" :employeeId="employeeId" @eventFromchild="getDataFromApi" />
              </v-tab-item>
            </v-tabs-items>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-dialog v-model="dialog" max-width="500px">
        <v-card>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12" class="mb-2">
                  <span class="headline">Import Employee</span>
                </v-col>
                <v-col cols="12">
                  <v-file-input accept="text/csv" v-model="files" placeholder="Upload your file" label="File"
                    prepend-icon="mdi-paperclip">
                    <template v-slot:selection="{ text }">
                      <v-chip v-if="text" small label color="primary">
                        {{ text }}
                      </v-chip>
                    </template>
                  </v-file-input>
                  <br />
                  <a href="/employees.csv" download> Download Sample</a>
                  <br />
                  <span v-if="errors && errors.length > 0" class="error--text">{{ errors[0] }}</span>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn class="error" small @click="close"> Cancel </v-btn>

            <v-btn class="primary" :loading="btnLoader" small @click="importEmployee">Save</v-btn>
          </v-card-actions>
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
            <!-- <v-btn
              small
              class="primary--text "
              to="/employees/employee_list"
            >
              <v-icon class="pa-0">mdi-menu</v-icon>
            </v-btn>
            <v-btn x-small class="primary" to="/employees">
              <v-icon class="pa-0">mdi-grid</v-icon>
            </v-btn> -->


          </div>
        </v-col>
      </v-row>
      <v-row>

        <!-- <v-col xs="12" sm="12" md="3" cols="12">
            <v-select class="custom-text-box shadow-none" @change="getDataFromApi(`employee`)"
              v-model="pagination.per_page" :items="[50, 100, 500, 1000]" placeholder="Per Page Records" solo flat
              :hide-details="true"></v-select>
          </v-col> -->
        <v-col xs="12" sm="12" md="3">
          <v-select class="custom-text-box shadow-none" @change="getDataFromApi()" v-model="department_filter_id"
            item-text="name" item-value="id" :items="[{ name: `All Departments`, id: `` }, ...departments]"
            placeholder="Department" solo flat :hide-details="true"></v-select>
        </v-col>
        <v-col xs="12" sm="12" md="3">
          <!-- <v-text-field
          class="rounded-md custom-text-box shadow-none"
          :hide-details="true"
          placeholder="Search..."
          solo
          flat
          @input="searchIt"
          v-model="search"
        ></v-text-field> -->

        </v-col>
        <!-- <v-col cols="3" align="right">
          <input style="width:200px;height: 33px;" small class="form-control py-3 custom-text-box floating shadow-none"
            placeholder="Search Employee Details" @input="searchIt" v-model="search" type="text" />
        </v-col> -->
        <v-col cols="6" align="right">

          <v-btn v-if="can('employee_import_access')" small dark class="primary" @click="dialog = true">
            Import <v-icon right dark>mdi-cloud-upload</v-icon>
          </v-btn>

          <v-btn v-if="can('employee_export_access')" small dark class="primary" @click="export_submit">
            Export <v-icon right dark>mdi-cloud-download</v-icon>
          </v-btn>


        </v-col>
      </v-row>
      <v-row>
        <div v-if="can(`employee_view`)">
          <v-card class="mb-5 rounded-md mt-3" elevation="0">
            <v-toolbar class="rounded-md" color="background" dense flat dark>
              <span> {{ Model }} List</span>

              <a style="padding-left:10px" title="Reload Page/Reset Form" @click="getDataFromApi()"><v-icon
                  class="mx-1">mdi
                  mdi-reload</v-icon></a>
              <v-spacer></v-spacer>
              <v-toolbar-items>
                <v-col>
                  <input small dark class="employeepage-seach-textfield form-control py-3 custom-text-box floating  "
                    placeholder="Search Employee Details" style="width:200px;height: 32px;
    margin-top: -3px;
    padding-top: 11px !important;" @input="searchIt" v-model="search" type="text" />


                </v-col>

                <v-col class="toolbaritems-button-design">

                  <v-btn v-if="can('employee_create')" @click="employeeDialog = true" small dark class="primary">{{ Model
                  }}
                    <v-icon right dark>mdi-account-tie</v-icon>
                  </v-btn>
                </v-col>
              </v-toolbar-items>
            </v-toolbar>


            <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
              {{ snackText }}

              <template v-slot:action="{ attrs }">
                <v-btn v-bind="attrs" text @click="snack = false">
                  Close
                </v-btn>
              </template>
            </v-snackbar>
            <!-- <v-progress-linear v-if="loadinglinear" :active="loadinglinear" :indeterminate="loadinglinear" absolute
              color="primary"></v-progress-linear> -->
            <!-- :server-items-length="datatableTotallenght" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc"  -->
            <v-data-table dense v-model="selectedItems" :headers="headers_table" :items="data" model-value="data.id"
              :loading="loadinglinear" :options.sync="options" :footer-props="{
                itemsPerPageOptions: [10, 50, 100, 500, 1000],
              }" class="elevation-1">

              <template v-slot:item.employee_id="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">
                  {{ item.employee_id }}
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterEmployeeid" v-model="datatable_search_textbox"
                      label="Search Employee Id"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <!-- <template v-slot:item.display_name_search_icon="{ item, index }">

                <v-row no-gutters>
                  <v-col style="padding: 5px;;padding-left:0px;width:50px;max-width:50px">
                    <v-img style="border-radius: 50%; height: auto;  width: 50px;max-width: 50px" :src="item.profile_picture
                      ? item.profile_picture
                      : '/no-profile-image.jpg'
                      ">
                    </v-img>
                  </v-col>
                  <v-col style="padding: 10px;">
                    <strong> {{ item.first_name ? item.first_name : '---' }} {{ item.last_name ? item.last_name : '---'
                    }}</strong>
                    <div> {{ item.designation ? item.designation.name : "---" }}</div>

                  </v-col>
                </v-row>

                <v-edit-dialog v-if="index == 0" large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                  @save="getDataFromApi()" @open="datatable_open">
                  <v-icon style="margin-top: -188px;
    margin-left: 106px;">mdi mdi-magnify</v-icon>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterEmployeeName" v-model="datatable_search_textbox"
                      label="Search Employee Name"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template> -->
              <template v-slot:item.display_name="{ item, index }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">
                  <v-row no-gutters>
                    <v-col style="padding: 5px;;padding-left:0px;width:50px;max-width:50px">
                      <v-img style="border-radius: 50%; height: auto;  width: 50px;max-width: 50px" :src="item.profile_picture
                        ? item.profile_picture
                        : '/no-profile-image.jpg'
                        ">
                      </v-img>
                    </v-col>
                    <v-col style="padding: 10px;">
                      <strong> {{ item.first_name ? item.first_name : '---' }} {{ item.last_name ? item.last_name : '---'
                      }}</strong>
                      <div> {{ item.designation ? item.designation.name : "---" }}</div>

                    </v-col>
                  </v-row>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterEmployeeName" v-model="datatable_search_textbox"
                      label="Type Employee Name"></v-text-field>
                  </template>
                </v-edit-dialog>
                <!-- <v-row no-gutters>
                  <v-col style="padding: 5px;;padding-left:0px;width:50px;max-width:50px">
                    <v-img style="border-radius: 50%; height: auto;  width: 50px;max-width: 50px" :src="item.profile_picture
                      ? item.profile_picture
                      : '/no-profile-image.jpg'
                      ">
                    </v-img>
                  </v-col>
                  <v-col style="padding: 10px;">
                    <strong> {{ item.first_name ? item.first_name : '---' }} {{ item.last_name ? item.last_name : '---'
                    }}</strong>
                    <div> {{ item.designation ? item.designation.name : "---" }}</div>

                  </v-col>
                </v-row>

                <v-edit-dialog v-if="index == 0" large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                  @save="getDataFromApi()" @open="datatable_open">
                  <v-icon style="margin-top: -188px;
    margin-left: 106px;">mdi mdi-magnify</v-icon>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterEmployeeName" v-model="datatable_search_textbox"
                      label="Search Employee Name"></v-text-field>
                  </template>
                </v-edit-dialog> -->

              </template>

              <template v-slot:item.department.name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">

                  <strong>{{ item.department.name }}</strong>
                  <div> {{ item.sub_department.name }}</div>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterDepartmentName" v-model="datatable_search_textbox"
                      label="Search Department name"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <template v-slot:item.phone_number="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">

                  {{ item.phone_number }}
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterPhoneNumber" v-model="datatable_search_textbox"
                      label="Search Phone Number"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <template v-slot:item.local_email="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">

                  {{ item.local_email }}
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterEmailid" v-model="datatable_search_textbox"
                      label="Search Email"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <template v-slot:item.schedule.shift.name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">

                  {{
                    item.schedule &&
                    item.schedule.shift &&
                    item.schedule.shift.name
                  }}
                  <div> {{
                    item.schedule &&
                    item.schedule.shift ? "Working Hours: " +
                  item.schedule.shift.working_hours : ""

                  }}
                  </div>
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterShiftname" v-model="datatable_search_textbox"
                      label="Search Shift Name"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <template v-slot:item.timezone.timezone_name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @save="getDataFromApi()"
                  @open="datatable_open">
                  {{
                    item.timezone ? item.timezone.timezone_name : ""
                  }}
                  <template v-slot:input>
                    <v-text-field @input="getDataFromApi_FilterTimezonename" v-model="datatable_search_textbox"
                      label="Search Timezone"></v-text-field>
                  </template>
                </v-edit-dialog>

              </template>
              <template v-slot:item.options="{ item }">

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
              </template>
            </v-data-table>

            <!-- <table class="employee-table">
            <v-progress-linear v-if="loadinglinear" :active="loadinglinear" :indeterminate="loadinglinear" absolute
              color="primary"></v-progress-linear>
            <tr>
              <th style="text-align: left; padding: 8px" v-for="(item, index) in headers" :key="index">
                {{ item.text }}
              </th>
            </tr>

            <tr v-for="(item, index) in data" :key="index">

              <td style="text-align: left; padding: 8px">
                {{ item.employee_id || "---" }}
              </td>
              <td style="text-align: left; padding: 8px">
                <v-img style="border-radius: 50%; height: 40px; width: 40px" :src="item.profile_picture
                  ? item.profile_picture
                  : '/no-profile-image.jpg'
                  ">
                </v-img>
              </td>
              <td>{{ item.display_name }}</td>
              <td>{{ item.user.email }}</td>
              <td>{{ item.timezone.timezone_name }}</td>
              <td>{{ item.department.name }}</td>
              <td>{{ item.sub_department.name }}</td>
              <td>{{ item.designation.name }}</td>
              <td>{{ item.role.name }}</td>
              <td>{{ item.phone_number || "---" }}</td>
              <td>
                {{
                  item.schedule &&
                  item.schedule.shift &&
                  item.schedule.shift.name
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
            </tr>
          </table> -->
            <!-- <v-col col="12" v-if="displayErrormsg" class="text-center">No Records avaialble</v-col> -->
          </v-card>
          <!-- <div>
            <v-row>
              <v-col md="12" class="float-right">
                <div class="float-right">
                  <v-pagination v-model="pagination.current" :length="pagination.total" @input="onPageChange"
                    :total-visible="12"></v-pagination>
                </div>
              </v-col>
            </v-row>
          </div> -->
        </div>
      </v-row>
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
  },

  data: () => ({
    sortBy: 'employee_id',
    sortDesc: false,
    datatableTotallenght: 10,
    snack: false,
    snackColor: '',
    snackText: '',
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
    attrs: [],
    dialog: false,
    editDialog: false,
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
    total: 0,
    next_page_url: "",
    prev_page_url: "",
    current_page: 1,
    per_page: 500,
    ListName: "",
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

    pagination: {
      current: 1,
      total: 0,
      per_page: 50,
    },
    options: {},
    Model: "Employee",
    endpoint: "employee",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
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

      { text: "EMP ID", align: "left", sortable: true, key: 'employee_id', value: "employee_id" },
      { text: "Name", align: "left", sortable: true, key: 'display_name', value: "display_name" },
      // { text: "Name", align: "left", sortable: true, key: 'display_name', value: "display_name_search_icon" },



      {
        text: "Department",
        align: "left",
        sortable: true,
        key: 'department',
        value: "department.name", //template name should be match for sorting sub table should be the same
      },
      {
        text: "Mobile",
        align: "left",
        sortable: true,
        key: 'mobile',
        value: "phone_number", // search and sorting enable if value matches with template name
      },
      {
        text: "Email",
        align: "left",
        sortable: true,
        key: 'email',
        value: "user.email",
      },
      {
        text: "Shift",
        align: "left",
        sortable: true,
        key: 'shceduleshift',  //sorting without . _
        value: "schedule.shift.name",
      },
      {
        text: "Timezone",
        align: "left",
        sortable: true,
        key: 'timezone',
        value: "timezone.timezone_name",
      },
      {
        text: "Options",
        align: "left",
        sortable: false,
        key: 'options',
        value: "options",
      },
    ],
  }),
  async created() {
    this.loading = false;
    this.boilerplate = true;

    this.payloadOptions = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      },
    };

    this.getDataFromApi();
    this.getDepartments();
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
      { text: "EID" },
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
    dialog(val) {
      val || this.close();
    },
  },
  methods: {
    datatable_save() {
    },
    datatable_cancel() {
      this.datatable_search_textbox = '';
    },
    datatable_open() {
      this.datatable_search_textbox = '';
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
      setTimeout(() => { }, 300);
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
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },
    onPageChange() {
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint) {
      //this.loading = true;
      this.loadinglinear = true;

      let page = this.pagination.current;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          department_id: this.department_filter_id,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.datatableTotallenght = this.data.length;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;

        this.data.length == 0
          ? (this.displayErrormsg = true)
          : (this.displayErrormsg = false);


        this.loadinglinear = false;
      });
    },
    getDataFromApi_FilterEmployeeid(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_employee_id');
      }
    },

    getDataFromApi_FilterEmployeeName(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_employee_name');
      }
    },
    getDataFromApi_FilterDepartmentName(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_department_name');
      }
    },
    getDataFromApi_FilterPhoneNumber(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_phone_number');
      }
    },
    getDataFromApi_FilterEmailid(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_emailid');
      }
    },
    getDataFromApi_FilterShiftname(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_shiftname');
      }
    },
    getDataFromApi_FilterTimezonename(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi_FilterDatatable(e, 'search_timezonename');
      }
    },
    getDataFromApi_FilterDatatable(key, extraColumnName) {

      let url = `${this.endpoint}/search/${key}`;
      //this.loading = true;
      this.loadinglinear = true;
      let page = this.pagination.current;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          department_id: this.department_filter_id,
          "datatable_column_filter": true,
        },
      };


      options.params[extraColumnName] = extraColumnName;

      this.$axios.get(`${url}?page = ${page}`, options).then(({ data }) => {

        if (data.data.length == 0) {

          this.snack = true;
          this.snackColor = 'error';
          this.snackText = 'No Results Found';
          this.loading = false;
          return false;
        }




        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;

        this.data.length == 0
          ? (this.displayErrormsg = true)
          : (this.displayErrormsg = false);

        this.loadinglinear = false;
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
    editItem(item) {
      this.employeeId = item.id;
      this.editDialog = true;
    },
    editItemId(id) {
      this.employeeId = id;
      this.editDialog = true;
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
      employee.append("company_id", this.$auth.user.company.id);

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
          employee.append("attachment_input", blob, "cropped_image.jpg");

          //croppedimagesptep4 //push to API in blob method only
          this.saveToAPI(employee);
        }, "image/jpeg");
      } else {
        this.saveToAPI(employee);
      }
    },
    saveToAPI(employee) {
      this.$axios
        .post("/employee-store", employee)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees inserted successfully";
            this.getDataFromApi();
            this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
