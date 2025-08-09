<template>
  <div style="width: 100% !important" v-if="can(`employee_upload_access`)">
    <div class="text-center ma-6">
      <v-snackbar
        v-model="snackbar.show"
        small
        top="top"
        :timeout="snackBarTimeout"
      >
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col>
        <v-card class="mb-5">
          <v-row>
            <v-col>
              <v-toolbar dense flat>
                <v-toolbar-title>
                  <b class="" style="font-size: 18px; font-weight: 600"
                    >Upload Employees Photos to Device(s) - OX-900 Models</b
                  >
                </v-toolbar-title>
              </v-toolbar>
            </v-col>
            <v-col
              v-if="$auth.user.user_type !== 'department'"
              cols="6"
              class="text-right"
            >
              <v-toolbar dense flat>
                <v-btn
                  class="primary mr-2"
                  dense
                  :loading="verifyOnDeviceLoading"
                  @click="verifyEmployeeIdOnDevice()"
                  >Check Employees on Device
                </v-btn>
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
              <v-card
                class="photo-displaylist mx-1"
                style="height: 300px"
                outlined
                :key="keyleftemployees"
              >
                <div class="pa-2 bold">Employees</div>
                <v-divider />
                <div
                  style="max-height: 250px; overflow-y: auto; overflow-x: auto"
                >
                  <v-card-text>
                    <v-row class="font-weight-bold">
                      <v-col md="1">-</v-col>
                      <v-col md="1">-</v-col>
                      <v-col>Name</v-col>
                      <v-col>Employee Id</v-col>
                      <v-col
                        v-for="device in rightDevices"
                        v-if="
                          device.status_id == 1 &&
                          device.model_number == 'OX-900'
                        "
                        >{{ device.name }} ({{
                          employeesListFromDevice[device.id]
                            ? employeesListFromDevice[device.id]?.length
                            : 0
                        }}/{{ leftEmployees.length }})</v-col
                      >
                    </v-row>
                    <v-row
                      class="timezone-displaylistview1"
                      v-for="(user, index) in leftEmployees"
                      :id="user.id"
                      :key="user.id"
                      style="
                        height: 35px;
                        margin: auto;
                        display: flex;
                        padding: 0px;
                      "
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
                      <v-col style="padding: 0px; padding-top: 5px">
                        {{ user.first_name }}
                        {{ user.last_name }}
                      </v-col>
                      <v-col style="padding: 0px; padding-top: 5px">
                        {{ user.employee_id }}
                      </v-col>

                      <v-col
                        v-for="device in rightDevices"
                        v-if="
                          device.status_id == 1 &&
                          device.model_number == 'OX-900'
                        "
                        style="padding: 0px; padding-top: 5px"
                      >
                        <span
                          v-if="
                            employeesListFromDevice[device.id]?.includes(
                              user.system_user_id
                            )
                          "
                          style="color: green"
                          >Uploaded</span
                        >
                        <span v-else>Not Exist</span>
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
                  class="btn btn-default btn-block dark-theme-button"
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
                  class="btn btn-default btn-block dark-theme-button"
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
                  class="btn btn-default btn-block dark-theme-button"
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
                  class="btn btn-default btn-block dark-theme-button"
                >
                  <i
                    aria-hidden="true"
                    class="v-icon notranslate mdi mdi-chevron-double-left theme--red"
                  ></i>
                </button>
              </div>
            </v-col>

            <v-col cols="5">
              <v-card
                :key="keyleftemployees"
                class="photo-displaylist mx-1"
                outlined
                style="height: 300px"
              >
                <div class="pa-2 bold">
                  Total Employees Count: {{ rightEmployees.length }} - Uploaded
                  {{ uploadedResponseEmployeeCount }}
                </div>
                <v-divider />
                <div
                  style="
                    max-height: 250px;
                    overflow-y: auto;
                    overflow-x: hidden;
                  "
                >
                  <v-card-text>
                    <v-row class="font-weight-bold">
                      <v-col md="1">-</v-col>
                      <v-col md="1">-</v-col>
                      <v-col>Name</v-col>
                      <v-col>Employee Id</v-col>
                      <v-col
                        v-for="device in rightDevices"
                        v-if="
                          device.status_id == 1 &&
                          device.model_number == 'OX-900'
                        "
                        >{{ device.name }} ({{
                          employeesListFromDevice[device.id]?.length
                            ? employeesListFromDevice[device.id]?.length
                            : 0
                        }}/{{ rightEmployees.length }})</v-col
                      >
                    </v-row>
                    <v-row
                      class="timezone-displaylistview1"
                      v-for="(user, index) in rightEmployees"
                      :id="user.id"
                      :key="user.id"
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
                      <v-col style="padding: 0px; padding-top: 5px">
                        {{ user.first_name }}
                        {{ user.last_name }}
                      </v-col>
                      <v-col style="padding: 0px; padding-top: 5px">
                        {{ user.employee_id }}
                      </v-col>
                      <v-col
                        v-for="device in rightDevices"
                        v-if="
                          device.status_id == 1 &&
                          device.model_number == 'OX-900'
                        "
                        style="padding: 0px; padding-top: 5px"
                      >
                        <!-- {{
                          employeesListFromDevice[device.id]
                            ? updateCount(
                                employeesListFromDevice[device.id].includes(
                                  user.employee_id
                                )
                              )
                            : ""
                        }}   -->
                        <span
                          v-if="
                            employeesListFromDevice[device.id]?.includes(
                              user.system_user_id
                            )
                          "
                          style="color: green"
                          >Uploaded</span
                        >
                        <span v-else style="color: red">Not Exist</span>
                        <!-- <span
                          v-if="user.devices && user.devices[device.device_id]"
                          style="color: green"
                          >Uploaded</span
                        >
                        <span v-else>--</span> -->
                      </v-col>
                    </v-row>
                  </v-card-text>
                </div>
              </v-card>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="5">
              <v-card
                class="photo-displaylist mx-1"
                outlined
                style="height: 300px"
              >
                <div class="pa-2 bold">Devices(Model OX-900)</div>
                <v-divider />
                <div
                  style="
                    max-height: 250px;
                    overflow-y: auto;
                    overflow-x: hidden;
                  "
                >
                  <v-card-text>
                    <v-row
                      class="timezone-displaylistview1"
                      v-for="(user, index) in leftDevices"
                      v-if="user.model_number == 'OX-900'"
                      :id="user.id"
                      v-model="leftSelectedDevices"
                      :key="user.id"
                    >
                      <v-col md="1" style="padding: 0px; margin-top: 3px">
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
                          style="padding: 0px; margin-top: 3px"
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
                  class="btn btn-default btn-block dark-theme-button"
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
                  class="btn btn-default btn-block dark-theme-button"
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
                  class="btn btn-default btn-block dark-theme-button"
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
                  class="btn btn-default btn-block dark-theme-button"
                >
                  <i
                    aria-hidden="true"
                    class="v-icon notranslate mdi mdi-chevron-double-left theme--red"
                  ></i>
                </button>
              </div>
            </v-col>

            <v-col cols="5">
              <v-card
                class="photo-displaylist mx-1"
                outlined
                style="height: 300px"
              >
                <div class="pa-2 bold">Selected Devices</div>
                <v-divider />
                <div
                  style="
                    max-height: 250px;
                    overflow-y: auto;
                    overflow-x: hidden;
                  "
                >
                  <v-card-text>
                    <v-row
                      class="timezone-displaylistview1"
                      v-for="(user, index) in rightDevices"
                      v-if="user.model_number == 'OX-900'"
                      :id="user.id"
                      v-model="rightSelectedDevices"
                      :key="user.id"
                    >
                      <v-col md="1" style="padding: 0px; margin-top: 3px">
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
                          style="padding: 0px; margin-top: 3px"
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
            <v-col cols="12" class="text-center">
              <!-- <v-btn class="grey" @click="goback" small dark> Back </v-btn> -->
              <v-row>
                <v-col
                  ><span
                    v-if="errors && errors.message"
                    class="text-danger mt-2"
                    >{{ errors.message }}</span
                  ></v-col
                >
                <v-col
                  ><v-btn
                    v-if="can('employee_upload_create')"
                    small
                    class="primary"
                    :disabled="!displaybutton"
                    :loading="loading"
                    @click="onSubmit"
                  >
                    Upload To Device(s)
                  </v-btn></v-col
                >
                <v-col>
                  <v-checkbox
                    dense
                    small
                    hideDetails
                    v-model="checkboxReplaceIfExist"
                    primary
                    hide-details
                    label="Skip if Employee Exist"
                  >
                  </v-checkbox>
                </v-col>
              </v-row>

              <v-dialog
                v-model="uploadPersonResponseDialog"
                max-width="800px"
                persistent
              >
                <UploadPersonResponse_ox900
                  :key="keyleftemployees"
                  :deviceResponses="deviceResponses"
                  :cameraResponses="cameraResponses"
                  :cameraResponses2="cameraResponses2"
                  :employeesListFromDevice="employeesListFromDevice"
                  :totalDevices="rightDevices"
                  :loadingToDevice="loading"
                  @closePopup="closePopup"
                  @uploadAgain="onSubmit"
                  :rightEmployeesCount="rightEmployees.length"
                  :uploadedResponseEmployeeCount="
                    uploadedResponseEmployeeCount + 1
                  "
                  :finalMessage="
                    '  Total : ' +
                    rightEmployees.length +
                    '. Uploaded :' +
                    deviceResponses.length +
                    ' '
                  "
                />
              </v-dialog>
            </v-col>
          </v-row>
        </v-card> </v-col
    ></v-row>
  </div>
  <NoAccess v-else />
