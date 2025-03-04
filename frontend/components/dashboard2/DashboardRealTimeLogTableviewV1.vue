<template>
  <div>
    <v-row>
      <v-col md="10" sm="10" xs="10" class="pl-5">
        <span style="float: left"> <h4>Live Employee Logs on Devices</h4></span>
        <span title="Click to Reload Logs"
          ><v-btn
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            title="Reload"
          >
            <v-icon class="ml-2" @click="getRecords(true)" dark
              >mdi mdi-reload</v-icon
            >
          </v-btn></span
        >
      </v-col>

      <v-col md="2" sm="2" xs="2" class="text-end">
        <v-menu bottom left>
          <template v-slot:activator="{ on, attrs }">
            <v-btn dark-2 icon v-bind="attrs" v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list width="120" dense>
            <v-list-item @click="viewLogs()">
              <v-list-item-title style="cursor: pointer">
                View Logs
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-col>
    </v-row>
    <v-dialog persistent v-model="dialogEmployeeAttendance" width="1200px">
      <v-card>
        <v-card-title dense class="primary white--text background">
          Employee Attendance
          <v-spacer></v-spacer>
          <v-icon
            @click="dialogEmployeeAttendance = false"
            outlined
            dark
            color="white"
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-container>
            <DashboardEmployeeAttendanceLog
              :system_user_id="system_user_id"
              :key1="componentKey"
              :key="componentKey"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <ComonPreloader icon="face-scan" v-if="loading" />
    <v-dialog v-model="dialog" max-width="550">
      <v-card>
        <v-container>
          <v-row no-gutters class="pa-0 ma-0">
            <v-col class="text-right">
              <v-icon color="primary" @click="dialog = false">
                mdi-close-circle-outline
              </v-icon>
            </v-col>
          </v-row>

          <EmployeeShortView :item="selectedItem" />
        </v-container>
      </v-card>
    </v-dialog>
    <v-data-table
      @click:row="showDialog"
      dense
      :headers="headers_table"
      :items="logs"
      model-value="data.id"
      :loading="tableloading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [5, 10],
      }"
      class="elevation-0 logtable"
      :server-items-length="totalRowsCount"
    >
      <template v-slot:item.sno="{ item, index }">
        {{
          currentPage
            ? (currentPage - 1) * perPage +
              (cumulativeIndex + logs.indexOf(item))
            : ""
        }}
      </template>

      <template v-slot:item.employee.first_name="{ item, index }">
        <v-row no-gutters>
          <v-col
            style="
              padding: 5px;
              padding-left: 0px;
              width: 40px;
              max-width: 40px;
            "
          >
            <v-img
              style="
                border-radius: 50%;
                height: auto;
                width: 40px;
                max-width: 40px;
              "
              :src="
                item.employee && item.employee.profile_picture
                  ? item.employee.profile_picture
                  : '/no-profile-image.jpg'
              "
            >
            </v-img>
          </v-col>
          <v-col style="padding: 10px">
            <span class="ml-2" small>
              {{ item.employee.first_name ?? "---" }}
              {{ item.employee.last_name ?? "---" }}
            </span>
            <div class="secondary-value ml-2">
              {{ item.employee?.designation?.name }}
            </div>
          </v-col>
        </v-row>
      </template>
      <template v-slot:item.employee.department="{ item }">
        {{
          item.employee && item.employee.department
            ? caps(item.employee.department.name)
            : "---"
        }}
        <div class="secondary-value">
          {{
            item.employee && item.employee.sub_department
              ? caps(item.employee.sub_department.name)
              : "---"
          }}
        </div>
      </template>

      <template v-slot:item.UserID="{ item }"> #{{ item.UserID }} </template>
      <template v-slot:item.employee.employee_id="{ item }">
        {{ item.employee && item.employee.employee_id }}
      </template>
      <template v-slot:item.LogTime="{ item }">
        {{ item.LogTime }}
      </template>

      <template v-slot:item.branch="{ item }">
        {{ item?.employee?.branch?.branch_name }} <br />
        {{ item?.employee?.department?.name }}
      </template>

      <template v-slot:item.LogTime="{ item }">
       <span :class="`${item?.device?.name == 'Manual' ? 'red' : ''}--text`">
        {{ item.LogTime }}
       </span>
      </template>     

      <template v-slot:item.device.device_name="{ item }">
        <div class="secondary-value" v-if="item.DeviceID?.includes(`Mobile`)">
          Mobile <br />
          {{ item.gps_location }}
        </div>
        <div class="secondary-value" v-else>
          {{ item.device ? caps(item.device.name) : "---" }} <br />
          {{
            item.device && item.device.location ? item.device.location : "---"
          }}
        </div>
      </template>
    </v-data-table>
  </div>
