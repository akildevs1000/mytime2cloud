<template>
  <div v-if="item">
    <v-dialog v-model="dialog" width="700">
      <template v-slot:activator="{ on, attrs }">
        <span class="ml-2 primary--text" small v-bind="attrs" v-on="on">
          {{ item.employee ? item.employee.first_name : "---" }}
          {{ item.employee ? item.employee.last_name : "---" }}
        </span>
        <div class="secondary-value ml-2">
          {{
            item.employee && item.employee.designation
              ? item.employee.designation.name
              : "---"
          }}
        </div>
      </template>

      <v-card>
        <v-toolbar dense flat class="grey lighten-2">
          <v-spacer></v-spacer>
          <v-icon color="primary" @click="dialog = false"
            >mdi-close-circle-outline</v-icon
          >
        </v-toolbar>

        <v-card-text class="pa-5">
          <v-row no-gutters>
            <v-col cols="4">
              <v-row no-gutters>
                <v-col cols="12" class="text-center">
                  <v-avatar size="150">
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
                <v-col cols="12">
                  <div class="text-center mt-2">
                    <span class="pt-2"
                      ><b>EID: {{ item.employee.system_user_id }}</b></span
                    >
                    <br />
                    <b
                      >{{ item.employee.first_name }}
                      {{ item.employee.last_name ?? "---" }}</b
                    >
                    <br />
                    <span
                      >Dept: <b>{{ item.employee.department.name }}</b></span
                    >
                    <br />
                    <span
                      >Desg: <b>{{ item.employee.designation.name }}</b></span
                    >
                  </div>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="8">
              <v-row v-if="todayAttendance">
                <v-col
                  cols="4"
                  class="text-center"
                  style="
                    border-left: 1px solid #b8a9eb;
                    border-top: 1px solid #b8a9eb;
                    border-bottom: 1px solid #b8a9eb;
                  "
                >
                  <v-sheet class="text-h6">
                    {{ todayAttendance && todayAttendance.total_hrs }}
                  </v-sheet>
                  <div>Work Time</div>
                </v-col>
                <v-col
                  cols="4"
                  class="text-center"
                  style="border: 1px solid #b8a9eb"
                >
                  <v-sheet class="text-h6">
                    {{ remainingTime }}
                  </v-sheet>
                  <div>Remaing Hours</div>
                </v-col>
                <v-col cols="12">
                  <v-tabs >
                    <v-tab >
                        <div>dsdffsdf</div>
                        <div>dsdffsdf</div>
                    </v-tab>
                    <v-tab >
                     dsdffsdf
                    </v-tab>
                    <v-tab >
                     dsdffsdf
                    </v-tab>
                  </v-tabs>
                  <v-tabs-items >
                    <v-tab-item>
                      <v-card>
                        <v-card-text>
                          sdsfsdf
                        </v-card-text>
                      </v-card>
                    </v-tab-item>
                    <v-tab-item>
                      <v-card>
                        <v-card-text>
                          sdsfsdf
                        </v-card-text>
                      </v-card>
                    </v-tab-item>
                    <v-tab-item>
                      <v-card>
                        <v-card-text>
                          sdsfsdf
                        </v-card-text>
                      </v-card>
                    </v-tab-item>
                  </v-tabs-items>
                </v-col>
                <v-col
                  cols="4"
                  class="text-center"
                  style="
                    border-top: 1px solid #b8a9eb;
                    border-bottom: 1px solid #b8a9eb;
                    border-right: 1px solid #b8a9eb;
                  "
                >
                  <v-sheet class="text-h6">
                    {{ todayAttendance && todayAttendance.ot }}
                  </v-sheet>
                  <div>OverTime</div>
                </v-col>
                <v-col cols="12">
                  <v-data-table
                    dense
                    :headers="headers_table"
                    :items="logs"
                    class="elevation-0 logtable"
                    :server-items-length="totalRowsCount"
                  >
                    <template v-slot:item.LogTime="{ item }">
                      {{ item.LogTime }}
                    </template>

                    <template v-slot:item.device.device_name="{ item }">
                      <div
                        class="secondary-value"
                        v-if="item.DeviceID.includes(`Mobile`)"
                      >
                        Mobile <br />
                        {{ item.gps_location }}
                      </div>
                      <div class="secondary-value" v-else>
                        {{ item.device ? caps(item.device.name) : "---" }}
                        <br />
                        {{
                          item.device && item.device.location
                            ? item.device.location
                            : "---"
                        }}
                      </div>
                    </template>
                  </v-data-table>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["item"],
  data() {
    return {
      dialog: false,
      todayAttendance: null,
      employee_stats: [],
      remainingTime: "00:00",
    };
  },
  async created() {
    await this.getEmployeeStats();
    await this.getTodayAttendance();
  },
  methods: {
    async getEmployeeStats() {
      this.$axios
        .get(`employee-statistics`, {
          params: {
            company_id: this.item.employee.company_id,
            employee_id: this.item.employee.system_user_id,
          },
        })
        .then(({ data }) => {
          this.employee_stats = data;
        });
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        res.replace(/\b\w/g, (c) => c.toUpperCase());
        return str.includes(`Mobile`) ? "Mobile" : str;
      }
    },
    async getTodayAttendance() {
      this.$axios
        .get(`report`, {
          params: {
            company_id: this.item.employee.company_id,
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
    timeHandler(value) {
      return value === "---" ? "00:00" : value;
    },
  },
};
</script>
