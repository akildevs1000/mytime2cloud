<template>
  <div style="width: 100% !important">
    <div>
      <v-row>
        <v-col cols="12">
          <v-row class="mt-5 mb-5">
            <v-col cols="4">
              <h3>Timezone Mapping - new</h3>
              <div>Dashboard</div>
            </v-col>
          </v-row>
          <v-progress-linear
            v-if="progressloading"
            :active="loading"
            :indeterminate="loading"
            absolute
            color="primary"
          ></v-progress-linear>
        </v-col>
      </v-row>
      <v-row>
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
      </v-row>
      <v-row>
        <v-col cols="4">
          <v-select
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
        </v-col>
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
          <div style="width: 150px; float: right">
            <button
              @click="goback()"
              type="button"
              id="back"
              class="btn primary btn-block white--text v-size--default"
            >
              Timezone Mapping List
            </button>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="5">
          <v-toolbar color="background" dense flat dark>
            <span>All Employees List</span>
          </v-toolbar>
          <div>
            <v-card class="displaylist">
              <v-card-text
                class="displaylistview"
                v-for="(user, index) in leftEmployees"
                :id="user.id"
                @click="moveRightEmp(user.id, user.timezone)"
                v-model="leftSelectedEmp"
                :key="user.id"
              >
                <div class="row">
                  <div
                    class="col-sm"
                    :style="{
                      color:
                        user.timezone && user.timezone.timezone_name
                          ? '#b4b0b0'
                          : '#000000',
                    }"
                  >
                    {{ user.employee_id }}: {{ user.display_name }}:
                    <span v-if="user.timezone">
                      {{ user.timezone.timezone_name }}
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
              @click="moveRightEmp"
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
              @click="allmoveRightEmp"
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
              @click="moveLeftemp"
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
              @click="allmoveLeftemp"
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
            <span>Selected Employees List</span>
          </v-toolbar>
          <div>
            <v-card class="displaylist">
              <v-card-text
                class="displaylistview"
                v-for="(user, index) in rightEmployees"
                :id="user.id"
                @click="moveLeftemp(user.id)"
                v-model="rightSelectedEmp"
                :key="user.id"
              >
                <div class="row">
                  <div class="col-sm">
                    {{ user.employee_id }} : {{ user.display_name }}
                  </div>
                  <div class="col-sm">
                    <span style="color: red">{{ user.sdkEmpResponse }}</span>
                  </div>
                </div>
              </v-card-text>
            </v-card>
            <!-- <select
              multiple
              v-model="rightSelectedEmp"
              @dblclick="moveLeftemp"
              class="form-control"
              size="13"
            >
              <option
                v-for="(user, index) in rightEmployees"
                :key="index"
                :value="user.id"
              >
                Eid: {{ user.employee_id }} : {{ user.display_name }} :
              </option>
            </select> -->
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="5">
          <v-toolbar color="background" dense flat dark>
            <span>All Devices List</span>
          </v-toolbar>
          <div>
            <v-card class="displaylist">
              <v-card-text
                class="displaylistview"
                v-for="(user, index) in leftDevices"
                :id="user.id"
                @click="moveRightDevices(user.id)"
                v-model="leftSelectedDevices"
                :key="user.id"
              >
                <div class="row">
                  <div class="col">
                    {{ user.name }} : {{ user.location }}: {{ user.device_id }}
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
              @click="moveRightDevices"
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
              @click="moveLeftDevices"
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
            <v-card class="displaylist">
              <v-card-text
                class="displaylistview"
                v-for="(user, index) in rightDevices"
                :id="user.id"
                @click="moveLeftDevices(user.id)"
                v-model="rightSelectedDevices"
                :key="user.id"
              >
                <div class="row">
                  <div class="col-sm">
                    {{ user.name }} : {{ user.location }} : {{ user.device_id }}
                  </div>
                  <div class="col-sm">
                    <span style="color: red">{{ user.sdkDeviceResponse }}</span>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <div class="row">
            <div class="col col-lg-6 text-center">
              <span v-if="errors && errors.message" class="text-danger mt-2">{{
                errors.message
              }}</span>
            </div>
            <div class="col col-lg-3 text-right">
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
            </div>
            <div class="col col-lg-3 text-right">
              <div style="width: 150px; float: right">
                <button
                  v-if="displaybutton"
                  :loading="loading"
                  @click="onSubmit"
                  type="button"
                  id="save"
                  class="btn primary btn-block white--text v-size--default"
                >
                  Submit
                </button>
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
    </div>
    <!-- <Preloader v-else /> -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      displaybutton: true,
      progressloading: false,
      searchInput: "",
      snackbar: {
        message: "",
        color: "black",
        show: true,
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
    this.getDepartmentsApi(this.options);
    this.getDevisesDataFromApi();
    this.getEmployeesDataFromApi();
    this.getTimezonesFromApi();
  },
  methods: {
    fetch_logs() {},
    loadDepartmentemployees() {
      //this.loading = true;
      // let page = this.pagination.current;
      let url = this.endpointEmployee;
      console.log("this.departmentselected", this.departmentselected);
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          department_id: this.departmentselected,
          cols: ["id", "employee_id", "display_name"],
        },
      };
      let page = 1;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftEmployees = data.data;
        this.leftEmployees = [];

        this.rightEmployees = [];
        this.rightSelectedEmp = [];
      });
    },
    getDepartmentsApi(options) {
      this.$axios
        .get("departments", options)
        .then(({ data }) => {
          this.departments = data.data;
          this.departments.unshift({ id: "---", name: "Select All" });
        })
        .catch((err) => console.log(err));
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

      $.extend(this.rightEmployees, {
        sdkEmpResponse: "",
      });
      $.extend(this.rightDevices, {
        sdkDeviceResponse: "",
      });
    },
    onSubmit() {
      this.resetErrorMessages();

      if (this.timezonesselected == "") {
        this.response = this.response + "Timezones not selected";
      } else if (this.rightEmployees.length == 0) {
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

      let columnsToFilter = ["systeM_user_id"];
      let onlyUserSystemids = {};
      // $.each(columnsToFilter, function (index, column) {
      //   if (this.timezonesselected.hasOwnProperty(column)) {
      //     onlyUserSystemids[column] = jsonData[column];
      //   }
      // });

      // Define the keys you want to select
      let keysToSelect = ["system_user_id"];

      // Select the specified keys from each object
      let filteredDataEmp = [];
      this.rightEmployees.map(function (obj) {
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
      console.log(filteredDataEmp);

      let options = {
        timezone_id: this.timezonesselected,
        employee_id: this.rightEmployees,
        device_id: this.rightDevices,
        company_id: this.$auth.user.company.id,
        employee_ids: filteredDataEmp,
        device_ids: filteredDataDevices,
      };

      let url = this.endpointUpdatetimezoneStore;

      this.progressloading = true;
      let jsrightEmployees = this.rightEmployees;

      let SDKSuccessStatus = true;
      this.$axios.post(`${url}`, options).then(({ data }) => {
        this.displaybutton = false;
        if (data.record.SDKResponse.data) {
          this.loading = false;

          $.each(this.rightDevices, function (index, rightDevicesobj) {
            let SdkResponseDeviceobject = data.record.SDKResponse.data.find(
              (e) => e.sn == rightDevicesobj.device_id
            );

            console.log("person info error", SdkResponseDeviceobject);
            let deviceStatusResponse = "";
            let EmpStatusResponse = "";

            if (SdkResponseDeviceobject.message == "") {
              deviceStatusResponse = "Success";
            } else if (
              SdkResponseDeviceobject.message == "The device was not found"
            ) {
              deviceStatusResponse = "The device was not found or offline";
              SDKSuccessStatus = false;
            } else if (SdkResponseDeviceobject.message == "person info error") {
              let SDKUseridArray = SdkResponseDeviceobject.userList; //SDK error userslist
              jsrightEmployees.forEach((element) => {
                let systemUserid = element.system_user_id;
                SDKSuccessStatus = false;
                let selectedEmpobject = SDKUseridArray.find(
                  (e) => e.userCode == systemUserid
                );
                EmpStatusResponse = SdkResponseDeviceobject.sdkEmpResponse;
                deviceStatusResponse = "";
                console.log("selectedEmpobject", selectedEmpobject);

                if (EmpStatusResponse != "") {
                  //Adding extra parameters for Employee object
                  if (selectedEmpobject) {
                    $.extend(element, {
                      sdkEmpResponse: "person info error ",
                    });
                  } else {
                    $.extend(element, {
                      sdkEmpResponse: " Success",
                    });
                  }
                }

                console.log("Final - jsrightEmployees", jsrightEmployees);
              });
            } else {
            }

            //Adding extra parameters for Devices object
            $.extend(rightDevicesobj, {
              sdkDeviceResponse:
                deviceStatusResponse != "" ? deviceStatusResponse : " Success",
            });
            this.errors = [];
          });
          this.rightEmployees = jsrightEmployees;
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
          return false;
        }
      });
    },
    goback() {
      this.$router.push("/timezonemapping/list");
    },
    getDevisesDataFromApi(url = this.endpointDevise) {
      //this.loading = true;
      // let page = this.pagination.current;
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          cols: ["id", "location", "name", "device_id"],
        },
      };
      let page = 1;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftDevices = data.data;
      });
    },
    getEmployeesDataFromApi(url = this.endpointEmployee) {
      //this.loading = true;
      // let page = this.pagination.current;
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          cols: ["id", "employee_id", "display_name", "first_name"],
        },
      };
      let page = 1;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.leftEmployees = data.data;
      }, 1000);
    },
    sortObject: (o) =>
      o.sort(function compareByName(a, b) {
        let nameA = a.first_name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
        let nameB = b.first_name.toUpperCase();

        if (nameA < nameB) {
          return -1;
        } else if (nameA > nameB) {
          return 1;
        } else {
          return 0;
        }
      }),
    sortObjectD: (o) =>
      o.sort(function compareByName(a, b) {
        if (a) {
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
        console.log("a", a);

        let nameA = a.name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
        let nameB = b.name.toUpperCase();

        if (nameA < nameB) {
          return -1;
        } else if (nameA > nameB) {
          return 1;
        } else {
          return 0;
        }
      }),
    allmoveLeftemp() {
      this.leftEmployees = this.leftEmployees.concat(this.rightEmployees);
      this.rightEmployees = [];
      this.leftEmployees = this.sortObject(this.leftEmployees);
    },
    allmoveRightEmp() {
      this.rightEmployees = this.rightEmployees.concat(this.leftEmployees);
      this.leftEmployees = [];
      this.rightEmployees = this.sortObject(this.rightEmployees);
    },
    moveLeftemp(id) {
      this.rightSelectedEmp.push(id);
      console.log("leftSelectedEmp", this.rightSelectedEmp);
      console.log("leftSelectedEmp length", this.rightSelectedEmp.length);

      if (!this.rightSelectedEmp.length) return;

      console.log("moveRightEmp", this.rightSelectedEmp);
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
      //console.log("-------End move right--------");

      this.rightSelectedEmp.pop(id);
    },
    moveRightEmp(id, timezone) {
      if (timezone && timezone.timezone_name) {
        return false;
      }

      this.leftSelectedEmp.push(id);

      console.log("Starting move right--------");
      console.log("leftSelectedEmp", this.leftSelectedEmp);
      console.log("leftSelectedEmp length", this.leftSelectedEmp.length);

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
      //console.log("-------End move right--------");

      this.leftSelectedEmp.pop(id);
    },
    /* Devices---------------------------------------- */
    allmoveLeftDevices() {
      this.leftDevices = this.leftDevices.concat(this.rightDevices);
      this.rightDevices = [];
      console.log("this.leftDevices", this.leftDevices);
      this.leftDevices = this.sortObjectD(this.leftDevices);
    },
    allmoveRightDevices() {
      this.rightDevices = this.rightDevices.concat(this.leftDevices);
      this.leftDevices = [];
      console.log("this.rightDevices", this.rightDevices);
      this.rightDevices = this.sortObjectD(this.rightDevices);
    },
    moveLeftDevices(id) {
      // console.log("e)", e);
      this.rightSelectedDevices.push(id);

      console.log("leftSelectedDevices", this.rightSelectedDevices);
      console.log(
        "leftSelectedDevices length",
        this.rightSelectedDevices.length
      );

      if (!this.rightSelectedDevices.length) return;

      console.log("moveRightDevices", this.rightSelectedDevices);
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
      console.log("this.leftDevices", this.leftDevices);
      this.leftDevices = this.sortObjectD(this.leftDevices);
      //console.log("-------End move right--------");

      this.rightSelectedDevices.pop(id);
    },
    moveRightDevices(id) {
      this.leftSelectedDevices.push(id);
      console.log("Starting move right--------");
      console.log("leftSelectedDevices", this.leftSelectedDevices);
      console.log(
        "leftSelectedDevices length",
        this.leftSelectedDevices.length
      );

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
      console.log("this.rightDevices", this.rightDevices);
      this.rightDevices = this.sortObjectD(this.rightDevices);
      //console.log("-------End move right--------");

      this.leftSelectedDevices.pop(id);
    },
  },
};
</script>

<style scoped>
/* @import "../../node_modules/@syncfusion/ej2-base/styles/material.css";
@import "../../node_modules/@syncfusion/ej2-inputs/styles/material.css";
@import "../../node_modules/@syncfusion/ej2-vue-dropdowns/styles/material.css"; */
/* @import "https://netdna.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"; */
/* @media only screen and ((min-width: 1200px)) {
  .container {
    width: 90% !important;
  }
}
@media (min-width: 1200px) {
  .container {
    width: 90% !important;
  }
}
.container {
  width: 90% !important;
} */
/* .container {
  display: grid;
  grid-template-columns: 30% 10% 30%;
  align-items: center;
} */

.container select {
  height: 200px;
  width: 100%;
}

.container .middle {
  text-align: center;
}

.container button {
  width: 80%;
  margin-bottom: 5px;
}

.displaylist {
  height: 225px;
  background: #fff;
  border-bottom-left-radius: 6px;
  border-bottom-right-radius: 6px;
  overflow: auto;
}

.displaylistview {
  padding-left: 10px;
  padding-bottom: 5px;
  padding-top: 0px;
}
</style>