</template>

<script>
export default {
  props: ["system_user_id", "branch_id"],
  data() {
    return {
      dialog: false,
      selectedItem: {},
      totalRowsCount: 0,
      perPage: 10,
      cumulativeIndex: 1,
      currentPage: 1,

      componentKey: 1,

      dialogEmployeeAttendance: false,
      loading: false,
      tableloading: false,
      items: [],
      emptyLogmessage: "",
      number_of_records: 5,
      logs: [],
      url: process.env.SOCKET_ENDPOINT,
      socket: null,

      total: 0,
      options: {},
      headers_table: [
        {
          text: "#",
          align: "left",
          sortable: true,
          filterable: true,

          value: "sno",
        },
        {
          text: "Employee Name",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee.first_name",
        },
        {
          text: "Branch/Department",
          align: "left",
          sortable: false,
          filterable: true,
          value: "branch",
        },
        {
          text: "Time",
          align: "left",
          sortable: true,
          filterable: true,

          value: "LogTime", //edit purpose
        },
        {
          text: "Device Name",
          align: "left",
          sortable: true,
          filterable: true,

          value: "device.device_name",
        },
        {
          text: "Mode",
          align: "left",
          sortable: true,
          filterable: true,

          value: "mode",
        },
        // {
        //   text: "Log",
        //   align: "left",
        //   sortable: true,
        //   filterable: true,

        //   value: "log",
        // },
      ],
      pagination: {
        current: 1,
        total: 0,
        per_page: 10,
      },
      devices_list: [],
    };
  },
  watch: {
    options: {
      handler() {
        setTimeout(() => {
          this.getRecords();
        }, 1000 * 5);
      },
      deep: true,
    },

    branch_id() {
      if (this.branch_id != "") {
        this.$store.commit("dashboard/recent_logs", null);
        setTimeout(() => {
          this.getRecords();
        }, 1000 * 5);
      }
    },
  },
  mounted() {
    // setTimeout(() => {
    //   this.socketConnection();
    // }, 1000 * 10);

    setInterval(() => {
      if (this.$route.name == "dashboard") {
        if (this.$auth.user) this.getRecords(true);
      }
    }, 1000 * 60);
  },
  created() {
    let payload = {
      params: {
        company_id: this.$auth.user.company_id,
      },
    };
    //this.devices_list = this.$store.dispatch("devices_list", options);
    this.$axios.get(`device-list`, payload).then(({ data }) => {
      this.devices_list = data;
    });
  },
  computed: {
    employees() {
      // return this.$store.state.employeeList.map((e) => ({
      //   system_user_id: e.system_user_id,
      //   first_name: e.first_name,
      //   last_name: e.last_name,
      //   display_name: e.display_name,
      // }));
      return this.$store.state.employeeList.map((e) => ({
        employee: {
          profile_picture: e.profile_picture,
          first_name: e.first_name,
          last_name: e.last_name,
          designation: e.designation,
          department: e.department,
          sub_department: e.sub_department,
          UserID: e.user_id,
          employee_id: e.employee_id,
          system_user_id: e.system_user_id,
        },
      }));
    },
    devices() {
      if (this.$store.state.devices)
        return this.$store.state.devices.map((e) => e.device_id);
      else {
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
          },
        };
        return this.$store.dispatch("devices_list", options);
      }
    },
  },
  methods: {
    showDialog(item) {
      this.selectedItem = item;
      this.dialog = true;
    },
    viewLogs() {
      this.$router.push("/devicelogs");
    },
    viewLog(system_user_id) {
      this.dialogEmployeeAttendance = true;
      this.componentKey = this.componentKey + 1;
      this.system_user_id = system_user_id;
    },
    caps(str) {
      if (str == "" || (str == null && str == undefined)) {
        return "---";
      } else if (str) {
        let res = str.toString();
        res.replace(/\b\w/g, (c) => c.toUpperCase());
        return str.includes(`Mobile`) ? "Mobile" : str;
      }
    },
    getRecords(socket = false) {
      this.tableloading = true;
      // this.loading = true;
      if (!socket) this.loading = true;

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
          branch_id: this.branch_id > 0 ? this.branch_id : null,
        },
      };

      this.currentPage = page;
      this.perPage = itemsPerPage;
      // if (page == 1 && !socket) {
      //   if (this.$store.state.dashboard.recent_logs) {
      //     this.loading = false;
      //     this.tableloading = false;
      //     this.logs = this.$store.state.dashboard.recent_logs;

      //     this.pagination.current = this.logs.current_page;
      //     this.pagination.total = this.logs.last_page;

      //     this.totalRowsCount = this.logs.total;
      //     return;
      //   }
      // }
      this.$axios
        .get(
          `device/getLastRecordsHistory/${this.$auth.user.company_id}/10`,
          options
        )
        .then(({ data }) => {
          this.logs = data.data;
          this.$store.commit("dashboard/recent_logs", data);
          this.loading = false;
          this.tableloading = false;

          this.pagination.current = data.current_page;
          this.pagination.total = data.last_page;

          this.totalRowsCount = data.total;
        });
    },
    getDetails(item) {
      let DeviceId = item.SN;

      if (DeviceId != "") {
        let isCompanyDevice = this.devices_list.filter(
          (e) => e.device_id == DeviceId
        );

        if (isCompanyDevice.length > 0) {
          this.tableloading = true;
          //this.getRecords(true);
          try {
            this.pushSocketEmployeeToTable(item);
          } catch (e) {
            console.log(e);
          }

          this.tableloading = false;
        }
      }
    },
    pushSocketEmployeeToTable(item) {
      //  console.log("pushSocketEmployee", item);
      //--------------------------
      let UserCode1 = item.UserCode;
      let SN1 = item.SN;
      let employee = this.employees.find(
        (e) => e.employee.system_user_id == UserCode1
      );
      //  console.log("pushSocketEmployee employee", employee);
      let device = this.devices_list.find((e) => e.device_id == SN1);

      let itemTable = {
        employee: employee.employee,
        device: { location: device.location, name: device.name },
        LogTime: this.setTime(item.RecordDate),
      };

      this.logs = [itemTable, ...this.logs];
      //this.logs.unshift(itemTable);
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);
      //console.log("this.$store.state.devices", this.devices);
      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data).Data;

        const { UserCode, SN, RecordDate, RecordNumber, RecordImage } = json;
        // console.log("socket UserCode", UserCode);
        if (UserCode > 0) {
          this.getDetails(json);
        }
      };
    },
    setTime(dateTimeString) {
      try {
        const dateTime = new Date(dateTimeString);
        const hours = dateTime.getHours().toString().padStart(2, "0");
        const minutes = dateTime.getMinutes().toString().padStart(2, "0");
        return `${hours}:${minutes}`;
      } catch (e) {
        return "--:--";
      }
    },
  },
};
</script>