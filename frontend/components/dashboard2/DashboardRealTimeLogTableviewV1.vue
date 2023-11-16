<template>
  <div>
    <v-row>
      <v-col md="10" sm="10" xs="10" class="pl-5">
        <h4>Live Employee Logs on Devices</h4>
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

    <v-data-table
      style="overflow-y: scroll"
      dense
      :headers="headers_table"
      :items="logs.data"
      model-value="data.id"
      :loading="tableloading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [5, 10],
      }"
      class="elevation-0"
      :server-items-length="totalRowsCount"
    >
      <template v-slot:item.sno="{ item, index }">
        {{
          currentPage
            ? (currentPage - 1) * perPage +
              (cumulativeIndex + logs.data.indexOf(item))
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
            {{ item.employee ? item.employee.first_name : "---" }}
            {{ item.employee ? item.employee.last_name : "---" }}
            <div class="secondary-value">
              {{
                item.employee && item.employee.designation
                  ? caps(item.employee.designation.name)
                  : "---"
              }}
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

      <template v-slot:item.online="{ item }">
        <v-icon v-if="item.device && item.device.location" color="green" fill
          >mdi-map-marker-radius</v-icon
        >
        <v-icon v-else color="red" fill>mdi-map-marker-radius</v-icon>
      </template>
      <template v-slot:item.device.device_name="{ item }">
        <div>
          {{ item.device ? caps(item.device.name) : "---" }} <br />
          <div class="secondary-value">
            {{
              item.device && item.device.location ? item.device.location : "---"
            }}
          </div>
        </div>
      </template>
      <!-- <template v-slot:item.log="{ item }">
        <v-btn @click="viewLog(item.UserID)">View Log</v-btn>
      </template> -->
    </v-data-table>
  </div>
</template>

<script>
export default {
  props: ["system_user_id", "branch_id"],
  data() {
    return {
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
        // {
        //   text: "#E.ID",
        //   align: "left",
        //   sortable: true,
        //   filterable: true,

        //   value: "UserID",
        // },
        {
          text: "#",
          align: "left",
          sortable: true,
          filterable: true,

          value: "sno",
        },
        {
          text: "Employee   Id",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee.employee_id",
        },

        {
          text: "Employee Name",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee.first_name",
        },
        {
          text: "Department",
          align: "left",
          sortable: false,
          filterable: true,

          value: "employee.department",
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
          text: "Online/Offline",
          align: "left",
          sortable: true,
          filterable: true,

          value: "online",
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
    };
  },
  watch: {
    options: {
      handler() {
        this.getRecords();
      },
      deep: true,
    },

    branch_id() {
      this.$store.commit("dashboard/recent_logs", null);
      setTimeout(() => {
        this.getRecords();
      }, 500);
    },
  },
  mounted() {
    setTimeout(() => {
      this.socketConnection();
    }, 1000 * 10);

    //this.getRecords();
  },
  created() {},
  computed: {
    employees() {
      return this.$store.state.employees.map((e) => ({
        system_user_id: e.system_user_id,
        first_name: e.first_name,
        last_name: e.last_name,
        display_name: e.display_name,
      }));
    },
    devices() {
      return this.$store.state.devices.map((e) => e.device_id);
    },
  },
  methods: {
    viewLogs() {
      this.$router.push("/devicelogs");
    },
    viewLog(system_user_id) {
      this.dialogEmployeeAttendance = true;
      this.componentKey = this.componentKey + 1;
      this.system_user_id = system_user_id;
      console.log(this.system_user_id, this.componentKey);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getRecords() {
      this.tableloading = true;
      this.loading = true;

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
      if (page == 1) {
        if (this.$store.state.dashboard.recent_logs) {
          this.loading = false;
          this.tableloading = false;
          this.logs = this.$store.state.dashboard.recent_logs;

          this.pagination.current = this.logs.current_page;
          this.pagination.total = this.logs.last_page;

          this.totalRowsCount = this.logs.total;
          return;
        }
      }
      this.$axios
        .get(
          `device/getLastRecordsHistory/${this.$auth.user.company_id}/10`,
          options
        )
        .then(({ data }) => {
          this.logs = data;
          this.$store.commit("dashboard/recent_logs", data);
          this.loading = false;
          this.tableloading = false;

          this.pagination.current = data.current_page;
          this.pagination.total = data.last_page;

          this.totalRowsCount = data.total;
        });
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);

      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data);

        if (json.Status == 200 && json.Data.UserCode > 0) {
          this.getDetails(json.Data);
        }
      };
    },
    getDetails(item) {
      item.company_id = this.$auth.user.company_id;

      this.$axios.post(`/device/details`, item).then(({ data }) => {
        if (
          data.device &&
          this.$auth.user &&
          data.device.company_id == this.$auth.user.company_id
        ) {
          this.logs.data.unshift(data);
        }
      });
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);

      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data).Data;
        if (json && json.UserCode > 0) {
          this.getDetails(json);
        }
      };
    },
    getDetails({ SN, RecordImage, UserCode, RecordDate }) {
      if (this.devices.includes(SN)) {
        let employee = this.employees.find(
          (e) => e.system_user_id == UserCode && e.first_name !== null
        );

        let item = {
          UserCode,
          image: "data:image;base64," + RecordImage || "/no-profile-image.jpg",
          time: this.setTime(RecordDate),
          name:
            employee &&
            (employee.display_name ||
              employee.first_name ||
              employee.last_name),
        };
        this.logs.unshift(item);
      }
    },
    setTime(dateTimeString) {
      const dateTime = new Date(dateTimeString);
      const hours = dateTime.getHours().toString().padStart(2, "0");
      const minutes = dateTime.getMinutes().toString().padStart(2, "0");
      return `${hours}:${minutes}`;
    },
  },
};
</script>
<style scoped>
.theme--light.v-data-table .v-data-footer {
  float: right !important;
}
.center-both {
  height: 31vh;
  /* Adjust the height as needed */
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
