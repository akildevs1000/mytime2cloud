<template>
  <div v-if="can(`attendance_report_access`)">
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
        <template v-slot:item.employee_name="{ item }">
          <div
            class="pa-1"
            style="display: flex; align-items: center; gap: 10px"
          >
            <v-avatar size="45">
              <v-img
                :src="
                  item?.employee?.profile_picture || '/no-profile-image.jpg'
                "
              ></v-img>
            </v-avatar>
            <div>
              <div style="font-size: 13px">
                {{ item?.employee?.first_name || "---" }}
                {{ item?.employee?.last_name || "---" }}
              </div>
              <small style="font-size: 12px; color: #6c7184">
                {{ item?.employee?.employee_id || "---" }}
              </small>
            </div>
          </div>
        </template>

        <template v-slot:item.report_from="{ item }">
          {{ $dateFormat.format6(payload.from_date) }}
        </template>
        <template v-slot:item.report_to="{ item }">
          {{ $dateFormat.format6(payload.to_date) }}
        </template>
        <template v-slot:item.rating="{ item }">
          <v-rating
            dense
            hide-details
            :value="getRating(item.p_count_value)"
            background-color="green lighten-3"
            color="green"
            half-increments
          ></v-rating>
        </template>
        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list dense>
              <v-list-item>
                <v-list-item-title style="cursor: pointer">
                  <PerformanceSingle :item="item" />
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-card>

    <v-row justify="center">
      <v-dialog persistent v-model="time_table_dialog" max-width="600px">
        <v-card class="darken-1">
          <v-toolbar class="primary" dense dark flat>
            <span class="text-h5">Time Slots</span>
          </v-toolbar>
          <v-card-text>
            <ol class="pa-3">
              <li v-for="(shift, index) in shifts" :key="index">
                {{ (shift && shift.name) || "---" }}
                {{
                  shift.on_duty_time
                    ? `(${shift.on_duty_time} - ${shift.off_duty_time})`
                    : ""
                }}
              </li>
            </ol>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-row>

    <v-row justify="center">
      <v-dialog persistent v-model="dialog" max-width="300px">
        <v-card>
          <v-card-title dark class="popup_background">
            <span dense>Employee Manual Logs</span>
            <v-spacer></v-spacer>
            <v-icon dark @click="dialog = false" outlined>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <!-- <v-card-title class="popup_background">
              <span class="headline"> Employee Manual Log </span>
              <v-spacer></v-spacer>
              <v-icon @click="dialog = false" outlined dark>
                mdi mdi-close-circle
              </v-icon>
            </v-card-title> -->
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
                          v-model="editItems.time"
                          label="Time"
                          readonly
                          v-bind="attrs"
                          :rules="timeRules"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-time-picker
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
      <v-dialog persistent v-model="reportSync" max-width="800px">
        <v-card style="width: 100%">
          <v-card-title class="popup_background">
            <span dense> Re-Generate Report </span>
            <v-spacer></v-spacer>
            <v-icon dark @click="reportSync = false">mdi-close-circle</v-icon>
          </v-card-title>
          <RenderAttendance
            :key="key"
            :shift_type_id="shift_type_id"
            :endpoint="render_endpoint"
            :display_emp_pic="display_emp_pic"
            :system_user_id="system_user_id"
            @update-data-table="getDataFromApi()"
          />
        </v-card>
      </v-dialog>
    </v-row>

    <v-row justify="center">
      <v-dialog persistent v-model="generateLogsDialog" max-width="800px">
        <v-card>
          <v-card-title class="popup_background">
            <span dense>Employee Manual Log </span>
            <v-spacer></v-spacer>
            <v-icon dark @click="generateLogsDialog = false"
              >mdi-close-circle</v-icon
            >
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <GenerateLog
                  @close-popup="generateLogsDialog = false"
                  :endpoint="render_endpoint"
                  :system_user_id="system_user_id"
                  :shift_type_id="shift_type_id"
                  @update-data-table="getDataFromApi()"
                />
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="generateMultiLogsDialog" max-width="800px">
        <v-card>
          <v-card-title class="popup_background">
            <span class="headline">Employee Manual Multi Log </span>
            <v-spacer></v-spacer>
            <v-icon dark @click="generateLogsDialog = false"
              >mdi-close-circle</v-icon
            >
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <GenerateLogMulti
                  @close-popup="generateLogsDialog = false"
                  :endpoint="render_endpoint"
                  :system_user_id="system_user_id"
                  :shift_type_id="shift_type_id"
                  @update-data-table="getDataFromApi()"
                />
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-row>

    <v-dialog persistent v-model="add_manual_log" width="700">
      <v-card>
        <v-card-title class="popup_background text-h5 darken-2" dark>
          Manual Log_old
        </v-card-title>

        <v-card-text class="pa-3">
          <v-row>
            <v-col md="12">
              <v-text-field
                v-model="log_payload.user_id"
                label="User Id"
              ></v-text-field>
              <span v-if="errors && errors.user_id" class="text-danger mt-2">{{
                errors.user_id[0]
              }}</span>
            </v-col>
            <v-col md="12">
              <v-autocomplete
                label="Select Device"
                v-model="log_payload.device_id"
                :items="devices"
                item-text="name"
                item-value="id"
                :rules="deviceRules"
              >
              </v-autocomplete>
              <span
                v-if="errors && errors.device_id"
                class="text-danger mt-2"
                >{{ errors.device_id[0] }}</span
              >
            </v-col>
            <v-col md="12">
              <v-autocomplete
                label="In/Out"
                v-model="log_payload.log_type"
                :items="['In', 'Out']"
                :rules="deviceRules"
              >
                {{ log_payload.log_type }}
              </v-autocomplete>
              <span v-if="errors && errors.log_type" class="text-danger mt-2">{{
                errors.log_type[0]
              }}</span>
            </v-col>
            <v-col cols="12" md="6">
              <v-menu
                ref="menu"
                v-model="menu"
                :close-on-content-click="false"
                :return-value.sync="date"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-model="log_payload.date"
                    label="Date"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                  >
                  </v-text-field>
                </template>
                <v-date-picker v-model="log_payload.date" no-title scrollable>
                  <v-spacer></v-spacer>
                  <v-btn text color="primary" @click="menu = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    text
                    color="primary"
                    @click="$refs.menu.save(log_payload.date)"
                  >
                    OK
                  </v-btn>
                </v-date-picker>
              </v-menu>
            </v-col>
            <v-col cols="12" md="6">
              <v-menu
                ref="manual_time_menu_ref"
                v-model="manual_time_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="log_payload.time"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-height="320px"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-model="log_payload.time"
                    label="Time"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                  >
                  </v-text-field>
                </template>
                <v-time-picker
                  v-if="manual_time_menu"
                  v-model="log_payload.time"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn x-small color="primary" @click="manual_ = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="$refs.manual_time_menu_ref.save(log_payload.time)"
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span v-if="errors && errors.time" class="text-danger mt-2">{{
                errors.time[0]
              }}</span>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            small
            :loading="loading"
            color="primary"
            @click="store_schedule"
          >
            Submit
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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

          <!-- <v-toolbar class="popup_background">
              <span class="headline">Log Details</span>
              <v-spacer></v-spacer>
  
              <v-icon color="white" @click="log_details = false"
                >mdi-close-circle-outline</v-icon
              >
            </v-toolbar> -->
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
                <!-- <td>Device Function</td> -->
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
                      {{ log?.device?.name == "Manual" ? "Manual" : "Device" }}
                    </div>
                    <div v-else>Device</div>
                  </b>
                </td>
                <!-- <td>
                    <b>{{
                      log?.device?.function ? caps(log.device.function) : "---"
                    }}</b>
                  </td> -->
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
  props: [
    "report_template",
    "branch_id",
    "title",
    "shift_type_id",
    "headers",
    "render_endpoint",
    "process_file_endpoint",
    "payload1",
    "display_emp_pic",
    "system_user_id",
  ],

  data: () => ({
    donwload_pdf_file: "",
    view_pdf_file: "",

    key: 1,
    generateMultiLogsDialog: false,
    currentPage: "",
    tableHeight: 750,
    status: "",
    department_ids: "",
    employee_id: "",
    daily_date: "",
    from_date: "",
    to_date: "",
    report_type: "Monthly",

    filters: {},
    attendancFilters: false,
    isFilter: false,
    totalRowsCount: 0,
    datatable_search_textbox: "",
    datatable_filter_date: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    menu: false,
    selectedItems: [],
    time_table_dialog: false,
    log_details: false,
    overtime: false,
    options: { page: 1 },
    date: null,
    menu: false,
    loading: false,
    time_menu: false,
    manual_time_menu: false,
    Model: "Attendance Reports",
    search: "",
    snackbar: false,
    add_manual_log: false,
    dialog: false,
    generateLogsDialog: false,
    reportSync: false,
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
    deviceRules: [(v) => !!v || "device is required"],
    main_report_type: "Multi In/Out Report",
    daily_menu: false,
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
    statuses: [
      {
        name: `Select All`,
        id: `-1`,
      },
      {
        name: `Present`,
        id: `P`,
      },
      {
        name: `Absent`,
        id: `A`,
      },
      {
        name: `Late In`,
        id: `LC`,
      },
      {
        name: `Early Out`,
        id: `EG`,
      },
      {
        name: `Missing`,
        id: `M`,
      },
      {
        name: `Off`,
        id: `O`,
      },
      {
        name: `Leave`,
        id: `L`,
      },
      {
        name: `Holiday`,
        id: `H`,
      },
      {
        name: `Vaccation`,
        id: `V`,
      },
      {
        name: `Manual Entry`,
        id: `ME`,
      },
    ],
    max_date: null,
    originalTableHeaders: [],
    clearPagenumber: false,
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    payload1(value) {
      this.payload = value;
      // this.payload.status = value.status;
      // this.payload.daily_date = value.daily_date;
      // this.payload.from_date = value.from_date;
      // this.payload.to_date = value.to_date;
      this.report_type = value.report_type;
      this.department_ids = value.department_ids;
      this.employee_id = value.employee_id;
      this.status = value.status;

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
  },

  methods: {
    getRating(count) {
      // Convert to Date objects
      let fromDate = new Date(this.payload.from_date);
      let toDate = new Date(this.payload.to_date);

      // Calculate difference in milliseconds
      let diffInMilliseconds = toDate - fromDate;

      // Convert milliseconds to days
      let totalDays = Math.ceil(diffInMilliseconds / (1000 * 60 * 60 * 24));

      let presentPercent = totalDays > 0 ? (count / totalDays) * 100 : 0;

      if (presentPercent > 90 && presentPercent <= 100) {
        return 5;
      } else if (presentPercent > 80 && presentPercent <= 90) {
        return 4.5;
      } else if (presentPercent > 70 && presentPercent <= 80) {
        return 4;
      } else if (presentPercent > 60 && presentPercent <= 70) {
        return 3.5;
      } else if (presentPercent > 50 && presentPercent <= 60) {
        return 3;
      } else if (presentPercent > 40 && presentPercent <= 50) {
        return 2.5;
      } else if (presentPercent > 30 && presentPercent <= 40) {
        return 2;
      } else if (presentPercent > 20 && presentPercent <= 30) {
        return 1.5;
      } else if (presentPercent > 10 && presentPercent <= 20) {
        return 1;
      } else {
        return 0;
      }
    },

    store_schedule() {
      let { user_id, date, time, device_id } = this.log_payload;
      let log_payload = {
        UserID: user_id,
        LogTime: date + " " + time,
        DeviceID: device_id,
        company_id: this.$auth.user.company_id,
      };
      this.loading = true;

      this.$axios
        .post(`/generate_log`, log_payload)
        .then(({ data }) => {
          this.getDataFromApi();
          this.add_manual_log = false;
          this.generateLogsDialog = false;
          this.loading = false;
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
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
            this.render_report(
              this.editItems.date,
              this.editItems.shift_type_id
            );
            this.$emit("close-popup");
            this.snackbar = true;
            this.response = data.message;
            this.getDataFromApi();
            //this.generateLogsDialog = false;
            this.dialog = false;
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    render_report(date, shift_type_id) {
      let payload = {
        params: {
          dates: [date, date],
          UserIds: [this.editItems.UserID],
          company_ids: [this.$auth.user.company_id],
          user_id: this.$auth.user.id,
          updated_by: this.$auth.user.id,
          reason: this.reason,
          employee_ids: [this.editItems.UserID],
          shift_type_id: shift_type_id,
        },
      };
      this.$axios
        .get("render_logs", payload)
        .then(({ data }) => {
          // let message = "";
          // data.forEach((element) => {
          //   message = message + " \n \n  \n" + element;
          // });
          this.snackbar = true;
          let message = data
            .map(
              (message) =>
                message.replace(
                  /^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] /,
                  ""
                ) + "<br>"
            )
            .join("");
          let searchString = "No schedule is mapped with   date and employee";
          let found = data.includes(searchString);

          console.log(found);

          if (found) {
            message = searchString;
          }
          this.response = message;
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

      this.$axios.post(`performance-report`, payload).then(({ data }) => {
        if (data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
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

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
  },
};
</script>
