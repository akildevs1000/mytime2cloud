<template>
  <div
    style="width: 100% !important"
    v-if="can(`employee_device_photo_upload_access`)"
  >
    <div class="text-center ma-2">
      <v-snackbar
        :color="snackbar.color"
        v-model="snackbar.show"
        small
        top="top"
        :timeout="3000"
      >
        {{ response }}
      </v-snackbar>
    </div>

    <v-card class="mb-5">
      <v-row>
        <v-col cols="8">
          <v-toolbar dense flat>
            <v-toolbar-title>
              <b class="" style="font-size: 18px; font-weight: 600"
                >Employees upload to Devices</b
              >
            </v-toolbar-title>
          </v-toolbar>
        </v-col>
        <v-col
          v-if="$auth.user.user_type !== 'department'"
          cols="4"
          class="text-right"
        >
          <v-toolbar dense flat>
            <v-select
              v-if="isCompany"
              @change="filterDepartmentsByBranch($event)"
              v-model="branch_id"
              :items="[
                { branch_name: `All Branches`, id: `` },
                ...branchesList,
              ]"
              dense
              outlined
              item-value="id"
              item-text="branch_name"
              hide-details
              label="All Branches"
            ></v-select>
            <v-select
              class="mx-2"
              @change="loadDepartmentemployees"
              v-model="departmentselected"
              :items="departments"
              dense
              outlined
              item-value="id"
              item-text="name"
              hide-details
              label="Department"
              :search-input.sync="searchInput"
            ></v-select>
          </v-toolbar>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="5">
          <v-card class="photo-displaylist mx-1" style="height: 300px" outlined>
            <div class="pa-2">Employees</div>
            <v-divider />
            <div
              style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
            >
              <v-card-text>
                <v-row
                  class="timezone-displaylistview1"
                  v-for="(user, index) in leftEmployees"
                  :id="user.id"
                  v-model="leftEmployees"
                  :key="user.id"
                  style="border-bottom: 1px solid #ddd"
                >
                  <v-col md="1" style="padding: 0px; margin-top: -7px">
                    <v-checkbox
                      dense
                      small
                      hideDetails
                      v-model="leftSelectedEmp"
                      :value="user.id"
                      primary
                      hide-details
                    ></v-checkbox>
                  </v-col>

                  <v-col md="1" style="padding: 0px">
                    <v-img
                      :title="user.first_name + ' ' + user.last_name"
                      style="
                        float: left;
                        border-radius: 50%;
                        height: auto;
                        padding: 0px;
                        position: relative;
                        top: 0;
                        transition: top ease 1s;

                        margin-left: -3px;
                        width: 25px;
                        border: 1px solid #ddd;
                      "
                      :src="
                        user.profile_picture
                          ? user.profile_picture
                          : '/no-profile-image.jpg'
                      "
                    >
                    </v-img>
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.first_name }}
                    {{ user.last_name }}
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.employee_id }}
                  </v-col>
                </v-row>
              </v-card-text>
            </div>
          </v-card>
        </v-col>

        <v-col cols="2">
          <div style="text-align: -webkit-center">
            <v-btn type="button" class="primary mt-8 mb-2" block>
              Transfer Employees
            </v-btn>

            <button
              @click="moveToRightEmpOption2"
              type="button"
              id="undo_redo_rightSelected"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-right theme--red"
              ></i>
            </button>

            <button
              @click="allmoveToRightEmp"
              type="button"
              id="undo_redo_rightAll"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-double-right theme--red"
              ></i>
            </button>
            <button
              @click="moveToLeftempOption2"
              type="button"
              id="undo_redo_leftSelected"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-left theme--red"
              ></i>
            </button>
            <button
              @click="allmoveToLeftemp"
              type="button"
              id="undo_redo_leftAll"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-double-left theme--red"
              ></i>
            </button>
          </div>
        </v-col>

        <v-col cols="5">
          <v-card class="photo-displaylist mx-1" outlined style="height: 300px">
            <div class="pa-2">Selected Employees</div>
            <v-divider />
            <div
              style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
            >
              <v-card-text>
                <v-row
                  class="timezone-displaylistview1"
                  v-for="(user, index) in rightEmployees"
                  :id="user.id"
                  v-model="rightSelectedEmp"
                  :key="user.id"
                  style="border-bottom: 1px solid #ddd"
                >
                  <v-col md="1" style="padding: 0px">
                    <v-checkbox
                      dense
                      small
                      hideDetails
                      v-model="rightSelectedEmp"
                      :value="user.id"
                      primary
                      hide-details
                    ></v-checkbox>
                  </v-col>

                  <v-col md="1" style="padding: 0px">
                    <v-img
                      :title="user.first_name + ' ' + user.last_name"
                      style="
                        float: left;
                        border-radius: 50%;
                        height: auto;
                        padding: 0px;
                        position: relative;
                        top: 0;
                        transition: top ease 1s;
                        margin-left: -3px;
                        width: 25px;
                        border: 1px solid #ddd;
                      "
                      :src="
                        user.profile_picture
                          ? user.profile_picture
                          : '/no-profile-image.jpg'
                      "
                    >
                    </v-img>
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.first_name }}
                    {{ user.last_name }}
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.employee_id }}
                  </v-col>
                </v-row>
              </v-card-text>
            </div>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="5">
          <v-card class="photo-displaylist mx-1" outlined style="height: 300px">
            <div class="pa-2">Devices</div>
            <v-divider />
            <div
              style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
            >
              <v-card-text>
                <v-row
                  class="timezone-displaylistview1"
                  v-for="(user, index) in leftDevices"
                  :id="user.id"
                  v-model="leftSelectedDevices"
                  :key="user.id"
                  style="border-bottom: 1px solid #ddd"
                >
                  <v-col md="1" style="padding: 0px;margin-top-3">
                    <v-checkbox
                      v-if="user.status.name == 'active'"
                      dense
                      small
                      hideDetails
                      v-model="leftSelectedDevices"
                      :value="user.id"
                      primary
                      hide-details
                    ></v-checkbox>
                    <v-checkbox
                      style="padding: 0px;margin-top-3"
                      v-else
                      indeterminate
                      value
                      disabled
                      dense
                      small
                      hideDetails
                      v-model="leftSelectedDevices"
                      :value="user.id"
                      primary
                      hide-details
                    ></v-checkbox>
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.name }}
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.model_number }}
                  </v-col>
                  <v-col md="3" style="padding: 0px">
                    <img
                      title="Online"
                      v-if="user.status.name == 'active'"
                      src="/icons/device_status_open.png"
                      style="width: 30px"
                    />
                    <img
                      title="Offline"
                      v-else
                      src="/icons/device_status_close.png"
                      style="width: 30px"
                    />
                  </v-col>
                </v-row>
              </v-card-text>
            </div>
          </v-card>
        </v-col>

        <v-col cols="2">
          <div style="text-align: -webkit-center">
            <v-btn type="button" class="primary mt-8 mb-2" block>
              Transfer Devices
            </v-btn>
            <button
              @click="moveToRightDevicesOption2"
              type="button"
              id="undo_redo_rightSelected"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-right theme--red"
              ></i>
            </button>

            <button
              @click="allmoveToRightDevices"
              type="button"
              id="undo_redo_rightAll"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-double-right theme--red"
              ></i>
            </button>
            <button
              @click="moveToLeftDevicesOption2"
              type="button"
              id="undo_redo_leftSelected"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-left theme--red"
              ></i>
            </button>
            <button
              @click="allmoveToLeftDevices"
              type="button"
              id="undo_redo_leftAll"
              class="btn btn-default btn-block"
            >
              <i
                aria-hidden="true"
                class="v-icon notranslate mdi mdi-chevron-double-left theme--red"
              ></i>
            </button>
          </div>
        </v-col>

        <v-col cols="5">
          <v-card class="photo-displaylist mx-1" outlined style="height: 300px">
            <div class="pa-2">Selected Devices</div>
            <v-divider />
            <div
              style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
            >
              <v-card-text>
                <v-row
                  class="timezone-displaylistview1"
                  v-for="(user, index) in rightDevices"
                  :id="user.id"
                  v-model="rightSelectedDevices"
                  :key="user.id"
                  style="border-bottom: 1px solid #ddd"
                >
                  <v-col md="1" style="padding: 0px;margin-top-3">
                    <v-checkbox
                      v-if="user.status.name == 'active'"
                      dense
                      small
                      hideDetails
                      v-model="rightSelectedDevices"
                      :value="user.id"
                      primary
                      hide-details
                    ></v-checkbox>
                    <v-checkbox
                      style="padding: 0px;margin-top-3"
                      v-else
                      indeterminate
                      value
                      disabled
                      dense
                      small
                      hideDetails
                      v-model="leftSelectedDevices"
                      :value="user.id"
                      primary
                      hide-details
                    ></v-checkbox>
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.name }}
                  </v-col>
                  <v-col md="3" style="padding: 0px; padding-top: 5px">
                    {{ user.model_number }}
                  </v-col>
                  <v-col md="3" style="padding: 0px">
                    <span
                      v-if="user.sdkDeviceResponse == 'Success'"
                      style="color: green"
                      >{{ user.sdkDeviceResponse }}</span
                    >
                    <span v-else style="color: red">{{
                      user.sdkDeviceResponse
                    }}</span>
                  </v-col>
                </v-row>
              </v-card-text>
            </div>
          </v-card>
        </v-col>
      </v-row>
      <v-row class="mx-1">
        <v-progress-linear
          v-if="progressloading"
          :active="loading"
          :indeterminate="loading"
          absolute
          color="primary"
        ></v-progress-linear>
        <v-col cols="12" class="text-right">
          <span v-if="errors && errors.message" class="text-danger mt-2">{{
            errors.message
          }}</span>
          <v-btn class="grey" @click="goback" small dark> Back </v-btn>
          <v-btn
            small
            class="primary"
            :disabled="!displaybutton"
            :loading="loading"
            @click="onSubmit"
          >
            Submit
          </v-btn>

          <UploadPersonResponse
            ref="UploadPersonRef"
            :deviceResponses="deviceResponses"
            :cameraResponses="cameraResponses"
            :cameraResponses2="cameraResponses2"
          />
        </v-col>
      </v-row>
    </v-card>
  </div>
  <NoAccess v-else/>  
