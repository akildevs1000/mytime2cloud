<template>
  <div>
    <v-skeleton-loader v-if="logs && !logs.length" type="card" />
    <div v-else>
      <v-toolbar flat>
        <h5>
          <b>
            Employees data(Recent Logs)
          </b>
        </h5>
        <v-spacer />
      </v-toolbar>
      <v-row>
        <v-col>
          <v-card class="mb-5" elevation="0">
            <v-toolbar class="rounded-md" color="background" dense flat dark>
              <v-toolbar-title><span> Recent Logs</span></v-toolbar-title>
              <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dense class="ma-0 px-0" x-small :ripple="false" text v-bind="attrs" v-on="on">
                    <v-icon color="white" class="ml-2" @click="getRecords()" dark>mdi mdi-reload</v-icon>
                  </v-btn>
                </template>
                <span>Reload</span>
              </v-tooltip>

            </v-toolbar>
            <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
              {{ snackText }}

              <template v-slot:action="{ attrs }">
                <v-btn v-bind="attrs" text @click="snack = false">
                  Close
                </v-btn>
              </template>
            </v-snackbar>
            <v-data-table dense :headers="headers_table" :items="logs" model-value="data.id" :loading="loading"
              :options.sync="options" :footer-props="{
                itemsPerPageOptions: [50, 100, 500, 1000],
              }" class="elevation-1">

              <template v-slot:item.sno="{ item, index }">

                <b>{{ ++index }}</b>
              </template>
              <template v-slot:item.profilepic="{ item }">
                <v-img class="gg" viewBox="0 0 100 100" style="border-radius: 50%;   max-width: 50px !important" :src="(item.employee && item.employee.profile_picture) ||
                  '/no-profile-image.jpg'
                  "></v-img>

              </template>

              <template v-slot:item.employee.first_name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @cancel="getRecords()"
                  @save="getRecords()">
                  {{ item.employee.first_name }} {{ item.employee.last_name }}
                  <template v-slot:input>
                    <v-text-field v-model="datatable_search_textbox" @input="getRecords('search_employee_name', $event)"
                      label="Search Employee Name"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.UserID="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @cancel="getRecords()"
                  @save="getRecords()" @open="datatable_open">
                  {{ item.UserID }}
                  <template v-slot:input>
                    <v-text-field v-model="datatable_search_textbox" @input="getRecords('search_system_user_id', $event)"
                      label="Search System User Id"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.employee.employee_id="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @cancel="getRecords()"
                  @save="getRecords()" @open="datatable_open">
                  {{ item.employee.employee_id }}
                  <template v-slot:input>
                    <v-text-field v-model="datatable_search_textbox" @input="getRecords('search_employee_id', $event)"
                      label="Search Employee ID"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.time="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @cancel="getRecords()"
                  @save="getRecords()" @open="datatable_open">
                  {{ item.time }}
                  <template v-slot:input>
                    <v-text-field v-model="datatable_search_textbox" label="Search Time"
                      @input="getRecords('search_time', $event)"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.device.device_name="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @cancel="getRecords()"
                  @save="getRecords()" @open="datatable_open">
                  {{ item.device ? item.device.device_name : '---' }}
                  <template v-slot:input>
                    <v-text-field v-model="datatable_search_textbox" @input="getRecords('search_device_name', $event)"
                      label="Search Device Name"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.device.device_id="{ item }">
                <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;" @cancel="getRecords()"
                  @save="getRecords()" @open="datatable_open">
                  {{ item.device ? item.device.device_id : '---' }}
                  <template v-slot:input>
                    <v-text-field v-model="datatable_search_textbox" @input="getRecords('search_device_id', $event)"
                      label="Search Device ID"></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
            </v-data-table>

          </v-card>
        </v-col>
      </v-row>

      <!-- <table>
        <tr>
          <th v-for="(item, index) in headers" :key="index">
            <span v-html="item.text"></span>
          </th>
        </tr>
        <tr v-for="(item, index) in logs" :key="index">
          <td class="ps-3">
            <b>{{ ++index }}</b>
          </td>
          <td>
            <v-img class="gg" viewBox="0 0 100 100" style="border-radius: 50%;  height: 80px; max-width: 80px !important"
              :src="(item.employee && item.employee.profile_picture) ||
                '/no-profile-image.jpg'
                "></v-img>
          </td>
          <td>{{ item.employee && item.employee.first_name }}</td>
          <td>EID: {{ item.UserID }}</td>
          <td>
            <span>{{ item && item.time }} </span><small>Time</small>
          </td>
          <td>
            <span>{{ (item.device && item.device.short_name) || "---" }}</span><small>Device</small>
          </td>
        </tr>
      </table> -->

      <!-- <v-slide-group class="px-4 pb-3" active-class="success" show-arrows>
        <div></div>
        <v-slide-item v-for="(item, index) in logs" :key="index">
          <div class="card mx-2 my-2 w-25">
            <div class="banner">
              <v-img
                class="gg"
                viewBox="0 0 100 100"
                style="border-radius: 50%;  height: 80px; max-width: 80px !important"
                :src="
                  (item.employee && item.employee.profile_picture) ||
                    '/no-profile-image.jpg'
                "
              ></v-img>
            </div>
            <div class="menu">
              <div class="opener"></div>
            </div>
            <h2 class="text-center pa-1" style="font-size:15px">
              {{ item.employee && item.employee.first_name }}
            </h2>
            <div class="title" style="font-size:12px !important">
              EID: {{ item.UserID }}
            </div>
            <div class="title" style="font-size:12px !important"></div>
            <div class="actions">
              <div class="follow-info">
                <h2>
                  <a href="#"
                    ><span>{{ item && item.time }} </span><small>Time</small></a
                  >
                </h2>
                <h2>
                  <a href="#"
                    ><span>{{
                      (item.device && item.device.short_name) || "---"
                    }}</span
                    ><small>Device</small></a
                  >
                </h2>
              </div>
            </div>
          </div>
        </v-slide-item>
      </v-slide-group>-->
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    datatable_search_textbox: '',
    datatable_searchById: '',
    filter_employeeid: '',
    snack: false,
    snackColor: '',
    snackText: '',
    Model: "Device",
    pagination: {
      current: 1,
      total: 0,
      per_page: 10
    },
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    data: [],
    loading: false,
    total: 0,
    headers: [
      { text: "&nbsp #" },
      { text: "Pic" },
      { text: "Name" },
      { text: "E.id" },
      { text: "Last Active" },
      { text: "Device name" }
    ],
    editedIndex: -1,
    response: "",
    errors: [],

    number_of_records: 20,
    logs: [],
    url: process.env.SOCKET_ENDPOINT,
    socket: null,
    headers_table: [

      {
        text: "#",
        align: "left",
        sortable: false,

        value: "sno",
      },
      {
        text: "Profile Pic",
        align: "left",
        sortable: false,

        value: "profilepic",
      },
      {
        text: "Employee Name",
        align: "left",
        sortable: true,
        key: "employee",
        value: "employee.first_name",
      },
      {
        text: "E.ID",
        align: "left",
        sortable: true,
        key: "UserID",
        value: "UserID",
      },
      {
        text: "Employee   Id",
        align: "left",
        sortable: true,

        key: "employeeid",
        value: "employee.employee_id",
      },
      {
        text: "Time",
        align: "left",
        sortable: true,
        key: "time", //sorting
        value: "time", //edit purpose
      },
      {
        text: "Device Name",
        align: "left",
        sortable: true,
        value: "devicename",
        value: "device.device_name",
      },
      {
        text: "Device ID",
        align: "left",
        sortable: true,
        key: "deviceid",
        value: "device.device_id",
      },
    ],

  }),
  // data() {
  //   return {
  //     number_of_records: 10,
  //     logs: [],
  //     url: process.env.SOCKET_ENDPOINT,
  //     socket: null
  //   };
  // },
  mounted() {
    this.socketConnection();

    this.getRecords();
  },
  created() {
    // this.getRecords();
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

    },
    getRecords(filter_column = '', filter_value = '') {
      this.loading = true;


      if (filter_value != '' && filter_value.length <= 2) {

        this.snack = true;
        this.snackColor = 'error';
        this.snackText = 'Minimum 3 Characters to filter ';
        this.loading = false;
        return false;
      }
      //let filter_value = this.datatable_search_textbox;
      let options = {
        params: {
          filter_column: filter_value,

        },
      };
      if (filter_column != '')
        options.params[filter_column] = filter_value;
      this.$axios
        .get(
          `device/getLastRecordsByCount/${this.$auth.user.company.id}/${this.number_of_records}`
          , options)
        .then(res => {

          if (filter_column != '' && res.data.length == 0) {

            this.snack = true;
            this.snackColor = 'error';
            this.snackText = 'No Results Found';
            this.loading = false;
            return false;
          }

          this.logs = res.data;
          this.loading = false;


        });
    },
    getShortName(item) {
      if (!item) {
        return false;
      }
      return item
        .split(" ")
        .slice(0, 2)
        .join(" ");
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);

      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data);
        if (json.Status == 200 && json.Data.UserCode !== 0) {
          this.getDetails(json.Data);
        }
      };
    },
    getDetails(item) {
      item.company_id = this.$auth.user.company.id;

      this.$axios.post(`/device/details`, item).then(({ data }) => {
        if (
          data.device &&
          this.$auth.user &&
          data.device.company_id == this.$auth.user.company.id
        ) {
          this.logs.unshift(data);
        }
      });
    }
  }
};
</script>
