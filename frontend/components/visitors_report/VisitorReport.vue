<template>
  <div v-if="can(`visitors_report_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}
        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
    </div>

    <v-row justify="center">
      <div class="text-center">
        <v-dialog persistent v-model="attendancFilters" width="900">
          <v-card>
            <v-card-title class="popup_background">
              <span class="headline"> Visitor Reports Filters </span>
              <v-spacer></v-spacer>
              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                class="ma-0"
                x-small
                :ripple="false"
                text
                title="PRINT"
                @click="process_file('print')"
              >
                <v-icon>mdi-printer-outline</v-icon>
              </v-btn>
              <!-- </template>
                <span>PRINT</span>
              </v-tooltip> -->

              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                x-small
                :ripple="false"
                text
                title="DOWNLOAD"
                @click="process_file('download')"
              >
                <v-icon>mdi-download-outline</v-icon>
              </v-btn>
              <!-- </template>
                <span>DOWNLOAD</span>
              </v-tooltip> -->

              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                x-small
                :ripple="false"
                text
                title="CSV"
                @click="process_file('csv')"
              >
                <v-icon>mdi-file-outline</v-icon>
              </v-btn>
              <!-- </template>
                <span>CSV</span>
              </v-tooltip> -->

              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    x-small
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                    @click="process_file('json')"
                  >
                    <v-icon class="white--text">mdi-code-json</v-icon>
                  </v-btn>
                </template>
                <span>JSON</span>
              </v-tooltip> -->
            </v-card-title>

            <v-card-text class="py-3">
              <v-row>
                <v-col md="6">
                  Report Type

                  <v-autocomplete
                    @change="getDataFromApi(`visitor_attendance`)"
                    outlined
                    dense
                    v-model="payload.status"
                    x-small
                    :items="[
                      `All`,
                      `Approved`,
                      `Pending`,
                      `Rejected`,
                      `Cancelled`,
                    ]"
                    item-value="id"
                    item-text="name"
                    :hide-details="true"
                  ></v-autocomplete>
                </v-col>
                <v-col md="6">
                  Visitor ID
                  <v-autocomplete
                    @change="getDataFromApi(`visitor_attendance`)"
                    outlined
                    dense
                    v-model="payload.visitor_id"
                    x-small
                    :items="visitors"
                    item-value="system_user_id"
                    item-text="name_with_user_id"
                    :hide-details="true"
                  ></v-autocomplete>
                </v-col>
                <v-col md="6">
                  <div>Frequency</div>
                  <v-autocomplete
                    @change="changeReportFrequency"
                    outlined
                    dense
                    v-model="frequency"
                    x-small
                    :items="['Daily', 'Weekly', 'Monthly', 'Custom']"
                    item-text="['Daily']"
                    :hide-details="true"
                  ></v-autocomplete>
                </v-col>
                <v-col>
                  <v-row v-if="frequency == 'Daily'">
                    <v-col cols="12">
                      <div>Date</div>
                      <div class="text-left">
                        <v-menu
                          ref="daily_menu"
                          v-model="daily_menu"
                          :close-on-content-click="false"
                          :return-value.sync="daily_date"
                          transition="scale-transition"
                          offset-y
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              :hide-details="payload.daily_date"
                              outlined
                              dense
                              v-model="payload.daily_date"
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="payload.daily_date"
                            no-title
                            scrollable
                          >
                            <v-spacer></v-spacer>
                            <v-btn
                              text
                              color="primary"
                              @click="daily_menu = false"
                            >
                              Cancel
                            </v-btn>
                            <v-btn
                              text
                              color="primary"
                              @click="
                                set_date_save(
                                  $refs.daily_menu,
                                  payload.daily_date
                                )
                              "
                            >
                              OK
                            </v-btn>
                          </v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>
                  </v-row>
                  <v-row v-else>
                    <v-col md="6">
                      <div>From Date</div>
                      <div class="text-left">
                        <v-menu
                          ref="from_menu"
                          v-model="from_menu"
                          :close-on-content-click="false"
                          :return-value.sync="from_date"
                          transition="scale-transition"
                          offset-y
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              :hide-details="payload.from_date"
                              outlined
                              dense
                              v-model="payload.from_date"
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="payload.from_date"
                            no-title
                            scrollable
                          >
                            <v-spacer></v-spacer>
                            <v-btn
                              text
                              color="primary"
                              @click="from_menu = false"
                            >
                              Cancel
                            </v-btn>
                            <v-btn
                              text
                              color="primary"
                              @click="
                                set_date_save(
                                  $refs.from_menu,
                                  payload.from_date
                                )
                              "
                            >
                              OK
                            </v-btn>
                          </v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>
                    <v-col md="6">
                      <div>To Date</div>
                      <div class="text-left">
                        <v-menu
                          ref="to_menu"
                          v-model="to_menu"
                          :close-on-content-click="false"
                          :return-value.sync="to_date"
                          transition="scale-transition"
                          offset-y
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              :hide-details="payload.to_date"
                              outlined
                              dense
                              v-model="payload.to_date"
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="payload.to_date"
                            :max="max_date"
                            no-title
                            scrollable
                          >
                            <v-spacer></v-spacer>
                            <v-btn
                              text
                              color="primary"
                              @click="to_menu = false"
                            >
                              Cancel
                            </v-btn>
                            <v-btn
                              text
                              color="primary"
                              @click="
                                set_date_save($refs.to_menu, payload.to_date)
                              "
                            >
                              OK
                            </v-btn>
                          </v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" text @click="attendancFilters = false">
                Close
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
      <v-dialog persistent v-model="time_table_dialog" max-width="600px">
        <v-card class="darken-1">
          <v-toolbar class="popup-background" dense flat>
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
      <v-dialog persistent v-model="dialog" max-width="700px">
        <v-card>
          <v-card-title class="primary darken-2">
            <span class="headline white--text"> Update Log </span>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-form ref="form" v-model="valid" lazy-validation>
                  <v-col md="12">
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
                  <!-- <v-col md="12">
                  <v-text-field
                    v-model="editItems.device_id"
                    label="Device Id"
                    readonly
                  ></v-text-field>
                  <span
                    v-if="errors && errors.device_id"
                    class="text-danger mt-2"
                    >{{ errors.device_id[0] }}</span
                  >
                </v-col> -->

                  <v-col md="12">
                    <v-autocomplete
                      label="Select Device"
                      v-model="editItems.device_id"
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
                  <v-col cols="12">
                    <v-textarea
                      filled
                      label="Reason"
                      v-model="editItems.reason"
                      auto-grow
                      :rules="nameRules"
                      required
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

    <v-dialog persistent v-model="add_manual_log" width="700">
      <v-card>
        <v-card-title class="text-h5 primary white--text darken-2" dark>
          Manual Log
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

    <v-row>
      <v-col>
        <v-card class="mb-5" elevation="0">
          <v-toolbar flat>
            <v-toolbar-title>
              <span> Visitor Report </span>
            </v-toolbar-title>

            <!-- <v-tooltip top color="primary">
              <template v-slot:activator="{ on, attrs }"> -->
            <v-btn
              x-small
              :ripple="false"
              text
              title="Reload"
              @click="clearFilters()"
            >
              <v-icon dark @click="clearFilters()">mdi-reload</v-icon>
            </v-btn>
            <!-- </template>
              <span>Reload</span>
            </v-tooltip> -->
            <!-- <v-tooltip top color="primary">
              <template v-slot:activator="{ on, attrs }"> -->
            <v-btn
              x-small
              :ripple="false"
              text
              title="Filter"
              @click="attendancFilters = true"
            >
              <v-icon dark @click="attendancFilters = true">mdi-filter</v-icon>
            </v-btn>
            <!-- </template>
              <span>Filter</span>
            </v-tooltip> -->

            <v-spacer></v-spacer>
            <!-- <v-tooltip top color="primary">
              <template v-slot:activator="{ on, attrs }"> -->
            <v-btn
              x-small
              :ripple="false"
              text
              title="Render"
              @click="renderVisitorDialog = true"
            >
              <v-icon dark>mdi-cached</v-icon>
            </v-btn>
            <!-- </template>
              <span>Render Fake Data</span>
            </v-tooltip> -->
          </v-toolbar>

          <v-data-table
            dense
            v-if="can(`attendance_report_view`)"
            :headers="headers"
            :items="data"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }"
            class="elevation-1"
            model-value="data.id"
            :server-items-length="totalRowsCount"
          >
            <template v-slot:item.visitor_full_name="{ item }">
              {{ item?.visitor?.first_name }} {{ item?.visitor?.last_name }}
            </template>

            <template
              v-slot:item.actions="{ item }"
              v-if="can('attendance_report_edit')"
            >
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

          <NoAccess v-else />
        </v-card>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-dialog persistent v-model="log_details" max-width="600px">
        <v-card class="darken-1">
          <v-toolbar class="primary" dense dark flat>
            <span class="text-h5 pa-2">Log Details</span>
            <v-spacer></v-spacer>
            Total logs
            <b class="background--text mx-1">({{ log_list.length }})</b>
          </v-toolbar>
          <v-card-text>
            <div class="pt-5">
              <span v-for="(log, index) in log_list" :key="index">
                {{ log.time }}
                <hr />
              </span>
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="renderVisitorDialog" max-width="500px">
        <v-card>
          <v-card-title class="popup_background">
            <span class="headline">Submit Data</span>
            <v-spacer></v-spacer>

            <v-btn
              x-small
              :ripple="false"
              text
              title="Add user"
              @click="addUser"
            >
              <v-icon dark>mdi-plus-circle</v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text>
            <v-form @submit.prevent="submitData">
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="formData.date"
                      label="Date"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-row v-for="(user, i) in users" :key="i">
                      <v-col cols="10">
                        <v-text-field
                          v-model="user.visitor_id"
                          label="User Id"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col cols="2">
                        <v-icon color="error" @click="removeUser(i)"
                          >mdi-delete</v-icon
                        >
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-container>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn small color="primary" @click="submitData">Submit</v-btn>
            <v-btn small color="gray" @click="renderVisitorDialog = false"
              >Cancel</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
function getCurrentDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}
export default {
  data: () => ({
    renderVisitorDialog: false,
    users: [],
    formData: {
      max: 10,
      date: getCurrentDate(),
      company_id: 0,
    },
    attendancFilters: false,
    filters: {},
    isFilter: false,
    totalRowsCount: 0,
    datatable_search_textbox: "",
    datatable_filter_date: "",
    filter_visitor_id: "",
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    filter_visitor_id: "",
    generateLogsDialog: false,
    reportSync: false,
    isCompany: true,
    time_table_dialog: false,
    log_details: false,
    overtime: false,
    options: {},
    date: null,
    menu: false,
    loading: false,
    time_menu: false,
    manual_time_menu: false,
    Model: "Visitor Reports",
    endpoint: "visitor_attendance",
    search: "",
    snackbar: false,
    add_manual_log: false,
    dialog: false,
    from_date: null,
    from_menu: false,
    to_date: null,
    to_menu: false,
    ids: [],
    visitors: [],
    DateRange: true,
    devices: [],
    valid: true,
    nameRules: [(v) => !!v || "reason is required"],
    timeRules: [(v) => !!v || "time is required"],
    deviceRules: [(v) => !!v || "device is required"],
    daily_menu: false,
    daily_date: null,
    dailyDate: false,
    editItems: {
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      time: null,
    },
    loading: false,
    total: 0,
    headers: [
      {
        text: "Date",
        align: "left",
        sortable: true,
        filterable: false,
        value: "date",
      },
      {
        text: "Visitor ID",
        align: "left",
        sortable: true,
        filterable: true,
        value: "visitor_id",
      },
      {
        text: "Full Name",
        align: "left",
        sortable: false,
        filterable: true,
        value: "visitor_full_name",
        key: "item.visitor",
      },

      {
        text: "In",
        align: "left",
        sortable: true,
        filterable: true,
        value: "in",
      },
      {
        text: "Out",
        align: "left",
        sortable: true,
        filterable: true,
        value: "out",
      },
      {
        text: "Total Hrs",
        align: "left",
        sortable: true,
        filterable: true,
        value: "total_hrs",
      },
      {
        text: "Status",
        align: "left",
        sortable: true,
        filterable: true,
        value: "status",
      },

      { text: "Actions", value: "actions", sortable: false },
    ],
    frequency: "Monthly",

    payload: {
      from_date: null,
      to_date: null,
      daily_date: null,
      visitor_id: "",
      status: "All",
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
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  created() {
    this.loading = true;
    this.getVisitors();
    // this.setMonthlyDateRange();
    this.payload.daily_date = new Date().toJSON().slice(0, 10);
    this.custom_options = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
      },
    };
    this.getDeviceList();

    let dt = new Date();
    let y = dt.getFullYear();
    let m = dt.getMonth() + 1;
    let dd = new Date(dt.getFullYear(), m, 0);

    m = m < 10 ? "0" + m : m;

    this.payload.from_date = `${y}-${m}-01`;
    this.payload.to_date = `${y}-${m}-${dd.getDate()}`;

    this.getDataFromApi();
  },

  methods: {
    addUser() {
      this.users.push({ visitor_id: "" });
    },
    removeUser(index) {
      this.users.splice(index, 1);
    },
    submitData() {
      this.formData.userIds = this.users.map((e) => e.visitor_id);
      this.formData.company_id = this.$auth.user.company_id;

      this.$axios
        .post(`/render_daily_report`, this.formData)
        .then(({ data }) => {
          this.getDataFromApi();
          this.snackbar = true;
          this.response = data.message;
          this.dialog = false;
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
    },

    processFilterDates() {
      const date = new Date(this.payload.from_date);

      date.setDate(date.getDate() + (this.frequency == "Weekly" ? 6 : 30));

      const d = date.getDate().toString().padStart(2, "0");
      const m = (date.getMonth() + 1).toString().padStart(2, "0");
      const y = date.getFullYear();

      if (this.frequency !== "Custom") {
        this.payload.to_date = `${y}-${m}-${d}`;
      }

      this.max_date = `${y}-${m}-${d}`;

      this.getDataFromApi();
    },

    set_date_save(from_menu, field) {
      from_menu.save(field);
      this.processFilterDates();
    },
    changeReportFrequency() {
      this.processFilterDates();
    },

    applyFilters(name, value) {
      if (value && value.length < 2) return false;

      this.getDataFromApi();
    },
    toggleFilter() {
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};
      this.isFilter = false;
      this.getDataFromApi();
    },
    getDeviceList() {
      let payload = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/device_list`, payload).then(({ data }) => {
        this.devices = data;
      });
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
          this.loading = false;
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    setVisitorId(id) {
      this.$store.commit("visitor_id", id);
    },

    async getVisitors() {
      // return;
      let payload = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      await this.$axios.get(`/visitors_with_type`, payload).then(({ data }) => {
        this.visitors = data;

        this.visitors.unshift({
          system_user_id: "",
          name_with_user_id: "Select All",
        });
      });
    },
    // getDevices(options) {
    //   this.$axios.get(`/device`, options).then(({ data }) => {
    //     this.devices = data.data;
    //   });
    // },

    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      // if (this.filters) {
      //   page = 1;
      // }

      if (this.frequency == "Custom") {
        if (this.payload.from_date == null) {
          return false;
        }
        if (this.payload.to_date == null) {
          return false;
        }
      }

      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          ...this.payload,
          frequency: this.frequency,
          // status: this.getStatus(this.payload.status),
          ...this.filters,
        },
      };
      if (filter_column != "") options.params[filter_column] = filter_value;
      this.$axios.get(url, options).then(async ({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.data = data.data;

        const uniqueVisitorIds = new Set();
        this.users = await data.data.filter((item) => {
          const visitorId = item["visitor_id"];
          if (!uniqueVisitorIds.has(visitorId)) {
            uniqueVisitorIds.add(visitorId);
            return true;
          }
          return false;
        });

        isFilter: false,
          //  users: [{ userId: 6666 }],
          (this.total = data.total);
        this.loading = false;

        this.totalRowsCount = data.total;
      });
    },

    editItem(item) {
      this.dialog = true;
      this.editItems.UserID = item.visitor_id;
      this.editItems.date = item.edit_date;
    },

    update() {
      if (this.$refs.form.validate()) {
        let payload = {
          UserID: this.editItems.UserID,
          LogTime: this.editItems.date + " " + this.editItems.time,
          DeviceID: this.editItems.device_id,
          user_id: this.editItems.UserID,
          company_id: this.$auth.user.company_id,
          reason: this.editItems.reason,
        };

        this.$axios
          .post("/generate_manual_log", payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = data.errors;
              // this.msg = data.message;
            } else {
              this.snackbar = true;
              this.response = data.message;
              // this.editItems = [];
              // this.renderByType("render_general_report");
              this.close();
            }
          })
          .catch((e) => console.log(e));
      }
    },
    renderByType(type) {
      const UserID = this.editItems.UserID;
      const date = this.editItems.date;

      if (!UserID || !date) {
        alert("System User Id and Date field is required");
        return;
      }

      let payload = {
        params: {
          date: this.editItems.date,
          UserID: this.editItems.UserID,
          updated_by: this.$auth.user.id,
          company_id: this.$auth.user.company_id,
          manual_entry: true,
          reason: this.editItems.reason,
        },
      };

      this.$axios
        .get("/" + type, payload)
        .then(({ data }) => {
          this.loading = false;
          this.snackbar = true;
          this.response = data.message;
          this.getDataFromApi();
        })
        .catch((e) => console.log(e));
    },
    viewItem(item) {
      this.log_list = [];
      let options = {
        params: {
          per_page: 500,
          UserID: item.visitor_id,
          LogTime: item.edit_date,
          company_id: this.$auth.user.company_id,
        },
      };
      this.log_details = true;

      this.$axios.get("visitor_log_list", options).then(({ data }) => {
        this.log_list = data.data;
      });

      // this.editedIndex = this.data.indexOf(item);
      // this.editedItem = Object.assign({}, item);
      // this.dialog = true;
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    process_file(action) {
      if (!this.data.length) {
        alert("No record found");
        return;
      }
      const { visitor_id, daily_date, from_date, to_date, status } =
        this.payload;
      const frequency = this.frequency;
      const company_id = this.$auth.user.company_id;
      let { itemsPerPage } = this.options;
      let path = process.env.BACKEND_URL + "/visitor_attendance_report";

      let qs = ``;

      qs += `${path}`;
      qs += `?company_id=${company_id}`;
      qs += `&status=${status}`;
      qs += `&visitor_id=${visitor_id}`;
      qs += `&frequency=${frequency}`;
      qs += `&action=${action}`;
      qs += `&per_page=${20}`;

      if (frequency == "Daily") {
        qs += `&daily_date=${daily_date}`;
      } else {
        qs += `&from_date=${from_date}&to_date=${to_date}`;
      }

      let report = document.createElement("a");
      report.setAttribute("href", qs);
      report.setAttribute("target", "_blank");
      report.click();

      this.getDataFromApi();
      return;
    },
  },
};
</script>
