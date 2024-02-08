<template>
  <div
    style="width: 100% !important"
    v-if="can(`employee_device_photo_upload`)"
  >
    <div class="text-center ma-2">
      <v-snackbar
        color="primary"
        v-model="snackbar.show"
        small
        top="top"
        :timeout="3000"
      >
        Users has been uploaded.
      </v-snackbar>
    </div>

    <v-row>
      <v-col cols="4">
        <v-autocomplete
          @change="getRoomsByFloorId(floor_id)"
          label="Floor Number"
          outlined
          v-model="floor_id"
          :items="floors"
          dense
          item-text="floor_number"
          item-value="id"
          :hide-details="true"
        >
        </v-autocomplete>
      </v-col>

      <v-col cols="4">
        <v-autocomplete
          @change="getTanentsAndMembersByRoom(room_id)"
          label="Room"
          outlined
          v-model="room_id"
          :items="rooms"
          dense
          item-text="room_number"
          item-value="id"
          :hide-details="true"
        >
        </v-autocomplete>
      </v-col>
      <v-col cols="4" class="text-right">
        <SnippetsBack />
      </v-col>
      <v-col cols="5">
        <v-card class="photo-displaylist" style="height: 300px">
          <v-toolbar dense flat style="border: 1px solid #ddd">
            <span> Tanents & Members </span>
          </v-toolbar>
          <v-progress-linear
            v-if="progressloading"
            :active="loading"
            :indeterminate="loading"
            absolute
            color="primary"
          ></v-progress-linear>
          <div style="max-height: 250px; overflow-y: auto; overflow-x: hidden">
            <v-card-text>
              <v-row
                no-gutters
                v-for="user in leftEmployees"
                :id="user.id"
                v-model="leftEmployees"
                :key="user.id"
                style="border-bottom: 1px solid #ddd"
              >
                <v-col cols="1" class="pa-0 ma-0">
                  <v-checkbox
                    dense
                    small
                    primary
                    hide-details
                    v-model="leftSelectedEmp"
                    :value="user.id"
                    :disabled="!user.profile_picture"
                  ></v-checkbox>
                </v-col>

                <v-col cols="1" class="py-1 ma-0">
                  <v-avatar size="40">
                    <v-img
                      :src="
                        user.profile_picture
                          ? user.profile_picture
                          : '/no-profile-image.jpg'
                      "
                    >
                    </v-img>
                  </v-avatar>
                </v-col>
                <v-col col="4" class="pt-2">
                  {{ user.full_name }} {{ user.isPrimaryUser }}
                </v-col>
                <v-col col="3" class="pt-2">
                  {{ user.system_user_id }}
                </v-col>
              </v-row>
            </v-card-text>
          </div>
        </v-card>
      </v-col>

      <v-col cols="2">
        <v-row no-gutters>
          <v-col cols="12">
            <v-btn class="primary" block> Options </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="moveToRightEmpOption2"
            >
              <v-icon> mdi-chevron-right </v-icon>
            </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="allmoveToRightEmp"
            >
              <v-icon> mdi-chevron-double-right </v-icon>
            </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="moveToLeftempOption2"
            >
              <v-icon> mdi-chevron-left </v-icon>
            </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="allmoveToLeftemp"
            >
              <v-icon> mdi-chevron-double-left </v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="5">
        <v-card class="photo-displaylist" style="height: 300px">
          <v-toolbar color=" " dense flat style="border: 1px solid #ddd">
            <span>Selected Tanents </span>
          </v-toolbar>
          <div style="max-height: 250px; overflow-y: auto; overflow-x: hidden">
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
                    v-if="user.profile_picture"
                    dense
                    small
                    hideDetails
                    v-model="rightSelectedEmp"
                    :value="user.id"
                    primary
                    hide-details
                  ></v-checkbox>
                  <v-checkbox
                    style="padding: 0px"
                    v-else
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
                  <v-avatar>
                    <v-img
                      :src="
                        user.profile_picture
                          ? user.profile_picture
                          : '/no-profile-image.jpg'
                      "
                    >
                    </v-img>
                  </v-avatar>
                </v-col>
                <v-col md="3" style="padding: 0px; padding-top: 5px">
                  {{ user.full_name }}
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
        <v-card class="photo-displaylist" style="height: 300px">
          <v-toolbar color=" " dense flat style="border: 1px solid #ddd">
            <span> Devices</span>
          </v-toolbar>
          <div style="max-height: 250px; overflow-y: auto; overflow-x: hidden">
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
        <v-row no-gutters>
          <v-col cols="12">
            <v-btn class="primary" block> Options </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="moveToRightDevicesOption2"
            >
              <v-icon> mdi-chevron-right </v-icon>
            </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="allmoveToRightDevices"
            >
              <v-icon> mdi-chevron-double-right </v-icon>
            </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="moveToLeftDevicesOption2"
            >
              <v-icon> mdi-chevron-left </v-icon>
            </v-btn>
          </v-col>

          <v-col cols="12">
            <v-btn
              class="popup_background my-1"
              block
              @click="allmoveToLeftDevices"
            >
              <v-icon> mdi-chevron-double-left </v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="5">
        <v-card class="photo-displaylist" style="height: 300px">
          <v-toolbar color=" " dense flat style="border: 1px solid #ddd">
            <span>Selected Devices</span>
          </v-toolbar>
          <div style="max-height: 250px; overflow-y: auto; overflow-x: hidden">
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
    <v-row>
      <v-col cols="12" class="text-right">
        <div>
          <span v-if="errors && errors.message" class="text-danger mt-2">{{
            errors.message
          }}</span>
        </div>

        <v-btn
          :disabled="!displaybutton"
          :loading="loading"
          @click="submit"
          id="save"
          class="primary"
        >
          Submit
        </v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  data() {
    return {
      floor_id: [],
      floors: [],
      room_id: [],
      rooms: [],
      isCompany: true,
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
    this.progressloading = true;
    await this.getFloors();
  },
  methods: {
    async getFloors() {
      let { data: floors } = await this.$axios.get(`floor`, {
        params: { company_id: this.$auth.user.company_id },
      });
      this.floors = floors.data;
    },
    async getRoomsByFloorId(floor_id) {
      let { data } = await this.$axios.get(`room-by-floor-id`, {
        params: {
          company_id: this.$auth.user.company_id,
          floor_id: floor_id,
        },
      });
      this.rooms = data;
    },
    async getTanentsAndMembersByRoom(room_id) {
      let { data } = await this.$axios.get(`tanents-and-members-by-room-id`, {
        params: {
          company_id: this.$auth.user.company_id,
          room_id: room_id,
        },
      });

      let result = [];
      data.forEach((e) => {
        result.push({
          id: e.id,
          full_name: e.full_name,
          system_user_id: parseInt(e.system_user_id),
          profile_picture: e.profile_picture,
          isPrimaryUser: "(Primary Member)",
        });

        e.members.forEach((m) => {
          result.push({
            id: e.id + m.id,
            full_name: m.full_name,
            system_user_id: m.system_user_id,
            profile_picture: m.profile_picture,
          });
        });
      });

      this.leftEmployees = result;

      this.getDevisesDataFromApi();
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    resetErrorMessages() {
      this.errors = [];
      this.response = "";

      this.leftEmployees.forEach((element) => {
        element["sdkEmpResponse"] = "";
      });
      this.leftDevices.forEach((element) => {
        element["sdkDeviceResponse"] = "";
      });
    },
    getDevisesDataFromApi() {
      this.$axios
        .get(this.endpointDevise, {
          params: {
            per_page: 1000, //this.pagination.per_page,
            company_id: this.$auth.user.company_id,
            sortBy: "status_id",
            //cols: ["id", "location", "name", "device_id", "status:id"],
          },
        })
        .then(({ data }) => {
          this.leftDevices = data.data;
        });
    },
    sortObject: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.full_name && b.full_name) {
          let nameA = a.full_name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.full_name.toUpperCase();

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
    selectLeftEmployee(id) {
      this.leftSelectedEmp.push(id);
    },
    moveToRightEmpOption2() {
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
    async submit() {
      this.loading = true;
      if (this.rightEmployees.length == 0) {
        this.response = this.response + " Atleast select one Employee Details";
      } else if (this.rightDevices.length == 0) {
        this.response = this.response + " Atleast select one Device Details";
      }

      this.loading_dialog = true;
      this.errors = [];

      let personListArray = [];

      this.rightEmployees.forEach((item) => {
        let person = {
          name: item.full_name,
          userCode: parseInt(item.system_user_id),
          faceImage: item.profile_picture,
          // process.env.APP_ENV != "local"
          //   ? item.profile_picture
          //   : "https://backend.mytime2cloud.com/media/employee/profile_picture/1697544063.jpg",
        };
        personListArray.push(person);
      });

      let payload = {
        personList: personListArray,
        snList: this.rightDevices.map((e) => e.device_id),
      };

      if (payload.snList && payload.snList.length === 0) {
        alert(`Atleast one device must be selected`);
        return false;
      }

      this.devices_dialog.forEach((e) => {
        e.state = "---";
        e.message = "---";
      });

      //try {
      let endpoint = `/Person/AddRange/Photos`;

      const { data } = await this.$axios.post(endpoint, payload);

      if (data.status == 200) {
        this.loading_dialog = false;

        this.snackbar.show = true;
        this.response = "Employee(s) Pictures has been uploaded";

        let jsrightEmployees = this.rightEmployees;
        jsrightEmployees.forEach((element) => {
          element["sdkEmpResponse"] = "Success";
        });
        this.rightDevices.forEach((elementDevice) => {
          elementDevice["sdkDeviceResponse"] = "Success";
          this.errors = [];
          this.loading = false;
        });
      } else {
        this.loading_dialog = false;
        this.snackbar.show = true;
        this.response = data.message;

        this.loading = false;
      }

      this.displaybutton = true;
    },
  },
};
</script>
