<template>
  <div>
    <GenerateLog />
    <v-card elevation="2" class="pa-5 mt-5">
      <v-row class="pt-2">
        <v-col md="12">
          <h5>Filters</h5>
        </v-col>
        <v-col md="5">
          Departments
          <v-autocomplete
            class="mt-2"
            outlined
            dense
            v-model="payload.department_id"
            x-small
            :items="departments"
            item-value="id"
            item-text="name"
          ></v-autocomplete>
        </v-col>
        <v-col md="5">
          Employee ID
          <v-autocomplete
            class="mt-2"
            outlined
            dense
            v-model="payload.employee_id"
            x-small
            :items="scheduled_employees"
            item-value="system_user_id"
            item-text="name_with_user_id"
          ></v-autocomplete>
        </v-col>

        <v-col md="5">
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
                <div class="mb-1">
                  From Date
                </div>
                <v-text-field
                  :hide-details="!payload.from_date"
                  outlined
                  dense
                  v-model="payload.from_date"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                ></v-text-field>
              </template>
              <v-date-picker v-model="payload.from_date" no-title scrollable>
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="from_menu = false">
                  Cancel
                </v-btn>
                <v-btn
                  text
                  color="primary"
                  @click="$refs.from_menu.save(payload.from_date)"
                >
                  OK
                </v-btn>
              </v-date-picker>
            </v-menu>
          </div>
        </v-col>
        <v-col md="5">
          <div class="mb-1">
            To Date
          </div>
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
                  :hide-details="!payload.to_date"
                  outlined
                  dense
                  v-model="payload.to_date"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                ></v-text-field>
              </template>
              <v-date-picker v-model="payload.to_date" no-title scrollable>
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="to_menu = false">
                  Cancel
                </v-btn>
                <v-btn
                  text
                  color="primary"
                  @click="$refs.to_menu.save(payload.to_date)"
                >
                  OK
                </v-btn>
              </v-date-picker>
            </v-menu>
          </div>
        </v-col>

        <v-col md="5">
          Status
          <v-select
            @change="getItemsFilters"
            class="mt-2"
            outlined
            dense
            v-model="payload.status_id"
            x-small
            :items="[
              { id: -1, name: `Select All` },
              { id: 1, name: `Present` },
              { id: 2, name: `Absent` },
              { id: 3, name: `Missing` }
            ]"
            item-value="id"
            item-text="name"
          ></v-select>
        </v-col>

        <v-col md="5">
          Late/Early
          <v-select
            @change="getItemsFilters"
            class="mt-2"
            outlined
            dense
            v-model="payload.late_early_id"
            x-small
            :items="[
              { id: -1, name: `Select All` },
              { id: 1, name: `Late` },
              { id: 2, name: `Early` }
            ]"
            item-value="id"
            item-text="name"
          ></v-select>
        </v-col>
        <v-col md="5">
          Per Page
          <v-select
            @change="getItemsFilters"
            class="mt-2"
            outlined
            dense
            v-model="payload.per_page"
            x-small
            :items="[5, 10, 15]"
            item-value="id"
            item-text="name"
          ></v-select>
        </v-col>
        <v-col md="12">
          <v-checkbox
            @click="getItemsFilters"
            dense
            v-model="overtime"
            label="Overtime"
            hide-details
          />
        </v-col>

        <v-col md="12">
          <div class="mb-5">
            <v-btn
              small
              :loading="loading"
              color="primary"
              @click="getDataFromApi"
            >
              <v-icon small class="pr-1">mdi-history</v-icon>
              Fetch records
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-card>

    <v-card class="mt-5 pa-5">
      <v-row>
        <v-col md="6">
          <h3>Attendance Logs</h3>
          <div>Dashboard / Attendance Logs</div>
        </v-col>

        <v-col md="12">
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Date</th>
                  <th class="text-left">E.ID</th>
                  <th class="text-left">First Name</th>
                  <th class="text-left">Dept</th>
                  <th class="text-left">Shift</th>

                  <th class="text-left">Status</th>

                  <th class="text-left">In</th>
                  <th class="text-left">Out</th>
                  <th class="text-left">Total Hrs</th>
                  <th class="text-left">OT</th>

                  <th class="text-left">Late Coming</th>
                  <th class="text-left">Early Going</th>

                  <th class="text-left">D.In</th>
                  <th class="text-left">D.Out</th>

                  <th class="text-left">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in filteredData" :key="index">
                  <td>{{ item.checkin_date }}</td>
                  <td>{{ item.EID }}</td>
                  <td>{{ item.first_name }}</td>
                  <td>{{ item.department }}</td>

                  <td>
                    <v-tooltip top color="primary">
                      <template v-slot:activator="{ on, attrs }">
                        <div class="primary--text" v-bind="attrs" v-on="on">
                          {{ item.shift_name || "---" }}
                        </div>
                      </template>
                      <div>
                        <span
                          >Beginning Date:
                          {{ item.time_table.beginning_date || "---" }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Cycle Number:
                          {{ item.time_table.cycle_number || "---" }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Holidays: {{ item.time_table.days || "---" }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Shift Type: {{ item.shift_type_name || "---" }}</span
                        >
                      </div>
                      <div>
                        <span>
                          Minimum Working Hours:
                          {{ item.time_table.working_hours || "---" }}
                        </span>
                      </div>
                      <div>
                        <span
                          >Duty Start Time:
                          {{ item.time_table.on_duty_time || "---" }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Duty End Time:
                          {{ item.time_table.off_duty_time || "---" }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Late Minutes Allowed:
                          {{
                            item.time_table.late_minutes_allowed || "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Early Minutes Allowed:
                          {{
                            item.time_table.early_minutes_allowed || "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Absent Min For Check In:
                          {{
                            item.time_table.check_in_absent_min || "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Absent Min For Check Out:
                          {{
                            item.time_table.check_out_absent_min || "---"
                          }}</span
                        >
                      </div>

                      <div>
                        <span
                          >Beginning In:
                          {{ item.time_table.beginning_in || "---" }}</span
                        >
                      </div>

                      <div>
                        <span
                          >Beginning Out:
                          {{ item.time_table.beginning_out || "---" }}</span
                        >
                      </div>

                      <div>
                        <span
                          >Ending In:
                          {{ item.time_table.ending_in || "---" }}</span
                        >
                      </div>

                      <div>
                        <span
                          >Ending Out:
                          {{ item.time_table.ending_out || "---" }}</span
                        >
                      </div>
                    </v-tooltip>
                  </td>
                  <td>
                    <span v-if="typeof item.status == 'string'">{{
                      item.status
                    }}</span>

                    <v-icon v-else-if="item.status" color="success darken-1"
                      >mdi-check</v-icon
                    >
                    <v-icon v-else color="error">mdi-close</v-icon>
                  </td>

                  <td>
                    <v-tooltip
                      v-if="item.check_in_reason.reason"
                      top
                      color="primary"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <span class="primary--text" v-bind="attrs" v-on="on">
                          {{ item.check_in }}
                        </span>
                      </template>
                      <div>
                        <span
                          >Reason:
                          {{
                            item.check_in_reason.reason &&
                              item.check_in_reason.reason
                          }}</span
                        >
                      </div>
                      <div>
                        <span v-if="item.check_in_reason.reason"
                          >Edited By:
                          {{
                            item.check_in_reason.user &&
                              item.check_in_reason.user.name
                          }}</span
                        >
                      </div>
                    </v-tooltip>
                    <span v-else>{{ item.check_in }}</span>
                  </td>
                  <td>
                    <v-tooltip
                      v-if="item.check_out_reason.reason"
                      top
                      color="primary"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <span class="primary--text" v-bind="attrs" v-on="on">
                          {{ item.check_out }}
                        </span>
                      </template>
                      <div>
                        <span
                          >Reason:
                          {{
                            item.check_out_reason.reason &&
                              item.check_out_reason.reason
                          }}</span
                        >
                      </div>
                      <div>
                        <span v-if="item.check_out_reason.reason"
                          >Edited By:
                          {{
                            item.check_out_reason.user &&
                              item.check_out_reason.user.name
                          }}</span
                        >
                      </div>
                    </v-tooltip>
                    <span v-else>{{ item.check_out }}</span>
                  </td>
                  <td>{{ item.diff }}</td>
                  <td>{{ item.ot }}</td>
                  <td>{{ item.late_coming }}</td>
                  <td>{{ item.early_going }}</td>
                  <td>
                    <v-tooltip top color="primary">
                      <template v-slot:activator="{ on, attrs }">
                        <div class="primary--text" v-bind="attrs" v-on="on">
                          {{ item.device_in.short_name }}
                        </div>
                      </template>
                      <div>
                        <span
                          >Device Full Name:
                          {{
                            (item.device_in &&
                              item.device_in.device_full_name) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Device Short Name:
                          {{
                            (item.device_in && item.device_in.short_name) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Device Id:
                          {{
                            (item.device_in && item.device_in.device_id) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Device Type:
                          {{
                            (item.device_in && item.device_in.device_type) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Model Number:
                          {{
                            (item.device_in && item.device_in.model_number) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Location:
                          {{
                            (item.device_in && item.device_in.location) || "---"
                          }}</span
                        >
                      </div>
                    </v-tooltip>
                  </td>

                  <td>
                    <v-tooltip top color="primary">
                      <template v-slot:activator="{ on, attrs }">
                        <div class="primary--text" v-bind="attrs" v-on="on">
                          {{
                            (item.device_out && item.device_out.short_name) ||
                              "---"
                          }}
                        </div>
                      </template>
                      <div>
                        <span
                          >Device Full Name:
                          {{
                            (item.device_out &&
                              item.device_out.device_full_name) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Device Short Name:
                          {{
                            (item.device_out && item.device_out.short_name) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Device Id:
                          {{
                            (item.device_out && item.device_out.device_id) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Device Type:
                          {{
                            (item.device_out && item.device_out.device_type) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Model Number:
                          {{
                            (item.device_out && item.device_out.model_number) ||
                              "---"
                          }}</span
                        >
                      </div>
                      <div>
                        <span
                          >Location:
                          {{
                            (item.device_out && item.device_out.location) ||
                              "---"
                          }}</span
                        >
                      </div>
                    </v-tooltip>
                  </td>

                  <td>
                    <v-icon @click="editItem(item)" small color="secondary"
                      >mdi-pencil</v-icon
                    >
                    <!-- <v-icon small color="primary" @click="getLogDetails(item)"
                      >mdi-eye</v-icon
                    > -->
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
          <!-- <v-pagination class="mt-5" v-model="page" :length="4" circle></v-pagination> -->
        </v-col>
      </v-row>
    </v-card>
    <v-row justify="center">
      <v-dialog v-model="dialog" max-width="700px">
        <v-card>
          <v-card-title>
            <span class="headline"> Create Manual Log </span>
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
                          label="Time In"
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
                      item-value="device_id"
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
  </div>
</template>

<script>
export default {
  data() {
    return {
      page: 1,
      overtime: false,
      dialog: false,
      devices: [],
      errors: [],
      title: `Attendance Logs`,
      endpoint: "attendance_logs",
      valid: true,
      nameRules: [v => !!v || "reason is required"],
      timeRules: [v => !!v || "time is required"],
      deviceRules: [v => !!v || "device is required"],
      time_menu: false,
      response: false,
      from_date: null,
      from_menu: false,
      to_date: null,
      to_menu: false,
      payload: {
        per_page: 5,
        from_date: null,
        to_date: null,
        employee_id: null,
        department_id: -1,
        status_id: -1,
        late_early_id: -1
      },
      log_details: {},
      first: {},
      second: {},
      ids: [],
      data: [],
      filteredData: [],
      search: "",
      loading: false,
      page: 1,
      total: 0,
      page_options: {},
      options: {
        per_page: 5
      },
      pagination: {},
      departments: [],
      scheduled_employees: [],
      editItems: {
        attendance_logs_id: "",
        UserID: "",
        device_id: "",
        user_id: "",
        reason: "",
        date: null,
        time: null
      }
    };
  },
  async created() {
    let dt = new Date();
    let y = dt.getFullYear();
    let m = dt.getMonth() + 1;
    m = m < 10 ? "0" + m : m;
    this.payload.from_date = `${y}-${m}-01`;
    this.payload.to_date = `${y}-${m}-${31}`;

    this.options = {
      params: {
        per_page: 1000,
        shift_type: "manual_shift",
        company_id: this.$auth.user.company.id
      }
    };
    this.getDepartments(this.options);
    this.getScheduledEmployees(this.options);
    this.getDevices(this.options);
    // this.getEmployees(this.options);
  },
  methods: {
    getScheduledEmployees(options) {
      this.$axios
        .get(`/scheduled_employees_with_type`, options)
        .then(({ data }) => {
          this.scheduled_employees = data;
        });
    },

    getDevices(options) {
      this.$axios.get(`/device`, options).then(({ data }) => {
        this.devices = data.data;
      });
    },

    editItem(item) {
      this.dialog = true;
      this.editItems.UserID = item.EID;
      this.editItems.attendance_logs_id = item.attendance_logs_id;
      this.editItems.date = item.edit_date;
    },

    update() {
      if (this.$refs.form.validate()) {
        let payload = {
          UserID: this.editItems.UserID,
          LogTime: this.editItems.date + " " + this.editItems.time + ":00",
          DeviceID: this.editItems.device_id,
          user_id: this.$auth.user.id,
          company_id: this.$auth.user.company.id,
          reason: this.editItems.reason
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
              this.editItems = [];
              this.close();
            }
          })
          .catch(e => console.log(e));
      }
    },

    close() {
      this.dialog = false;
    },
    getItemsFilters() {
      let status = null;
      let status_id = this.payload.status_id;

      if (status_id != -1) {
        if (status_id == 1) {
          status = true;
        } else if (status_id == 2) {
          status = false;
        } else {
          status = "---";
        }
      }

      let le_id = this.payload.late_early_id;
      let data = this.data;

      if (le_id != -1) {
        data = data.filter(({ late_coming, early_going }) =>
          le_id == 1 ? late_coming !== "---" : early_going !== "---"
        );
      } else {
        data = this.data;
      }

      if (this.overtime) {
        data = data.filter(e => e.ot != "---");
      }

      this.filteredData =
        status != null ? data.filter(e => e.status == status) : data;

      this.filteredData = this.filteredData.slice(0, this.payload.per_page);
    },
    getLogDetails(item) {
      let s = item.edit_date.split("-");

      item.to_date = `${y}-${m}-${d}`;
      console.log(
        "🚀 ~ file: CustomReport.vue ~ line 467 ~ getLogDetails ~ item",
        item
      );
      return;
      this.dialog = true;

      this.options = {
        params: {
          EID: item.EID,
          date: item.checkin_date,
          company_id: this.$auth.user.company.id
        }
      };

      this.log_details = this.options;
      this.$axios.get("/attendance_logs_details", this.options).then(res => {
        console.log(res.data);
      });
    },
    CheckIfTotalHrsMins(diff) {
      if (isNaN(diff)) {
        return "---";
      }
      let h = Math.floor(diff / 60);
      let m = Math.floor(diff % 60);
      return `${h < 10 ? "0" + h : h}:${m < 10 ? "0" + m : m}`;
    },
    getDepartments(options) {
      this.$axios
        .get("departments", options)
        .then(({ data }) => {
          this.departments = [{ id: -1, name: "Select All" }].concat(data.data);
        })
        .catch(err => console.log(err));
    },
    showItem(id) {
      this.$router.push(`/attendance_report_no_shift/${id}`);
    },
    caps(str) {
      if (!str) {
        str = "all data";
      }
      return str.replace(/\b\w/g, c => c.toUpperCase());
    },
    fetch_report() {
      this.search = "";
      this.getDataFromApi();
    },
    fetch_logs() {
      this.loading = true;
      let payload = {
        company_id: this.$auth.user.company.id
      };
      this.$axios.post(`attendance_logs`, payload).then(({ data }) => {
        this.getDataFromApi();
        this.loading = false;
      });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(
          `attendance_logs/${this.$auth.user.company.id}/search/${e}`
        );
      }
    },
    json_to_csv(json) {
      let data = json.map(e => ({
        "Employee ID": e.system_user_id,
        "Full Name": e.full_name,
        Department: e.employee.department.name,
        "Date In": e.show_date_in,
        "Date Out": e.show_date_out,
        "Time In": e.show_time_in,
        "Time Out": e.show_time_out,
        "Total Hours": null
      }));
      let header = Object.keys(data[0]).join(",") + "\n";
      let rows = "";
      data.forEach(e => {
        rows +=
          Object.values(e)
            .join(",")
            .trim() + "\n";
      });
      return header + rows;
    },
    export_submit() {
      if (this.data.length == 0) {
        this.snackbar = true;
        this.response = "No record to download";
        return;
      }
      let csvData = this.json_to_csv(this.data);
      let a = document.createElement("a");
      a.setAttribute(
        "href",
        "data:text/csv;charset=utf-8, " + encodeURIComponent(csvData)
      );
      a.setAttribute("download", "logs.csv");
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    },
    getDataFromApi() {
      this.loading = true;

      let payload = {
        params: {
          ...this.payload,
          company_id: this.$auth.user.company.id
        }
      };

      this.$axios.get(`manual_report`, payload).then(({ data }) => {
        this.data = data;
        this.getItemsFilters();
        this.loading = false;
      });
    }
  }
};
</script>
<style>
.v-data-table-header-mobile {
  display: none !important;
}
</style>