</template>

<script>
// import Back from "../components/Snippets/Back.vue";

export default {
  components: {},
  data() {
    return {
      UploadPersonResponseCompKey: 1,
      deviceResponses: [],
      cameraResponses: [],
      cameraResponses2: [],
      uploadPersonResponseDialog: false,
      isCompany: true,
      branch_id: null,
      branchesList: [],
      loading: false,
      counter: 0,
      devices_dialog: [],
      displaybutton: false,
      progressloading: false,
      searchInput: "",
      snackbar: {
        message: "",
        color: "black",
        show: false,
      },
      errors: [],
      response: "",
      color: "primary",
      loading: true,
      endpointEmployee: "get_employeeswith_timezonename",
      endpointUpdatetimezoneStore: "employee_timezone_mapping",
      //endpointUpdatetimezoneUpdate: "employee_timezone_mapping",
      endpointDevise: "device",
      leftSelectedEmp: [],
      departmentselected: [],
      departments: [],
      leftEmployees: [],
      rightSelectedEmp: [],
      rightEmployees: [],
      leftSelectedDevices: [],
      leftDevices: [],
      rightSelectedDevices: [],
      rightDevices: [],
      department_ids: ["---"],
      timezones: ["Timeszones are not available"],
      timezonesselected: [],
      options: {
        params: {
          company_id: this.$auth.user.company_id,
          cols: ["id", "name"],
        },
      },
    };
  },
  mounted: function () {
    // this.snackbar.show = false;
    // this.snackbar.message = "Data loading...Please wait ";
    // this.response = "Data loading...Please wait ";

    this.$nextTick(function () {
      setTimeout(() => {
        this.loading = false;
        //this.snackbar = false;
      }, 2000);
    });

    setTimeout(() => {
      this.loading = false;
      //this.snackbar = false;
    }, 2000);
  },
  async created() {
    if (this.$auth.user.branch_id == 0) {
      this.isCompany = true;
      try {
        const { data } = await this.$axios.get(`branches_list`, {
          params: {
            per_page: 100,
            company_id: this.$auth.user.company_id,
          },
        });
        this.branchesList = data;
      } catch (error) {
        // Handle the error
        console.error("Error fetching branch list", error);
      }
    } else {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
    }
    this.progressloading = true;
    await this.filterDepartmentsByBranch(this.branch_id);
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async filterDepartmentsByBranch(branch_id) {
      await this.getDepartmentsApi(this.options, branch_id);
      await this.getDevisesDataFromApi(branch_id);
      await this.getEmployeesDataFromApi(branch_id);
      await this.getTimezonesFromApi(branch_id);
    },
    fetch_logs() {},
    loadDepartmentemployees() {
      //this.loading = true;
      // let page = this.pagination.current;
      let url = this.endpointEmployee;
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          department_id: this.departmentselected,
          branch_id: this.branch_id,
          cols: ["id", "employee_id", "first_name", "last_name"],
        },
      };
      let page = 1;

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftEmployees = [];
        this.leftEmployees = data.data;
        this.leftSelectedEmp = [];

        this.rightEmployees = [];
        this.rightSelectedEmp = [];
      });
    },
    getDepartmentsApi(options, branch_id) {
      options.params.branch_id = branch_id;
      this.progressloading = true;
      this.$axios
        .get("departments", options)
        .then(({ data }) => {
          this.departments = data.data;
          this.departments.unshift({ id: "---", name: "All Departments" });
        })
        .catch((err) => console.log(err));
    },
    getTimezonesFromApi(branch_id) {
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          branch_id: branch_id,
        },
      };
      this.$axios
        .get("timezone", options)
        .then(({ data }) => {
          this.timezones = data.data;

          this.$axios
            .get("employee_timezone_mapping", options)
            .then(({ data }) => {
              data.data.forEach((element) => {
                let selectedindex = this.timezones.findIndex(
                  (e) => e.timezone_id == element.timezone_id
                );

                if (selectedindex >= 0) this.timezones.splice(selectedindex, 1);
              });
            });
        })
        .catch((err) => console.log(err));
    },
    resetErrorMessages() {
      this.errors = [];
      this.response = "";

      // $.extend(this.rightEmployees, {
      //   sdkEmpResponse: "",
      // });
      // $.extend(this.rightDevices, {
      //   sdkDeviceResponse: "",
      // });
      this.leftEmployees.forEach((element) => {
        element["sdkEmpResponse"] = "";
      });
      this.leftDevices.forEach((element) => {
        element["sdkDeviceResponse"] = "";
      });
    },

    goback() {
      this.$router.push("/timezonemapping/list");
    },
    getDevisesDataFromApi(branch_id, url = this.endpointDevise) {
      //this.loading = true;
      // let page = this.pagination.current;
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          sortBy: "status_id",
          branch_id: branch_id,
          //cols: ["id", "location", "name", "device_id", "status:id"],
        },
      };
      let page = 1;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftDevices = data.data;
      });
    },
    getEmployeesDataFromApi(branch_id, url = this.endpointEmployee) {
      //this.loading = true;
      // let page = this.pagination.current;
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          cols: [
            "id",
            "employee_id",
            "display_name",
            "first_name",
            "last_name",
          ],
          branch_id: branch_id,
        },
      };
      let page = 1;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftEmployees = data.data;
      }, 1000);
    },
    sortObject: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.first_name && b.first_name) {
          let nameA = a.first_name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.first_name.toUpperCase();

          if (nameA < nameB) {
            return -1;
          } else if (nameA > nameB) {
            return 1;
          } else {
            return 0;
          }
        } else {
        }
      }),
    sortObjectD: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.device_id && b.device_id) {
          let nameA = a.device_id.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.device_id.toUpperCase();

          if (nameA < nameB) {
            return -1;
          } else if (nameA > nameB) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
      }),
    sortObjectC: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.name && b.name) {
          let nameA = a.name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.name.toUpperCase();

          if (nameA < nameB) {
            return -1;
          } else if (nameA > nameB) {
            return 1;
          } else {
            return 0;
          }
        }
      }),
    verifySubmitButton() {
      if (this.rightEmployees.length > 0 && this.rightDevices.length > 0) {
        this.displaybutton = true;
      } else {
        this.displaybutton = false;
      }
    },
    allmoveToLeftemp() {
      this.resetErrorMessages();
      this.leftEmployees = this.leftEmployees.concat(this.rightEmployees);
      this.rightEmployees = [];
      this.leftEmployees = this.sortObject(this.leftEmployees);

      this.verifySubmitButton();
    },
    allmoveToRightEmp() {
      this.resetErrorMessages();
      // this.rightEmployees = this.rightEmployees.concat(this.leftEmployees);
      // this.leftEmployees = [];

      this.rightEmployees = this.rightEmployees.concat(
        this.leftEmployees.filter((el) => el.profile_picture != null)
      );

      this.leftEmployees = this.leftEmployees.filter(
        (el) => el.profile_picture == null
      );

      this.rightEmployees = this.sortObject(this.rightEmployees);
      this.verifySubmitButton();
    },
    moveToLeftempOption2() {
      this.resetErrorMessages();

      if (!this.rightSelectedEmp.length) return;
      //for (let i = this.leftSelectedEmp.length; i > 0; i--) {
      let _rightSelectedEmp_length = this.rightSelectedEmp.length;
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        if (this.rightSelectedEmp) {
          let selectedindex = this.rightEmployees.findIndex(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          let selectedobject = this.rightEmployees.find(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          selectedobject.sdkEmpResponse = "";
          this.leftEmployees.push(selectedobject);

          this.rightEmployees.splice(selectedindex, 1);
        }
      }
      this.leftEmployees = this.sortObject(this.leftEmployees);
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        this.rightSelectedEmp.pop(this.rightSelectedEmp[i]);
      }

      this.verifySubmitButton();
    },
    moveToLeftemp(id) {
      this.resetErrorMessages();
      this.rightSelectedEmp.push(id);
      if (!this.rightSelectedEmp.length) return;

      //for (let i = this.leftSelectedEmp.length; i > 0; i--) {
      let _rightSelectedEmp_length = this.rightSelectedEmp.length;
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        if (this.rightSelectedEmp) {
          let selectedindex = this.rightEmployees.findIndex(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          let selectedobject = this.rightEmployees.find(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          selectedobject.sdkEmpResponse = "";
          this.leftEmployees.push(selectedobject);

          this.rightEmployees.splice(selectedindex, 1);
        }
      }
      this.leftEmployees = this.sortObject(this.leftEmployees);

      this.rightSelectedEmp.pop(id);
      this.verifySubmitButton();
    },
    check: function (id, e) {},
    selectLeftEmployee(id) {
      this.leftSelectedEmp.push(id);
    },

    moveToRightEmpOption2() {
      this.resetErrorMessages();
      this.resetErrorMessages();
      if (!this.leftSelectedEmp.length) return;

      let _leftSelectedEmp_length = this.leftSelectedEmp.length;
      for (let i = 0; i < _leftSelectedEmp_length; i++) {
        if (this.leftSelectedEmp) {
          let selectedindex = this.leftEmployees.findIndex(
            (e) => e.id == this.leftSelectedEmp[i]
          );

          let selectedobject = this.leftEmployees.find(
            (e) => e.id == this.leftSelectedEmp[i]
          );

          this.rightEmployees.push(selectedobject);

          this.leftEmployees.splice(selectedindex, 1);
        }
      }
      this.rightEmployees = this.sortObject(this.rightEmployees);

      for (let i = 0; i < _leftSelectedEmp_length; i++) {
        this.leftSelectedEmp.pop(this.leftSelectedEmp[i]);
      }
      this.verifySubmitButton();
    },

    /* Devices---------------------------------------- */
    allmoveToLeftDevices() {
      this.resetErrorMessages();
      this.leftDevices = this.leftDevices.concat(this.rightDevices);
      this.rightDevices = [];

      this.leftDevices = this.sortObjectD(this.leftDevices);
      this.verifySubmitButton();
    },
    allmoveToRightDevices() {
      this.resetErrorMessages();
      ///this.rightDevices = this.rightDevices.concat(this.leftDevices);
      //this.leftDevices = [];

      this.rightDevices = this.rightDevices.concat(
        this.leftDevices.filter((el) => el.status.name == "active")
      );

      this.leftDevices = this.leftDevices.filter(
        (el) => el.status.name == "inactive"
      );

      this.rightDevices = this.sortObjectD(this.rightDevices);
      this.verifySubmitButton();
    },
    moveToLeftDevicesOption2() {
      this.resetErrorMessages();

      if (!this.rightSelectedDevices.length) return;

      //for (let i = this.leftSelectedDevices.length; i > 0; i--) {
      let _rightSelectedDevices_length = this.rightSelectedDevices.length;
      for (let i = 0; i < _rightSelectedDevices_length; i++) {
        if (this.rightSelectedDevices) {
          let selectedindex = this.rightDevices.findIndex(
            (e) => e.id == this.rightSelectedDevices[i]
          );

          let selectedobject = this.rightDevices.find(
            (e) => e.id == this.rightSelectedDevices[i]
          );
          selectedobject["sdkEmpResponse"] = "";
          this.leftDevices.push(selectedobject);

          this.rightDevices.splice(selectedindex, 1);
        }
      }

      this.leftDevices = this.sortObjectD(this.leftDevices);

      for (let i = 0; i < _rightSelectedDevices_length; i++) {
        this.rightSelectedDevices.pop(this.rightSelectedDevices[i]);
      }
      this.verifySubmitButton();
    },
    moveToLeftDevices(id) {
      this.resetErrorMessages();
      this.rightSelectedDevices.push(id);

      if (!this.rightSelectedDevices.length) return;

      //for (let i = this.leftSelectedDevices.length; i > 0; i--) {
      let _rightSelectedDevices_length = this.rightSelectedDevices.length;
      for (let i = 0; i < _rightSelectedDevices_length; i++) {
        if (this.rightSelectedDevices) {
          let selectedindex = this.rightDevices.findIndex(
            (e) => e.id == this.rightSelectedDevices[i]
          );

          let selectedobject = this.rightDevices.find(
            (e) => e.id == this.rightSelectedDevices[i]
          );

          this.leftDevices.push(selectedobject);

          this.rightDevices.splice(selectedindex, 1);
        }
      }

      this.leftDevices = this.sortObjectD(this.leftDevices);

      this.rightSelectedDevices.pop(id);
      this.verifySubmitButton();
    },
    moveToRightDevicesOption2() {
      this.resetErrorMessages();

      if (!this.leftSelectedDevices.length) return;

      let _leftSelectedDevices_length = this.leftSelectedDevices.length;
      for (let i = 0; i < _leftSelectedDevices_length; i++) {
        if (this.leftSelectedDevices) {
          let selectedindex = this.leftDevices.findIndex(
            (e) => e.id == this.leftSelectedDevices[i]
          );

          let selectedobject = this.leftDevices.find(
            (e) => e.id == this.leftSelectedDevices[i]
          );
          selectedobject["sdkDeviceResponse"] = "";
          this.rightDevices.push(selectedobject);

          this.leftDevices.splice(selectedindex, 1);
        }
      }

      this.rightDevices = this.sortObjectD(this.rightDevices);

      for (let i = 0; i < _leftSelectedDevices_length; i++) {
        this.leftSelectedDevices.pop(this.leftSelectedDevices[i]);
      }
      this.verifySubmitButton();
    },
    moveToRightDevices(id) {
      this.resetErrorMessages();
      this.leftSelectedDevices.push(id);

      if (!this.leftSelectedDevices.length) return;

      let _leftSelectedDevices_length = this.leftSelectedDevices.length;
      for (let i = 0; i < _leftSelectedDevices_length; i++) {
        if (this.leftSelectedDevices) {
          let selectedindex = this.leftDevices.findIndex(
            (e) => e.id == this.leftSelectedDevices[i]
          );

          let selectedobject = this.leftDevices.find(
            (e) => e.id == this.leftSelectedDevices[i]
          );

          selectedobject["sdkDeviceResponse"] = "";
          this.rightDevices.push(selectedobject);

          this.leftDevices.splice(selectedindex, 1);
        }
      }

      this.rightDevices = this.sortObjectD(this.rightDevices);

      this.leftSelectedDevices.pop(id);
      this.verifySubmitButton();
    },
    async onSubmit() {
      this.$refs["UploadPersonRef"]["uploadPersonResponseDialog"] = true;

      this.deviceResponses = [];
      this.cameraResponses = [];
      this.cameraResponses2 = [];

      this.displaybutton = false;
      this.loading = true;
      if (this.rightEmployees.length == 0) {
        this.response = this.response + " Atleast select one Employee Details";
      } else if (this.rightDevices.length == 0) {
        this.response = this.response + " Atleast select one Device Details";
      }

      this.loading_dialog = true;
      this.errors = [];
      for (const item of this.rightEmployees) {
        let person = {
          name: `${item.first_name} ${item.last_name}`,
          userCode: parseInt(item.system_user_id),
          profile_picture_raw: item.profile_picture_raw,
          faceImage: item.profile_picture,
        };

        if (item.rfid_card_number) {
          person.cardData = item.rfid_card_number;
        }

        if (item.rfid_card_password) {
          person.password = item.rfid_card_password;
        }

        if (item.finger_prints.length > 0) {
          person.fp = item.finger_prints.map((e) => e.fp);
        }

        if (item.palms.length > 0) {
          person.palm = item.palms.map((e) => e.palm);
        }

        let personListArray = [person];

        let payload = {
          personList: personListArray,
          snList: this.rightDevices.map((e) => e.device_id),
          branch_id: this.branch_id,
        };

        try {
          let { data } = await this.$axios.post(`/SDK/AddPerson`, payload);
          if (data.deviceResponse[0]) {
            this.deviceResponses.push(data.deviceResponse[0]);
          }
          if (data.cameraResponse[0]) {
            this.cameraResponses.push(data.cameraResponse[0]);
          }
          if (data.cameraResponse2[0]) {
            this.cameraResponses2.push(data.cameraResponse2[0]);
          }
        } catch (error) {
          console.log(`Error for ${person.name}:`, error);
        }
      }

      this.loading_dialog = false;

      this.loading = false;

      this.displaybutton = true;
    },
  },
};
</script>
