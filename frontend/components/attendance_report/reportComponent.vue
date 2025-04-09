<template>
  <div v-if="can(`attendance_report_access`)">
    <style scoped>
      .short-table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
      }

      .short-table td,
      .short-table th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }

      .short-table tr:nth-child(even) {
        background-color: #dddddd;
      }
    </style>
    <div class="text-center">
      <v-snackbar
        v-model="snackbar"
        multi-line
        top="top"
        color="secondary"
        elevation="24"
      >
        <span v-html="response"></span>
      </v-snackbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
    </div>

    <v-card class="mb-5" elevation="0" v-if="can(`attendance_report_view`)">
      <v-toolbar class="backgrounds" dense flat>
        <v-toolbar-title> </v-toolbar-title>

        <v-spacer></v-spacer>
      </v-toolbar>

      <v-data-table
        dense
        :headers="headers"
        :items="data"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 30, 50, 100, 500, 1000],
          page: true,
        }"
        class="elevation-1"
        model-value="data.id"
        :server-items-length="totalRowsCount"
        fixed-header
        :height="tableHeight"
        no-data-text="No Data available. Click 'Generate' button to see the results"
      >
        <template
          v-slot:item.employee_name="{ item, index }"
          style="width: 300px"
        >
          <v-row no-gutters>
            <v-col
              style="
                padding: 5px;
                padding-left: 0px;
                width: 50px;
                max-width: 50px;
              "
            >
              <v-img
                style="
                  border-radius: 50%;
                  height: auto;
                  width: 45px;
                  max-width: 45px;
                "
                :src="
                  item.employee.profile_picture
                    ? item.employee.profile_picture
                    : '/no-profile-image.jpg'
                "
              >
              </v-img>
            </v-col>
            <v-col style="padding: 10px">
              <div style="font-size: 13px">
                {{
                  item.employee.first_name ? item.employee.first_name : "---"
                }}
                {{ item.employee.last_name ? item.employee.last_name : "---" }}
              </div>
              <small style="font-size: 12px; color: #6c7184">
                {{
                  item.employee.employee_id
                    ? item.employee.employee_id
                    : item?.employee_id
                }}
              </small>
            </v-col>
          </v-row>
        </template>

        <template v-slot:item.status="{ item }">
          <v-tooltip top color="primary">
            <template v-slot:activator="{ on, attrs }">
              {{ setStatusLabel(item.status) }}
              <div class="secondary-value" v-if="item.status == 'P'">
                {{ getShortShiftDetails(item) }}
              </div>

              <v-btn
                v-if="item.is_manual_entry"
                color="primary"
                text
                v-bind="attrs"
                v-on="on"
              >
                (ME)
              </v-btn>
            </template>
            <div>Reason: {{ item.last_reason?.reason }}</div>
            <div>Added By: {{ item.last_reason?.user?.email }}</div>
            <div>Created At: {{ item.last_reason?.created_at }}</div>
          </v-tooltip>
        </template>

        <template v-slot:item.shift="{ item }">
          <div>
            {{ item?.employee?.schedule?.shift?.on_duty_time || "---" }} -
            {{ item?.employee?.schedule?.shift?.off_duty_time || "---" }}
          </div>
          <div class="secondary-value">
            {{ item?.employee?.schedule?.shift?.name || "---" }}
            <span v-if="checkHalfday(item || `---`)">
              {{ `(Half Day ${item.shift.halfday_working_hours} hrs)` }}
            </span>
          </div>
          <!-- <v-tooltip v-if="item && item.shift" top color="primary">
            <template v-slot:activator="{ on, attrs }">
              <div class="primary--text" v-bind="attrs" v-on="on">
                <div>
                  {{ item.shift.on_duty_time }} - {{ item.shift.off_duty_time }}
                </div>
                {{ (item.shift && item.shift.name) || "---" }}
              </div>
            </template>
            <div v-for="(iterable, index) in item.shift" :key="index">
              <span v-if="index !== 'id'">
                {{ caps(index) }}: {{ iterable || "---" }}</span
              >
            </div>
          </v-tooltip>
          <span v-else>---</span> -->
        </template>
        <!-- <template v-slot:item.name="{ item }">
          {{ item.employee.first_name }} {{ item.employee.last_name }}
        </template> -->
        <template v-slot:item.date="{ item }">
          <div>{{ item.date }}</div>
          <div class="secondary-value">
            {{ item.day }}
          </div>
        </template>
        <template v-slot:item.in="{ item }">
          <div
            :class="`${item?.device_in?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in }}</div>
            <div class="secondary-value">
              <div
                v-if="
                  item.device_in &&
                  item.device_in.name &&
                  item.device_in.name != '---'
                "
              >
                {{ item.device_in.name }}
              </div>
              <div v-else-if="item.device_id_in != '---'">
                {{ item.device_id_in }}
              </div>
              <div v-else>---</div>
            </div>
          </div>
        </template>
        <template v-slot:item.out="{ item }">
          <div
            :class="`${item?.device_out?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out }}</div>
            <div class="secondary-value">
              <div
                v-if="
                  item.device_out &&
                  item.device_out.name &&
                  item.device_out.name != '---'
                "
              >
                {{ item.device_out.name }}
              </div>
              <div v-else-if="item.device_id_out != '---'">
                {{ item.device_id_out }}
              </div>
              <div v-else>---</div>

              <!-- {{ item.device_id_out == "Manual" ? "Manual" : "---" }} -->
            </div>
          </div>
        </template>
        <template v-slot:item.in1="{ item }">
          <div
            :class="`${item?.device_in1?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in1 }}</div>
            <div class="secondary-value">
              {{ (item.device_in1 && item.device_in1) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out1="{ item }">
          <div
            :class="`${item?.device_out1?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out1 }}</div>
            <div class="secondary-value">
              {{ (item.device_out1 && item.device_out1) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.in2="{ item }">
          <div
            :class="`${item?.device_in2?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in2 }}</div>
            <div class="secondary-value">
              {{ (item.device_in2 && item.device_in2) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out2="{ item }">
          <div
            :class="`${item?.device_in2?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out2 }}</div>
            <div class="secondary-value">
              {{ (item.device_out2 && item.device_out2) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.in3="{ item }">
          <div
            :class="`${item?.device_in3?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in3 }}</div>
            <div class="secondary-value">
              {{ (item.device_in3 && item.device_in3) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out3="{ item }">
          <div
            :class="`${item?.device_out3?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out3 }}</div>
            <div class="secondary-value">
              {{ (item.device_out3 && item.device_out3) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.in4="{ item }">
          <div
            :class="`${item?.device_in4?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in4 }}</div>
            <div class="secondary-value">
              {{ (item.device_in4 && item.device_in4) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out4="{ item }">
          <div
            :class="`${item?.device_out4?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out4 }}</div>
            <div class="secondary-value">
              {{ (item.device_out4 && item.device_out4) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.in5="{ item }">
          <div
            :class="`${item?.device_in5?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in5 }}</div>
            <div class="secondary-value">
              {{ (item.device_in5 && item.device_in5) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out5="{ item }">
          <div
            :class="`${item?.device_out5?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out5 }}</div>
            <div class="secondary-value">
              {{ (item.device_out5 && item.device_out5) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.in6="{ item }">
          <div
            :class="`${item?.device_in6?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in6 }}</div>
            <div class="secondary-value">
              {{ (item.device_in6 && item.device_in6) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out6="{ item }">
          <div
            :class="`${item?.device_out6?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out6 }}</div>
            <div class="secondary-value">
              {{ (item.device_out6 && item.device_out6) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.in7="{ item }">
          <div
            :class="`${item?.device_in7?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.in7 }}</div>
            <div class="secondary-value">
              {{ (item.device_in7 && item.device_in7) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.out7="{ item }">
          <div
            :class="`${item?.device_out7?.name == 'Manual' ? 'red' : ''}--text`"
          >
            <div>{{ item.out7 }}</div>
            <div class="secondary-value">
              {{ (item.device_out7 && item.device_out7) || "---" }}
            </div>
          </div>
        </template>
        <template v-slot:item.device_in="{ item }">
          <v-tooltip v-if="item && item.device_in" top color="primary">
            <template v-slot:activator="{ on, attrs }">
              <div class="primary--text" v-bind="attrs" v-on="on">
                {{ (item.device_in && item.device_in.short_name) || "---" }}
              </div>
            </template>
            <div v-for="(iterable, index) in item.device_in" :key="index">
              <span v-if="index !== 'id'">
                {{ caps(index) }}: {{ iterable || "---" }}</span
              >
            </div>
          </v-tooltip>
          <span v-else>---</span>
        </template>

        <template v-slot:item.device_out="{ item }">
          <v-tooltip v-if="item && item.device_out" top color="primary">
            <template v-slot:activator="{ on, attrs }">
              <div class="primary--text" v-bind="attrs" v-on="on">
                {{ (item.device_out && item.device_out.short_name) || "---" }}
              </div>
            </template>
            <div v-for="(iterable, index) in item.device_out" :key="index">
              <span v-if="index !== 'id'">
                {{ caps(index) }}: {{ iterable || "---" }}</span
              >
            </div>
          </v-tooltip>
          <span v-else>---</span>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-icon
            @click="editItem(item)"
            x-small
            color="primary"
            class="mr-2"
            v-if="can(`attendance_report_manual_entry_access`)"
          >
            mdi-pencil
          </v-icon>
          <v-icon
            @click="viewItem(item)"
            x-small
            color="primary"
            class="mr-2"
            v-if="can('attendance_report_view')"
          >
            mdi-eye
          </v-icon>
        </template>
      </v-data-table>
    </v-card>

    <v-row justify="center">
      <v-dialog persistent v-model="dialog" max-width="300px">
        <WidgetsClose :left="290" @click="dialog = false" />
        <v-card>
          <v-alert dark dense flat class="primary">
            Employee Manual Log
          </v-alert>
          <v-card-text>
            <v-container>
              <v-row>
                <v-form
                  ref="form"
                  v-model="valid"
                  lazy-validation
                  style="width: 100%"
                >
                  <v-col md="12" class="pa-0">
                    <v-menu
                      ref="time_menu_ref"
                      v-model="time_menu"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      :return-value.sync="payload.time"
                      transition="scale-transition"
                      offset-y
                      max-width="290px"
                      min-width="290px"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          outlined
                          dense
                          v-model="editItems.time"
                          label="Time"
                          readonly
                          v-bind="attrs"
                          :rules="timeRules"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-time-picker
                        no-title
                        v-if="time_menu"
                        v-model="editItems.time"
                        full-width
                        format="24hr"
                      >
                        <v-spacer></v-spacer>
                        <v-btn
                          x-small
                          color="primary"
                          @click="time_menu = false"
                        >
                          Cancel
                        </v-btn>
                        <v-btn
                          x-small
                          color="primary"
                          @click="$refs.time_menu_ref.save(editItems.time)"
                        >
                          OK
                        </v-btn>
                      </v-time-picker>
                    </v-menu>
                    <span
                      v-if="errors && errors.time"
                      class="text-danger mt-2"
                      >{{ errors.time[0] }}</span
                    >
                  </v-col>

                  <v-col cols="12" class="pa-0">
                    <v-textarea
                      outlined
                      dense
                      filled
                      label="Reason"
                      v-model="editItems.reason"
                      auto-grow
                      :rules="nameRules"
                      required
                      rows="2"
                    ></v-textarea>
                    <span v-if="errors && errors.reason" class="error--text">
                      {{ errors.reason[0] }}
                    </span>
                  </v-col>
                </v-form>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn class="error" small @click="close"> Cancel </v-btn>
            <v-btn class="primary" small @click="update">Save</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>

    <v-row justify="center">
      <v-dialog persistent v-model="log_details" max-width="800px">
        <v-card class="darken-1">
          <v-card-title dark class="popup_background">
            <span dense>Log Details </span>
            <v-spacer></v-spacer>
            <v-icon dark @click="log_details = false" outlined>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-toolbar flat dense>
            Employee Id: <b>{{ log_list?.item?.employee?.system_user_id }}</b>
            <v-spacer></v-spacer>
            Total logs
            <b class="background--text">({{ log_list.length }})</b>
          </v-toolbar>
          <v-card-text>
            <!-- <hr /> -->
            <table class="short-table">
              <tr>
                <td>LogTime</td>
                <td>Device</td>
                <td>Log Type</td>
              </tr>
              <tr v-for="(log, index) in log_list" :key="index">
                <td
                  :class="`${log?.device?.name == 'Manual' ? 'red' : ''}--text`"
                >
                  {{ log.LogTime }}
                </td>
                <td>{{ log?.device?.name }}</td>
                <td>
                  <b
                    ><div v-if="log.log_type">
                      {{ log?.device?.name }}
                    </div>
                    <div v-else>Device</div>
                  </b>
                </td>
              </tr>
            </table>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  props: ["title", "shift_type_id", "headers", "payload1", "system_user_id"],

  data: () => ({
    key: 1,
    tableHeight: 750,
    status: "",
    department_ids: "",
    employee_id: "",
    daily_date: "",
    from_date: "",
    to_date: "",
    report_type: "Monthly",
    filters: {},
    totalRowsCount: 0,
    datatable_search_textbox: "",
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    menu: false,
    log_details: false,
    overtime: false,
    options: { page: 1 },
    loading: false,
    time_menu: false,
    Model: "Attendance Reports",
    search: "",
    snackbar: false,
    dialog: false,
    from_menu: false,
    to_menu: false,
    ids: [],
    departments: [],
    scheduled_employees: [],
    DateRange: true,
    devices: [],
    valid: true,
    nameRules: [(v) => !!v || "reason is required"],
    timeRules: [(v) => !!v || "time is required"],
    dailyDate: false,
    editItems: {
      shift_type_id: 0,
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      time: null,
      manual_entry: false,
    },
    loading: false,
    total: 0,

    payload: {
      from_date: null,
      to_date: null,
      daily_date: null,
      employee_id: "",
      department_ids: [],
      status: "-1",
    },
    log_payload: {
      user_id: null,
      device_id: "",
      date: null,
      time: null,
    },
    log_list: [],
    snackbar: false,
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    shifts: [],
    errors: [],
    custom_options: {},
    max_date: null,
    originalTableHeaders: [],
    clearPagenumber: false,
    baseURL: null,
  }),

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    payload1(value) {
      this.payload = value;
      this.report_type = value.report_type;
      this.department_ids = value.department_ids;
      this.employee_id = value.employee_id;
      this.statuses = value.statuses;

      if (this.payload.from_date == null) {
        this.payload.from_date = this.payload.daily_date;
        this.payload.to_date = this.payload.daily_date;
      }
      this.originalTableHeaders = this.headers;
      this.clearPagenumber = true;
      this.getDataFromApi();
    },

    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  mounted() {
    this.tableHeight = window.innerHeight - 370;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 370;
    });
  },

  created() {
    this.payload = {
      ...this.payload,
      ...this.payload1,
    };
    this.baseURL = `http://${window.location.hostname ?? "localhost"}:8000/api`;
  },

  methods: {
    checkHalfday(item) {
      let currentDay = new Date().toLocaleString("en-US", {
        weekday: "long",
      });

      return item.shift && currentDay === item.shift.halfday;
    },
    update() {
      let log_payload = {
        UserID: this.editItems.UserID,
        LogTime: this.editItems.date + " " + this.editItems.time,
        DeviceID: "Manual",
        company_id: this.$auth.user.company_id,
        log_type: "auto",
      };
      this.loading = true;

      this.$axios
        .post(`/generate_log`, log_payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            // this.render_report(
            //   this.editItems.date,
            //   this.editItems.shift_type_id
            // );
            this.regenerateAttendance(this.editItems);
            this.$emit("close-popup");
            this.snackbar = true;
            this.response = data.message;
            this.getDataFromApi();
            this.dialog = false;
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    regenerateAttendance({ date, shift_type_id, UserID }) {
      let payload = {
        params: {
          date,
          UserID,
          shift_type_id,
          reason: this.reason,
          company_id: this.$auth.user.company_id,
          user_id: this.$auth.user.id,
          updated_by: this.$auth.user.id,
        },
      };
      this.$axios
        .get("regenerate-attendance", payload)
        .then(({ data }) => {
          this.snackbar = true;
          this.response = "Reprot has been regerated";
          this.loading = false;
          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDataFromApi() {
      if (!this.payload.from_date) return false;

      let { page, itemsPerPage } = this.options;

      this.loading = true;

      let payload = {
        page: page,
        per_page: itemsPerPage,
        company_id: this.$auth.user.company_id,
        report_type: this.report_type,
        shift_type_id: this.shift_type_id,
        overtime: this.overtime ? 1 : 0,
        ...this.filters,
        ...this.payload,
      };

      this.$axios.post(`attendance-report-new`, payload).then(({ data }) => {
        if (data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.data = [];
          this.total = 0;
          this.loading = false;
          this.totalRowsCount = 0;
          return false;
        }

        this.data = data.data;
        this.total = data.total;
        this.loading = false;
        this.totalRowsCount = data.total;

        if (this.clearPagenumber) {
          this.options.page = 1;
          this.clearPagenumber = false;
        }
      });
    },

    editItem(item) {
      this.dialog = true;
      this.editItems = item;
      this.editItems.UserID = item.employee_id;
      this.editItems.date = item.edit_date;
    },

    viewItem(item) {
      this.log_list = [];
      let options = {
        params: {
          per_page: 500,
          UserID: item.employee_id,
          LogTime: item.edit_date,
          company_id: this.$auth.user.company_id,
        },
      };
      this.log_details = true;

      this.$axios.get("attendance_single_list", options).then(({ data }) => {
        this.log_list = data.data;
        this.log_list.item = item;
      });
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    getShortShiftDetails(item) {
      if (item.shift) {
        let shiftWorkingHours = item.shift.working_hours;
        let employeeHours = item.total_hrs;

        if (
          shiftWorkingHours != "" &&
          employeeHours != "" &&
          shiftWorkingHours != "---" &&
          employeeHours != "---"
        ) {
          let [hours, minutes] = shiftWorkingHours.split(":").map(Number);
          shiftWorkingHours = hours * 60 + minutes;

          [hours, minutes] = employeeHours.split(":").map(Number);
          employeeHours = hours * 60 + minutes;

          if (
            employeeHours < shiftWorkingHours &&
            !this.checkHalfday(item || `---`)
          ) {
            return "Short Shift";
          }
        }
      }
    },
    setStatusLabel(status) {
      const statuses = {
        A: "Absent",
        P: "Present",
        M: "Incomplete",
        LC: "Late In",
        EG: "Early Out",
        O: "Week Off",
        L: "Leave",
        H: "Holiday",
        V: "Vaccation",
      };
      return statuses[status];
    },
  },
};
</script>
