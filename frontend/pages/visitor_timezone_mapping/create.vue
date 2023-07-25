<template>
  <div style="width: 100% !important">
    <v-progress-linear
      v-if="progressloading"
      :active="loading"
      :indeterminate="loading"
      absolute
      color="primary"
    ></v-progress-linear>
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
    <v-row>
      <v-col cols="4">
        <v-select
          v-model="timezonesselected"
          :items="timezones"
          dense
          outlined
          item-value="timezone_id"
          item-text="timezone_name"
          hide-details
          label="Timezones"
          required
        ></v-select>
      </v-col>
      <v-col cols="4">
       
      </v-col>

      <v-col cols="4">
        <div class="text-right">
          <v-btn small fab class="primary">
            <v-icon small @click="goback()" color="white"
              >mdi-arrow-left</v-icon
            >
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="5">
        <v-toolbar color="background" dense flat dark>
          <span>All Visitors List</span>
        </v-toolbar>
        <div>
          <v-card class="timezone-displaylist">
            <v-card-text
              class="timezone-displaylistview"
              v-for="(user, index) in leftVisitors"
              :id="user.id"
              v-model="leftSelectedEmp"
              :key="user.id"
            >
              <div class="row">
                <v-col class="col-1" style="padding: 0px">
                  <v-checkbox
                    hideDetails
                    class="col-1 d-flex flex-column justify-center"
                    v-model="leftSelectedEmp"
                    :value="user.id"
                    primary
                    hide-details
                  ></v-checkbox>
                </v-col>
                <div
                  class="col-8"
                  :style="{
                    color:
                      user.timezone && user.timezone.timezone_name
                        ? '#000000'
                        : '#b4b0b0',
                  }"
                  style="padding-top: 21px"
                >
                  {{ user.system_user_id }}: {{ user.first_name }}
                  {{ user.last_name }}:
                  <span v-if="user.timezone && user.timezone.timezone_id != 1">
                    {{
                      user.timezone.timezone_name == "---"
                        ? "---"
                        : user.timezone.timezone_name + " Assigned"
                    }}
                  </span>
                </div>
                <div class="col-sm"></div>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </v-col>

      <v-col cols="2">
        <div style="text-align: -webkit-center">
          <button
            type="button"
            id="undo_redo_undo"
            class="btn primary btn-block white--text"
          >
            Options
          </button>

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
        <v-toolbar color="background" dense flat dark>
          <span>Selected Visitors List</span>
        </v-toolbar>
        <div>
          <v-card class="timezone-displaylist">
            <v-card-text
              class="timezone-displaylistview"
              v-for="(user, index) in rightVisitors"
              :id="user.id"
              v-model="rightSelectedEmp"
              :key="user.id"
            >
              <div class="row">
                <v-col class="col-1" style="padding: 0px">
                  <v-checkbox
                    hideDetails
                    class="col-1 d-flex flex-column justify-center"
                    v-model="rightSelectedEmp"
                    :value="user.id"
                    primary
                    hide-details
                  ></v-checkbox>
                </v-col>
                <div class="col-sm" style="padding-top: 21px; color: #000000">
                  {{ user.system_user_id }} : {{ user.first_name }}
                  {{ user.last_name }}
                </div>
                <div class="col-sm" style="padding-top: 21px">
                  <span style="color: red">{{ user.sdkEmpResponse }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="5">
        <v-toolbar color="background" dense flat dark>
          <span>All Devices List</span>
        </v-toolbar>
        <div>
          <v-card class="timezone-displaylist">
            <v-card-text
              class="timezone-displaylistview"
              v-for="(user, index) in leftDevices"
              :id="user.id"
              v-model="leftSelectedDevices"
              :key="user.id"
            >
              <div class="row">
                <v-col class="col-1" style="padding: 0px">
                  <v-checkbox
                    v-if="user.status.name == 'active'"
                    hideDetails
                    class="col-1 d-flex flex-column justify-center"
                    v-model="leftSelectedDevices"
                    :value="user.id"
                    primary
                    hide-details
                  ></v-checkbox>
                  <v-checkbox
                    v-else
                    indeterminate
                    value
                    disabled
                    hide-details
                    class="col-1 d-flex flex-column justify-center"
                  ></v-checkbox>
                </v-col>
                <div class="col-sm" style="padding-top: 21px; color: #000000">
                  {{ user.name }} : {{ user.device_id }}

                  <span
                    style="color: green"
                    v-if="user.status.name == 'active'"
                  >
                    Online</span
                  >
                  <span style="color: red" v-else>Offline </span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </v-col>

      <v-col cols="2">
        <div style="text-align: -webkit-center">
          <button
            type="button"
            id="undo_redo_undo"
            class="btn primary btn-block white--text"
          >
            Options
          </button>

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
            @click="allmoveRightDevices"
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
            @click="allmoveLeftDevices"
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
        <v-toolbar color="background" dense flat dark>
          <span>Selected Devices List</span>
        </v-toolbar>
        <div>
          <v-card class="timezone-displaylist">
            <v-card-text
              class="timezone-displaylistview"
              v-for="(user, index) in rightDevices"
              :id="user.id"
              v-model="rightSelectedDevices"
              :key="user.id"
            >
              <div class="row">
                <v-col class="col-1" style="padding: 0px"
                  ><v-checkbox
                    hideDetails
                    class="col-1 d-flex flex-column justify-center"
                    v-model="rightSelectedDevices"
                    :value="user.id"
                    primary
                    hide-details
                  ></v-checkbox>
                </v-col>
                <div class="col-sm" style="padding-top: 21px; color: #000000">
                  {{ user.name }} : {{ user.device_id }}
                </div>
                <div class="col-sm" style="padding-top: 21px">
                  <span
                    v-if="user.sdkDeviceResponse == 'Success'"
                    style="color: green"
                    >{{ user.sdkDeviceResponse }}</span
                  >
                  <span v-else style="color: red">{{
                    user.sdkDeviceResponse
                  }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <div class="col col-lg-6 text-center">
          <span v-if="errors && errors.message" class="text-danger mt-2">{{
            errors.message
          }}</span>
        </div>
        <!-- <div class="col col-lg-3 text-right">
            <div style="width: 150px; float: right">
              <button
                :loading="loading"
                @click="goback()"
                type="button"
                id="save"
                class="btn primary btn-block white--text v-size--default"
              >
                Back
              </button>
            </div>
          </div> -->
      </v-col>
      <v-col class="text-right">
        <button
          :loading="loading"
          @click="onSubmit"
          type="button"
          id="save"
          class="btn primary white--text v-size--default"
        >
          Submit
        </button>
      </v-col>
    </v-row>

    <!-- <Preloader v-else /> -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      displaybutton: false,
      progressloading: false,
      searchInput: "",
      snackbar: {
        message: "",
        color: "primary",
        show: true,
      },
      errors: [],
      response: "",
      color: "primary",
      loading: true,
      endpointUpdatetimezoneStore: "employee_timezone_mapping",
      leftSelectedEmp: [],
      departmentselected: [],
      departments: [],
      leftVisitors: [],
      rightSelectedEmp: [],
      rightVisitors: [],
      leftSelectedDevices: [],
      leftDevices: [],
      rightSelectedDevices: [],
      rightDevices: [],
      department_ids: ["---"],
      timezones: ["Timeszones are not available"],
      timezonesselected: [],
      options: {
        params: {
          company_id: this.$auth.user.company.id,
          cols: ["id", "name"],
        },
      },
    };
  },
  mounted: function () {
    this.snackbar.show = true;
    this.snackbar.message = "Data loading...Please wait ";
    this.response = "Data loading...Please wait ";

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
  created() {
    this.getDevisesDataFromApi();
    this.getVisitorsDataFromApi();
    this.getTimezonesFromApi();
  },
  methods: {
    verifySubmitButton() {
      if (this.rightVisitors.length > 0 && this.rightDevices.length > 0) {
        this.displaybutton = true;
      } else {
        this.displaybutton = false;
      }
    },
    fetch_logs() {},
    loadDepartmentVisitors() {
      //this.loading = true;
      // let page = this.pagination.current;
      let url = this.endpointEmployee;

      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          department_id: this.departmentselected,
          cols: ["id", "system_user_id", "first_name", "last_name"],
        },
      };
      let page = 1;

      this.$axios
        .get(`get_visitors_with_timezonename?page=${page}`, options)
        .then(({ data }) => {
          this.leftVisitors = [];
          this.leftVisitors = data.data;
          this.leftSelectedEmp = [];

          this.rightVisitors = [];
          this.rightSelectedEmp = [];
        });
    },
    getTimezonesFromApi() {
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios
        .get("timezone", options)
        .then(({ data }) => {
          this.timezones = data.data;
        })
        .catch((err) => console.log(err));
    },
    resetErrorMessages() {
      this.errors = [];
      this.response = "";

      $.extend(this.leftVisitors, {
        sdkEmpResponse: "",
      });
      $.extend(this.leftDevices, {
        sdkDeviceResponse: "",
      });
    },
    onSubmit() {
      this.resetErrorMessages();

      if (this.timezonesselected == "") {
        this.response = "Timezones not selected";
      } else if (this.rightVisitors.length == 0) {
        this.response = "Atleast select one Employee Details";
      } else if (this.rightDevices.length == 0) {
        this.response = "Atleast select one Device Details";
      }

      if (this.response != "") {
        this.snackbar.show = true;
        this.snackbar.message = this.response;
        this.snackbar.color = "red";
        setTimeout(() => {
          this.snackbar.show = false;
        }, 1000 * 10);
        return false;
      }
      this.loading = true;

      let snList = this.rightDevices.map((e) => e.device_id);

      let personList = this.rightVisitors.map((e) => ({
        name: e.first_name,
        userCode: e.system_user_id,
        expiry: "2089-12-31 23:59:59",
        timeGroup: this.timezonesselected,
      }));

      let payload = {
        snList,
        personList,
      };

      this.$axios.post(`visitor_timezone_mapping`, payload).then(({ data }) => {
        console.log(data);
        this.rightDevices.forEach((e, i) => {
          let found = data.find((re) => re.sn == e.device_id);
          console.log(found);

          if (found.state == true) {
            found["sdkDeviceResponse"] = "Success";
            return;
          } else {
            found["sdkDeviceResponse"] = "The device was not found or offline";
            SDKSuccessStatus = false;
          }
        });
      });
    },
    onSubmit_old() {
      this.displaybutton = false;
      if (this.timezonesselected == "") {
        this.response = this.response + "Timezones not selected";
      } else if (this.rightVisitors.length == 0) {
        this.response = this.response + " Atleast select one Employee Details";
      } else if (this.rightDevices.length == 0) {
        this.response = this.response + " Atleast select one Device Details";
      }

      if (this.response != "") {
        this.snackbar.show = true;
        this.snackbar.message = this.response;
        this.snackbar.color = "red";
        setTimeout(() => {
          this.snackbar.show = false;
        }, 1000 * 10);
        return false;
      }
      this.loading = true;

      // Define the keys you want to select
      let keysToSelect = ["system_user_id"];

      // Select the specified keys from each object
      let filteredDataEmp = [];
      this.rightVisitors.map(function (obj) {
        let selectedObj = {};
        keysToSelect.forEach(function (key) {
          if (obj.hasOwnProperty(key)) {
            // selectedObj[key] = obj[key];
            selectedObj = obj[key];
            filteredDataEmp.push(selectedObj);
          }
        });
        return selectedObj;
      });
      //
      // Define the keys you want to select
      keysToSelect = ["device_id"];

      // Select the specified keys from each object
      let filteredDataDevices = [];
      this.rightDevices.map(function (obj) {
        let selectedObj = {};
        keysToSelect.forEach(function (key) {
          if (obj.hasOwnProperty(key)) {
            // selectedObj[key] = obj[key];
            selectedObj = obj[key];
            filteredDataDevices.push(selectedObj);
          }
        });
        return selectedObj;
      });
      let options = {
        timezone_id: this.timezonesselected,
        company_id: this.$auth.user.company.id,
        employee_ids: filteredDataEmp,
        device_ids: filteredDataDevices,
      };

      console.log(options);

      let json = {
        snList: ["FC-8300T20094123"],
        personList: [
          {
            name: "Fran",
            userCode: "1",
            expiry: "2089-12-31 23:59:59",
            timeGroup: 3,
          },
        ],
      };

      this.progressloading = true;
      let jsrightVisitors = this.rightVisitors;

      let SDKSuccessStatus = true;
      this.$axios
        .post(`visitor_timezone_mapping`, options)
        .then(({ data }) => {
          if (data.record.SDKResponse) {
            this.loading = false;

            $.each(this.rightDevices, function (index, rightDevicesobj) {
              let SdkResponseDeviceobject = data.record.SDKResponse.data.find(
                (e) => e.sn == rightDevicesobj.device_id
              );

              let deviceStatusResponse = "";
              let EmpStatusResponse = "";

              if (SdkResponseDeviceobject.message == "") {
                deviceStatusResponse = "Success";
              } else if (
                SdkResponseDeviceobject.message == "The device was not found"
              ) {
                deviceStatusResponse = "The device was not found or offline";
                SDKSuccessStatus = false;
              } else if (
                SdkResponseDeviceobject.message == "person info error"
              ) {
                let SDKUseridArray = SdkResponseDeviceobject.userList; //SDK error userslist
                jsrightVisitors.forEach((element) => {
                  element["sdkEmpResponse"] = "Success";
                  let systemUserid = element.system_user_id;
                  SDKSuccessStatus = false;
                  let selectedEmpobject = SDKUseridArray.find(
                    (e) => e.userCode == systemUserid
                  );
                  EmpStatusResponse = SdkResponseDeviceobject.sdkEmpResponse;
                  deviceStatusResponse = "";

                  if (EmpStatusResponse != "") {
                    //Adding extra parameters for Employee object
                    if (selectedEmpobject) {
                      element["sdkEmpResponse"] = "person photo error ";
                      // $.extend(element, {
                      //   sdkEmpResponse: "person info error ",
                      // });
                    } else {
                      // $.extend(element, {
                      //   sdkEmpResponse: " Success",
                      // });
                      element["sdkEmpResponse"] = "Success";
                    }
                  }
                });
              } else {
              }

              //Adding extra parameters for Devices object
              rightDevicesobj["sdkDeviceResponse"] =
                deviceStatusResponse != "" ? deviceStatusResponse : " Success";
              this.errors = [];
            });
            this.rightVisitors = jsrightVisitors;
            this.progressloading = false;

            this.loading = false;
            if (!SDKSuccessStatus) {
              {
                this.errors = data.errors;
              }
              this.errors = [];
              this.errors["message"] =
                "Device/Employee Error:   Device and Employee details are Mapped. You can add/remove items from Edit list ";

              //this.displaybutton = false;
            } else {
              this.$router.push("/timezonemapping/list");
            }
          } else {
            this.errors = [];
            this.progressloading = false;

            this.errors["message"] = "Device Communication is not available";

            this.snackbar.show = true;
            this.snackbar.message = "Device Communication is not available ";
            this.response = "Device Communication is not available ";
            return false;
          }

          this.displaybutton = true;
        })
        .catch((e) => {
          console.log(e);
          alert("Error found in SDK. Please check server.");
          this.progressloading = false;
        });
    },
    goback() {
      this.$router.push("/visitor_timezone_mapping/list");
    },
    getDevisesDataFromApi() {
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          // cols: ["id", "location", "name", "device_id"],
        },
      };
      let page = 1;
      this.$axios.get(`device?page=${page}`, options).then(({ data }) => {
        this.leftDevices = data.data;
      });
    },
    getVisitorsDataFromApi(url = this.endpointEmployee) {
      let options = {
        params: {
          company_id: this.$auth.user.company.id,
          cols: ["id", "system_user_id", "first_name", "last_name"],
        },
      };
      this.$axios
        .get(`get_visitors_with_timezonename`, options)
        .then(({ data }) => {
          this.leftVisitors = data;
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
    allmoveToLeftemp() {
      this.resetErrorMessages();
      this.leftVisitors = this.leftVisitors.concat(this.rightVisitors);
      this.rightVisitors = [];
      this.leftVisitors = this.sortObject(this.leftVisitors);
      this.verifySubmitButton();
    },
    allmoveToRightEmp() {
      this.resetErrorMessages();
      // this.rightVisitors = this.rightVisitors.concat(this.leftVisitors);
      // this.leftVisitors = [];
      this.rightVisitors = this.rightVisitors.concat(
        this.leftVisitors.filter(
          (el) =>
            el.timezone.timezone_name == "---" || el.timezone.timezone_id == 1
        )
      );

      this.leftVisitors = this.leftVisitors.filter(
        (el) =>
          el.timezone.timezone_name != "---" && el.timezone.timezone_id != 1
      );
      this.rightVisitors = this.sortObject(this.rightVisitors);
      this.verifySubmitButton();
    },
    moveToLeftempOption2() {
      this.resetErrorMessages();
      if (!this.rightSelectedEmp.length) return;

      //for (let i = this.leftSelectedEmp.length; i > 0; i--) {
      let _rightSelectedEmp_length = this.rightSelectedEmp.length;
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        if (this.rightSelectedEmp) {
          let selectedindex = this.rightVisitors.findIndex(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          let selectedobject = this.rightVisitors.find(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          selectedobject.sdkEmpResponse = "";
          this.leftVisitors.push(selectedobject);

          this.rightVisitors.splice(selectedindex, 1);
        }
      }
      this.leftVisitors = this.sortObject(this.leftVisitors);

      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        this.rightSelectedEmp.pop(this.rightSelectedEmp[i]);
      }
      this.verifySubmitButton();
    },

    moveToRightEmpOption2() {
      this.resetErrorMessages();
      if (!this.leftSelectedEmp.length) return;

      let _leftSelectedEmp_length = this.leftSelectedEmp.length;
      for (let i = 0; i < _leftSelectedEmp_length; i++) {
        if (this.leftSelectedEmp) {
          let selectedindex = this.leftVisitors.findIndex(
            (e) => e.id == this.leftSelectedEmp[i]
          );

          let selectedobject = this.leftVisitors.find(
            (e) => e.id == this.leftSelectedEmp[i]
          );

          this.rightVisitors.push(selectedobject);

          this.leftVisitors.splice(selectedindex, 1);
        }
      }
      this.rightVisitors = this.sortObject(this.rightVisitors);
      for (let i = 0; i < _leftSelectedEmp_length; i++) {
        this.leftSelectedEmp.pop(this.leftSelectedEmp[i]);
      }
      this.verifySubmitButton();
    },
    /* Devices---------------------------------------- */
    allmoveLeftDevices() {
      this.resetErrorMessages();
      this.leftDevices = this.leftDevices.concat(this.rightDevices);
      this.rightDevices = [];

      this.leftDevices = this.sortObjectD(this.leftDevices);
      this.verifySubmitButton();
    },
    allmoveRightDevices() {
      this.resetErrorMessages();
      //this.rightDevices = this.rightDevices.concat(this.leftDevices);
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
  },
};
</script>
