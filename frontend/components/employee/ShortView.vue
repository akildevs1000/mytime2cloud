<template>
  <div v-if="item && item.id">
    <v-dialog v-model="dialog" width="650">
      <template v-slot:activator="{ on, attrs }">
        <span class="ml-2 primary--text" small v-bind="attrs" v-on="on">
          {{ item.employee.first_name ?? "---" }}
          {{ item.employee.last_name ?? "---" }}
        </span>
        <div class="secondary-value ml-2">
          {{ item.employee?.designation?.name }}
        </div>
      </template>

      <v-card>
        <v-toolbar flat dense class="text-h6"
          ><b><small>
            Employee Details
          </small></b>
          <v-spacer></v-spacer>
          <v-icon color="primary" @click="dialog = false">
            mdi-close-circle-outline
          </v-icon>
          </v-toolbar
        >
        <v-divider></v-divider>
        <v-container>
          <v-row no-gutters>
            <v-col cols="5">
              <v-row class="mx-1" style="border-right: 1px solid #dddddd">
                <v-col cols="12" class="mt-1">
                  <v-row class="pa-1">
                    <v-col cols="12" class="text-center">
                      {{ item.employee.profile_pictrue }}
                      <v-avatar size="120">
                        <img
                          style="width: 100%"
                          :src="
                            item.employee && item.employee.profile_picture
                              ? item.employee.profile_picture
                              : '/no-profile-image.jpg'
                          "
                          alt="Avatar"
                        />
                      </v-avatar>
                    </v-col>
                    <v-col cols="12" class="text-center">
                      <div>
                        <b>EID: {{ item.employee.system_user_id ?? "---" }}</b>
                        <br />
                        {{ item.employee.first_name ?? "---" }}
                        {{ item.employee.last_name ?? "---" }}
                      </div>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12">
                  <v-divider></v-divider>
                  <v-row
                    no-gutters
                    v-for="(item, index) in employee_stats"
                    :key="index"
                  >
                    <v-col cols="6">
                      <small> {{ item.title }}</small>
                    </v-col>
                    <v-col cols="6" class="text-right">
                      <small> {{ item.value }}</small>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-col>

            <v-col cols="7">
              <v-row no-gutters class="mb-2">
                <v-col
                  cols="4"
                  class="text-center"
                  style="
                    border-top: 1px solid #dddddd;
                    border-bottom: 1px solid #dddddd;
                    border-right: 1px solid #dddddd;
                  "
                >
                  <b>
                    <small>{{
                      todayAttendance && todayAttendance.total_hrs
                    }}</small>
                  </b>
                  <div>
                    <small>Work Time</small>
                  </div>
                </v-col>
                <v-col
                  cols="4"
                  class="text-center"
                  style="
                    border-top: 1px solid #dddddd;
                    border-bottom: 1px solid #dddddd;
                  "
                >
                  <b>
                    <small>{{ remainingTime }}</small>
                  </b>
                  <div>
                    <small> Remaing Hours </small>
                  </div>
                </v-col>
                <v-col
                  cols="4"
                  class="text-center"
                  style="
                    border-top: 1px solid #dddddd;
                    border-bottom: 1px solid #dddddd;
                    border-left: 1px solid #dddddd;
                  "
                >
                  <b>
                    <small>
                      {{ todayAttendance && todayAttendance.ot }}
                    </small>
                  </b>
                  <div>
                    <small> OverTime </small>
                  </div>
                </v-col>
                <v-col cols="12" class="mt-3 px-1 grey lighten-2" >
                  last 10 Logs
                </v-col>
                <v-col cols="12">
                  <v-data-table
                    dense
                    :headers="log_headers"
                    :items="logs_data"
                    hide-default-footer
                  >
                    <!-- <template v-slot:top>
      <div class="px-2"><b>Today Logs</b></div>
    </template> -->
                    <template v-slot:item.id="{ item, index }">
                      {{ index + 1 }}
                    </template>
                    <template v-slot:item.LogTime="{ item }">
                      {{ item.date }} {{ item.time }}
                    </template>
                    <template v-slot:item.gps_location="{ item }">
                      {{ item.gps_location || "---" }}
                    </template>
                  </v-data-table>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-container>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  props: ["item"],
  data: () => ({
    logs_data: [],
    log_endpoint: "attendance_logs",
    log_headers: [
      {
        text: "#",
        align: "left",
        sortable: false,
        key: "id",
        value: "id",
        width: "10px",
      },
      {
        text: "DateTime",
        align: "left",
        sortable: false,
        key: "date_range",
        value: "LogTime",
        fieldType: "date_range_picker",
      },

      {
        text: "Location",
        align: "left",
        sortable: true,
        key: "gps_location",
        value: "gps_location",
        filterable: true,
        filterSpecial: true,
      },
    ],

    employee_stats_header: [{ value: "value" }],

    dialog: false,
    sinceDate: null,
    UserID: null,
    profile_pictrue: "no-profile-image.jpg",
    logsCount: null,
    company_id: 0,
    lastLog: null,
    employee_stats: [],
    todayAttendance: null,
    remainingTime: "00:00",

    headers: [
      { text: "LogTime", value: "LogTime" },
      { text: "Device", value: "DeviceID" },
    ],
    attendanceLogs: [],
    log_type: "",
    puching_image: "",
    response_image: "/sucess.png",
    uniqueDeviceId: null,
    device_id: null,
    isButtonDisabled: false,
    message: "",
    response: "",
    dialog: false,
    buttonLocked: false,
    locationError: null,
    intervalId: 0,
    locationData: null,
    initialPunch: true,
    shift_type_id: 0,
  }),

  async created() {
    this.company_id = this.item.employee.company_id;

    await this.getEmployeeStats();
    await this.getTodayAttendance();
    await this.getLogs();
  },
  methods: {
    getDate() {
      const date = new Date();
      const year = date.getFullYear();
      const month = (date.getMonth() + 1).toString().padStart(2, "0");
      const day = date.getDate().toString().padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    async getLogs() {
      this.$axios
        .get(this.log_endpoint, {
          params: {
            per_page: 10,
            company_id: this.company_id,
            UserID: this.item.employee.system_user_id,
            from_date: this.getDate(),
            to_date: this.getDate(),
          },
        })
        .then(({ data }) => {
          this.logs_data = data.data;
        });
    },
    async getTodayAttendance() {
      this.$axios
        .get(`report`, {
          params: {
            company_id: this.company_id,
            employee_id: this.item.employee.system_user_id,
            from_date: this.getFormattedDate(),
            to_date: this.getFormattedDate(),
          },
        })
        .then(({ data }) => {
          if (!data.data.length) {
            this.getRemainingTime("00:00", "00:00");
            this.todayAttendance = { total_hrs: "00:00", ot: "00:00" };
            return;
          }

          const { total_hrs, ot, shift } = data.data[0];

          if (!shift) {
            this.getRemainingTime("00:00", "00:00");
            this.todayAttendance = { total_hrs: "00:00", ot: "00:00" };
            return;
          }

          this.todayAttendance = {
            total_hrs: this.timeHandler(total_hrs),
            ot: this.timeHandler(ot),
          };
          this.getRemainingTime(
            this.timeHandler(total_hrs),
            this.timeHandler(shift.working_hours)
          );
        });
    },
    timeHandler(value) {
      return value === "---" ? "00:00" : value;
    },
    async getEmployeeStats() {
      this.$axios
        .get(`employee-statistics`, {
          params: {
            company_id: this.company_id,
            employee_id: this.item.employee.system_user_id,
          },
        })
        .then(({ data }) => {
          this.employee_stats = data;
        });
    },
    getFormattedDate() {
      const now = new Date();
      return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(
        2,
        "0"
      )}-${String(now.getDate()).padStart(2, "0")}`;
    },

    getRemainingTime(totalHours, performedHours) {
      const [totalHoursStr, totalMinutesStr] = totalHours
        .split(":")
        .map(Number);
      const [performedHoursStr, performedMinutesStr] = performedHours
        .split(":")
        .map(Number);

      const totalMinutes = totalHoursStr * 60 + totalMinutesStr;
      const performedMinutes = performedHoursStr * 60 + performedMinutesStr;

      const remainingMinutes = totalMinutes - performedMinutes;

      if (remainingMinutes < 0) {
        const remainingHours = Math.abs(Math.ceil(remainingMinutes / 60));
        const remainingMinutesPart = Math.abs(remainingMinutes % 60);
        this.remainingTime = `${String(remainingHours).padStart(
          2,
          "0"
        )}:${String(remainingMinutesPart).padStart(2, "0")}`;
      }
    },
  },
};
</script>