</template>

<script>
import UploadPersonResponse_ox900 from "../components/UploadPersonResponse_ox900.vue";

// import Back from "../components/Snippets/Back.vue";

export default {
  components: { UploadPersonResponse_ox900 },
  data() {
    return {
      snackBarTimeout: 3000,
      checkboxReplaceIfExist: true,
      totalDevices: null,
      keyleftemployees: 1,
      TOKEN: "7VOarATI4IfbqFWLF38VdWoAbHUYlpAY",
      SN: "M014200892110002761",

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
      employeesListFromDevice: [],
      verifyOnDeviceLoading: false,
      uploadPersonResponseDialog: false,
      uploadedResponseEmployeeCount: 0,
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
    updateCount(status) {
      if (status) this.uploadedResponseEmployeeCount++;

      console.log(status);
      console.log(this.uploadedResponseEmployeeCount);
    },
    closePopup() {
      this.uploadPersonResponseDialog = false;
    },
    async verifyEmployeeIdOnDevice() {
      this.verifyOnDeviceLoading = true;
      this.employeesListFromDevice = [];

      let response = [];
      for (const device of this.rightDevices) {
        if (device.model_number === "OX-900") {
          this.snackbar.show = true;
          this.response =
            "Checking Employee Details on Device... Please wait... ";
          this.verifyOnDeviceLoading = true;
          const options = {
            params: {
              camera_sdk_url: device.camera_sdk_url,
              device_id: device.device_id,
              company_id: this.$auth.user.company_id,
            },
          };

          try {
            const { data } = await this.$axios.get(
              "/SDK/personids-from-ox900device",
              options
            );
            if (data.error) {
              this.snackBarTimeout = 5000;
              this.snackbar.show = true;
              this.response =
                "Unable to connect device " +
                device.name +
                ". Please try after sometime";

              response.push();
              // alert(
              //   "Unable to connect device " +
              //     device.name +
              //     ". Please try after sometime"
              // );
              this.verifyOnDeviceLoading = false;

              //continue;
            } else {
              this.employeesListFromDevice[device.id] = [];
              this.employeesListFromDevice[device.id] = data; // [...data];

              // let dataArray = [...data];
              // this.$set(this.employeesListFromDevice, device.id, data);

              // console.log("Person IDs from", device.id, data);
              console.log(
                "Person IDs from",
                this.employeesListFromDevice[device.id]
              );

              // Ensure data is an array before proceeding
              if (Array.isArray(data)) {
                this.leftEmployees.forEach((employee) => {
                  if (!employee.devices) employee.devices = [];
                  employee.devices[device.device_id] = data.includes(
                    employee.employee_id
                  );
                });
                try {
                  if (this.leftEmployees)
                    this.leftEmployees = [...this.leftEmployees].sort(
                      (a, b) => {
                        return String(a.system_user_id).localeCompare(
                          String(b.system_user_id)
                        );
                      }
                    );
                } catch (e) {
                  console.log(e);
                }
                this.$emit("update:leftEmployees", this.leftEmployees);
                this.keyleftemployees++;
              } else {
                console.log(
                  `Unexpected response for device ${device.device_id}:`,
                  data
                );
              }
            }
          } catch (err) {
            console.log("Error fetching from", device.device_id, err);
          }

          // Wait 5 seconds
          await new Promise((resolve) => setTimeout(resolve, 1000 * 5));
        } //loop
      }

      if (this.response.length > 0) {
        this.snackbar.show = true;
        this.snackBarTimeout = 10000;

        response.forEach((element) => {
          this.response += element;
        });
      } else {
        this.snackbar.show = true;
        this.response = "No Device is selected in Right Hand side";
      }
      //this.response = "Employee Checking is completed.";
      this.verifyOnDeviceLoading = false;
    },

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
        let devices = data.data;

        this.totalDevices = structuredClone(data.data);

        this.leftDevices = devices;
      });
    },
    async getEmployeesDataFromApi(branch_id, url = this.endpointEmployee) {
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
      await this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftEmployees = data.data;
      }, 1000);

      // setTimeout(() => {
      //   this.verifyEmployeeIdOnDevice();
      // }, 1000 * 2);
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
      // await this.verifyEmployeeIdOnDevice();

      this.uploadedResponseEmployeeCount = 0;
      this.finalMessage = "";
      // this.$refs["UploadPersonRef"]["uploadPersonResponseDialog"] = true;
      this.uploadPersonResponseDialog = true;
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
        let shouldSkipUpload = false;

        const deviceId = this.rightDevices[0].id;
        const isEmployeeExistOnDevice = this.employeesListFromDevice[
          deviceId
        ]?.includes(item.system_user_id);

        // Determine whether to skip upload
        if (!this.checkboxReplaceIfExist && isEmployeeExistOnDevice) {
          shouldSkipUpload = true;
        }

        console.log("shouldSkipUpload", shouldSkipUpload);

        // Proceed only if we should not skip
        if (!shouldSkipUpload) {
          console.log(
            this.employeesListFromDevice[this.rightDevices[0].id],
            item.system_user_id
          );
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
            // if (data.deviceResponse[1]) {
            //   this.deviceResponses.push(data.deviceResponse[1]);
            // }
            // if (data.cameraResponse[1]) {
            //   this.cameraResponses.push(data.cameraResponse[1]);
            // }
            // if (data.cameraResponse2[1]) {
            //   this.cameraResponses2.push(data.cameraResponse2[1]);
            // }
          } catch (error) {
            console.log(`Error for ${person.name}:`, error);
          }

          // Wait 5 seconds - sleep
          await new Promise((resolve) => setTimeout(resolve, 1000 * 4));
        } //check is uploaded or not
      } //for

      // setTimeout(() => {
      await this.verifyEmployeeIdOnDevice();

      this.rightEmployees = [...this.rightEmployees].sort((a, b) => {
        return String(a.system_user_id).localeCompare(String(b.system_user_id));
      });

      this.rightEmployees.forEach((employee) => {
        this.rightDevices.forEach((device) => {
          this.employeesListFromDevice[device.id]
            ? this.updateCount(
                this.employeesListFromDevice[device.id].includes(
                  employee.employee_id
                )
              )
            : "";
        });
      });

      try {
        if (this.deviceResponses)
          this.deviceResponses = [...this.deviceResponses].sort((a, b) => {
            return String(a.userCode).localeCompare(String(b.userCode));
          });
      } catch (e) {
        console.log(e);
      }

      setTimeout(() => {
        this.loading_dialog = false;

        this.loading = false;

        this.displaybutton = true;
      }, 1000 * 5);

      // this.keyleftemployees++;
      // }, 1000 * 10);

      //----------------

      // try {
      //   await this.createSession();
      //   await this.login();
      //   for (let i = 0; i < payload.personList.length; i++) {
      //     await this.uploadEmployee(payload.personList[i], i + 1);
      //   }
      //   alert("✅ All 100 employees uploaded successfully.");
      // } catch (err) {
      //   console.info("❌ Upload failed:", err.message || err);
      //   alert("Upload failed. Check console for details.");
      // }
    },

    // async createSession() {
    //   const res = await this.$axios.get(
    //     `http://139.59.69.241:8888//api/auth/login/challenge`,
    //     {
    //       params: { username: "admin" },
    //       headers: {
    //         sxdmToken: this.TOKEN,
    //         sxdmSn: this.SN,
    //       },
    //     }
    //   );
    //   const json = res.data;
    //   sessionId.value = json.session_id;

    //   const saltedHash = CryptoJS.SHA256(
    //     PASSWORD + json.salt + json.challenge
    //   ).toString();
    //   const key = CryptoJS.enc.Utf8.parse(json.session_id);
    //   const encrypted = CryptoJS.AES.encrypt(PASSWORD, key, {
    //     mode: CryptoJS.mode.ECB,
    //   });

    //   // Save for next step
    //   sessionId.value = json.session_id;
    //   localStorage.setItem("hashed_password", saltedHash);
    //   localStorage.setItem(
    //     "ciphertext",
    //     encrypted.ciphertext.toString(CryptoJS.enc.Base64)
    //   );
    // },

    // async login() {
    //   const payload = {
    //     session_id: sessionId.value,
    //     username: "admin",
    //     password: localStorage.getItem("hashed_password"),
    //     ciphertext: localStorage.getItem("ciphertext"),
    //   };

    //   const res = await this.$axios.post(
    //     `http://139.59.69.241:8888/api/auth/login?type=web`,
    //     payload,
    //     {
    //       headers: {
    //         "Content-Type": "application/json",
    //         sxdmToken: this.TOKEN,
    //         sxdmSn: this.SN,
    //       },
    //     }
    //   );

    //   // Session ID is re-affirmed after login
    //   sessionId.value = res.data.session_id;
    // },

    // async uploadEmployee(emp, index) {
    //   console.log("emp", emp);

    //   // const res = await axios.post(
    //   //   `http://139.59.69.241:8888//api/persons/item`,
    //   //   emp,
    //   //   {
    //   //     headers: {
    //   //       "Content-Type": "application/json",
    //   //       Cookie: `sessionID=${sessionId.value}`,
    //   //       sxdmToken: this.TOKEN,
    //   //       sxdmSn: this.SN,
    //   //     },
    //   //   }
    //   // );
    //   console.log(`✅ Uploaded employee ${index}:`, res.data);
    // },
  },
};
</script>
