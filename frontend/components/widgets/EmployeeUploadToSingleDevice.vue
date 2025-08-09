<template>
  <v-dialog
    persistent
    v-model="dialog"
    width="800"
    :key="system_user_id"
    :loading="loading"
  >
    <v-snackbar v-model="snackbar" small top="top" timeout="5000">
      {{ response }}
    </v-snackbar>
    <WidgetsClose left="790" @click="close" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon :size="`small`">mdi-cellphone-text</v-icon>
        Upload To Device
      </span>
    </template>

    <v-card>
      <v-card-title dense class="popup_background">
        Single Employee - Upload To Single Device
        <v-spacer></v-spacer>
      </v-card-title>
      <!-- <v-alert class="white--text primary pa-2" dense>
        <div class="white--text primary pa-2">
          <div class="">Update From Device</div>
          <v-icon
            class="cursor-pointer"
            @click="export_submit"
            title="Download"
          >
            mdi-download
          </v-icon>
        </div>
      </v-alert> -->

      <v-card-text :loading="loading">
        <div class="d-flex justify-space-between mt-3">
          <v-row
            ><v-col cols="6"></v-col>
            <v-col cols="6">
              <v-autocomplete
                v-model="selectedDeviceId"
                :items="[
                  { name: 'Select Device', device_id: null, status_id: null },
                  ...devices,
                ]"
                item-text="name"
                item-value="device_id"
                :disabled="loading"
                dense
                outlined
                clearable
                hide-details
                placeholder="Device"
              >
                <template v-slot:item="{ item, on, attrs }">
                  <v-list-item v-bind="attrs" v-on="on">
                    <v-list-item-content>
                      <v-list-item-title
                        :style="{
                          color: item.status_id === 1 ? 'green' : 'red',
                        }"
                      >
                        {{ item.name }}
                      </v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </template>
              </v-autocomplete>
            </v-col>
          </v-row>

          &nbsp;
        </div>

        <!-- <div class="my-2">
          <WidgetsProgress
            :key="selectedDeviceId"
            :total="totalEmployees"
            :engaged="engaged"
          />
        </div> -->

        <span v-if="employee">
          <div style="overflow-y: auto; max-height: 400px" class="px-2">
            <v-simple-table dense>
              <template v-slot:default>
                <tbody>
                  <!-- {{
                  data
                }} -->
                  <tr>
                    <td>
                      {{ employee.first_name ? caps(employee.first_name) : "" }}
                      {{ employee.last_name ? caps(employee.last_name) : "" }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div
                        style="
                          padding: 5px;
                          padding-left: 0px;
                          width: 50px;
                          max-width: 50px;
                        "
                      >
                        <v-avatar>
                          <v-img
                            :src="
                              employee.profile_picture
                                ? employee.profile_picture
                                : '/no-profile-image.jpg'
                            "
                          >
                          </v-img>
                        </v-avatar>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ caps(employee.designation.name) }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ employee.system_user_id }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ caps(employee.department.name) }}
                    </td>
                  </tr>
                  <tr>
                    <td>{{ caps(employee.sub_department.name) }}</td>
                  </tr>
                  <tr>
                    <td>
                      {{ employee.phone_number }}
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </div>
          <div class="pt-5">
            <v-row
              ><v-col></v-col
              ><v-col cols="3" class="text-right"
                ><v-btn
                  style="width: 150px"
                  :disabled="loading"
                  :loading="loading"
                  block
                  class="primary"
                  small
                  @click="submit"
                  >Upload to Device</v-btn
                ></v-col
              >

              <v-col
                ><div style="color: red">
                  {{ deviceResponses }}
                </div></v-col
              >
            </v-row>
          </div>
        </span>

        <div class="pt-5 red--text" v-if="response">
          {{ response }}
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  props: ["system_user_id", "id", "employee"],
  data: () => ({
    snackbar: false,

    selectedDeviceId: null,
    totalEmployees: 0,
    engaged: 0,
    selectAll: false,
    dialog: false,
    devices: [],

    loading: false,
    response: null,
    company_id: 1,
    responses: [],
    deviceResponses: "",
  }),
  async created() {
    //this.loading = true;
    // let page = this.pagination.current;
    await this.getDevices();
  },
  watch: {},
  methods: {
    close() {
      this.dialog = false;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    async getDevices() {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.company_id,
          sortBy: "name",
          cols: ["name", "device_id", "status:id"],
        },
      };
      let { data } = await this.$axios.get(`/device`, options);
      this.devices = data.data;

      // this.devices.map((e) => (e.name = e.name + (e.status_id==1?' - Online':' - Offline')));
    },

    async submit() {
      if (this.selectedDeviceId == null) {
        this.response = "Select Device";
        this.snackbar = true;

        return false;
      }
      this.response = " ";

      this.loading = true;
      // const deviceId = this.rightDevices[0].id;

      // Proceed only if we should not skip
      {
        console.log(this.employee);
        let person = {
          name: `${this.employee.first_name} ${this.employee.last_name}`,
          userCode: parseInt(this.employee.system_user_id),
          profile_picture_raw: this.employee.profile_picture_raw,
          faceImage: this.employee.profile_picture,
        };

        if (this.employee.rfid_card_number) {
          person.cardData = this.employee.rfid_card_number;
        }

        if (this.employee.rfid_card_password) {
          person.password = this.employee.rfid_card_password;
        }

        if (this.employee.finger_prints.length > 0) {
          person.fp = this.employee.finger_prints.map((e) => e.fp);
        }

        if (this.employee.palms.length > 0) {
          person.palm = this.employee.palms.map((e) => e.palm);
        }

        let personListArray = [person];

        let payload = {
          personList: personListArray,
          snList: [this.selectedDeviceId],
        };

        // try {
        let { data } = await this.$axios.post(`/SDK/AddPerson`, payload);

        // console.log("data", data);
        this.loading = false;

        let message = data.deviceResponse[0].sdk_response.message;

        if (
          message === "没有对应的命令" ||
          message === "设备未连接到服务器或者未注册" ||
          /[^\u0000-\u007F]+/.test(message)
        ) {
          this.deviceResponses = "Unable to connect Device. Please try again";
        } else if (message === 200) {
          this.deviceResponses = "Uploaded";
        } else if (message) this.deviceResponses = message;

        // this.deviceResponses = "Unable to connect Device. Please try again";

        // console.log("this.deviceResponses", this.deviceResponses);

        // this.deviceResponses = "Unable to connect Device. Please try again ";
        // if (data.deviceResponse[0]?.sdk_response?.status == 102) {
        //   this.deviceResponses =
        //     "Unable to connect Device. Please try again ";
        // } else if (data.cameraResponse[0]?.status == 200) {
        //   this.deviceResponses = "Uplaoded Successfully";
        // } else if (data.cameraResponse2[0]?.status == 200) {
        //   this.deviceResponses = "Uplaoded Successfully";
        // } else if (data.deviceResponse[0]?.sdk_response?.status == 200) {
        //   this.deviceResponses = "Uplaoded Successfully";
        // } else if (data.deviceResponse[0]?.sdk_response?.message) {
        //   this.deviceResponses =
        //     data.deviceResponse[0]?.sdk_response?.message;
        // } else if (data.deviceResponse[0]?.message) {
        //   this.deviceResponses = data.deviceResponse[0]?.message;
        // }
        // } catch (error) {
        //   console.log(`Error for ${person.name}:`, error);
        // }
      } //check is uploaded or not
    }, //for
  },
};
</script>
