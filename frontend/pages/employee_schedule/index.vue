<template>
  <div v-if="can(`employee_schedule_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="editDialog" width="900">
      <v-card>
        <v-card-title class="text-h5">
          {{ !isEdit ? "View Shift(s)" : "Edit Shift(s)" }}
          <v-spacer></v-spacer>
          <v-btn
            v-if="isEdit"
            class="primary"
            small
            fab
            @click="addRow(rosterFirstValue)"
          >
            <b>+</b>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>
        <v-card-text v-for="(item, i) in schedules_temp_list" :key="i">
          <v-row>
            <v-col md="3">
              <div class="">Schedule List</div>
              <v-autocomplete
                outlined
                :readonly="!isEdit"
                dense
                v-model="item.schedule_id"
                x-small
                :items="rosters"
                item-value="schedule_id"
                item-text="name"
              ></v-autocomplete>
            </v-col>
            <v-col md="3">
              <div class="mb-6">
                <div>From</div>
                <!-- <v-menu
                  v-model="from_menu[i]"
                  :close-on-content-click="false"
                  :nudge-right="40"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="item.from_date"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                      outlined
                      dense
                      :hide-details="true"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="item.from_date"
                    @input="from_menu[i] = false"
                  ></v-date-picker>
                </v-menu> -->
                <v-menu
                  ref="from_menu"
                  v-model="from_menu[i]"
                  :close-on-content-click="false"
                  :return-value.sync="item.from_date"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      :hide-details="true"
                      outlined
                      dense
                      v-model="item.from_date"
                      readonly
                      v-bind="!isEdit || attrs"
                      v-on="!isEdit || on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    :readonly="!isEdit"
                    v-model="item.from_date"
                    no-title
                    scrollable
                  >
                    <v-spacer></v-spacer>
                    <v-btn text color="primary" @click="from_menu[i] = false">
                      Cancel
                    </v-btn>
                    <v-btn
                      text
                      color="primary"
                      @click="
                        isEdit
                          ? set_date_save($refs.from_menu[i], item.from_date, i)
                          : ''
                      "
                    >
                      OK
                    </v-btn>
                  </v-date-picker>
                </v-menu>
              </div>
            </v-col>
            <v-col md="3">
              <div class="mb-6">
                <div>To</div>
                <v-menu
                  v-model="to_menu[i]"
                  :close-on-content-click="false"
                  :nudge-right="40"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                  :readonly="!isEdit"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="item.to_date"
                      readonly
                      v-bind="!isEdit || attrs"
                      v-on="!isEdit || on"
                      outlined
                      dense
                      :hide-details="true"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    :readonly="!isEdit"
                    v-model="item.to_date"
                    @input="to_menu[i] = false"
                  ></v-date-picker>
                </v-menu>
              </div>
            </v-col>
            <v-col md="2">
              <div>
                Overtime Allowed
                <v-checkbox
                  :readonly="!isEdit"
                  style="margin-top: -8px"
                  v-model="item.is_over_time"
                ></v-checkbox>
              </div>
            </v-col>
            <v-col md="1" v-if="isEdit">
              <div></div>
              <v-icon @click="removeItem(i, item)" color="error"
                >mdi-delete</v-icon
              >
            </v-col>
          </v-row>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn dark small color="grey" @click="editDialog = false">
            Close
          </v-btn>
          <v-btn v-if="isEdit" dark small color="primary" @click="update">
            Submit
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialog" width="1300">
      <v-card>
        <v-card-title class="text-h5">
          Schedule Employees
          <v-spacer></v-spacer>
          <v-btn dark small color="grey" @click="close"> Close </v-btn> &nbsp;
          <v-btn dark small color="primary" @click="save"> Submit </v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-row>
            <v-col md="4">
              <v-row>
                <v-col md="12">
                  <div class="mb-5">
                    <span class="text-h6">Filters</span>
                  </div>
                  <div class="mb-1">Department</div>

                  <v-autocomplete
                    outlined
                    dense
                    @change="runMultipleFunctions"
                    v-model="department_ids"
                    multiple
                    x-small
                    :items="departments"
                    item-value="id"
                    item-text="name"
                    :disabled="is_edit == true ? true : false"
                  ></v-autocomplete>
                  <div class="mb-1">Sub Department</div>
                  <v-autocomplete
                    outlined
                    dense
                    @change="getEmployeesBySubDepartment"
                    v-model="sub_department_ids"
                    multiple
                    x-small
                    :items="sub_departments"
                    item-value="id"
                    item-text="name"
                    :disabled="is_edit == true ? true : false"
                  ></v-autocomplete>

                  <div class="mb-1">Shift Types</div>

                  <v-autocomplete
                    :error="errors && errors.shift_type_id"
                    :error-messages="
                      errors && errors.shift_type_id
                        ? errors.shift_type_id[0]
                        : ''
                    "
                    @change="runShiftTypeFunction"
                    outlined
                    dense
                    v-model="shift_type_id"
                    x-small
                    :items="shift_types"
                    item-value="id"
                    item-text="name"
                  ></v-autocomplete>

                  <div class="mb-1">Shifts</div>
                  <v-autocomplete
                    :error="errors && errors.shift_id"
                    :error-messages="
                      errors && errors.shift_id ? errors.shift_id[0] : ''
                    "
                    @change="runShiftFunction"
                    outlined
                    dense
                    v-model="shift_id"
                    x-small
                    :items="shifts"
                    item-value="id"
                    item-text="name"
                  ></v-autocomplete>
                  <div class="mb-6">
                    <div>From</div>
                    <v-menu
                      v-model="from_menu"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="from_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          outlined
                          dense
                          :hide-details="true"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="from_date"
                        @input="from_menu = false"
                      ></v-date-picker>
                    </v-menu>
                  </div>
                  <div class="mb-6">
                    <div>To</div>
                    <v-menu
                      v-model="to_menu"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="to_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          outlined
                          dense
                          :hide-details="true"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="to_date"
                        @input="to_menu = false"
                      ></v-date-picker>
                    </v-menu>
                  </div>
                  <v-checkbox
                    dense
                    v-model="isOverTime"
                    label="Overtime Allowed"
                  ></v-checkbox>
                </v-col>
              </v-row>
            </v-col>

            <v-col md="8">
              <v-row>
                <v-col md="6">
                  <div class="mb-5">
                    <span class="text-h6">Employees List</span>
                  </div>
                </v-col>
                <v-col md="6">
                  <div class="text-right">
                    <v-text-field
                      @input="dialogSearchIt"
                      dense
                      v-model="dialog_search"
                      append-icon="mdi-magnify"
                      single-line
                      hide-details
                    ></v-text-field>
                  </div>
                </v-col>
              </v-row>

              <v-data-table
                v-model="employee_ids"
                show-select
                item-key="id"
                :headers="headers_dialog"
                :items="employees_dialog"
                :server-items-length="total_dialog"
                :loading="loading_dialog"
                :options.sync="options_dialog"
                :footer-props="{
                  itemsPerPageOptions: [10, 50, 100, 500, 1000],
                }"
              >
              </v-data-table>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>

          <v-btn dark small color="grey" @click="close"> Close </v-btn>
          <v-btn dark small color="primary" @click="save"> Submit </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-card class="mb-5 rounded-md mt-3" elevation="0">
      <v-toolbar class="rounded-md" color="background" dense flat dark>
        <v-toolbar-title><span> Schedule List</span></v-toolbar-title>
        <a
          style="padding-left: 10px"
          title="Reload Page/Reset Form"
          @click="getDataFromApi"
          ><v-icon class="mx-1">mdi mdi-reload</v-icon></a
        >
        <v-spacer></v-spacer>
        <v-icon
          v-if="can(`employee_schedule_create`)"
          @click="gotoCreateSchedule"
          >mdi-plus</v-icon
        >
      </v-toolbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-data-table
        dense
        :headers="headers_table"
        :items="employees"
        model-value="data.id"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [50, 100, 500, 1000],
        }"
        class="elevation-1"
      >
        <template v-slot:item.sno="{ item, index }">
          <b>{{ ++index }}</b>
        </template>
        <template v-slot:item.employee_id="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ caps(item.employee.employee_id) }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_employee_id', $event)"
                label="Search Employee ID"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.employee.first_name="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ caps(item.employee && item.employee.first_name) }}
            {{ caps(item.employee && item.employee.last_name) }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_employee_name', $event)"
                label="Search Employee name"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.roster.name="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ caps(item.roster && item.roster.name) }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_schedule_name', $event)"
                label="Search Schedule name"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.show_from_date="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ item && item.from_date }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_from_date', $event)"
                label="Search From Date"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.show_to_date="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ item && item.to_date }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_to_date', $event)"
                label="Search Shift name"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.isOverTime="{ item }">
          <v-icon v-if="item && item.isOverTime" color="success darken-1"
            >mdi-check</v-icon
          >
          <v-icon v-else color="error">mdi-close</v-icon>
        </template>
        <template v-slot:item.shift.name="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ item.shift && item.shift.name }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_shift_name', $event)"
                label="Search Shift name"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.shift_type.name="{ item }">
          <v-edit-dialog
            large
            save-text="Reset"
            cancel-text="Ok"
            style="margin-left: 4%"
            @cancel="getDataFromApi()"
            @save="getDataFromApi()"
            @open="datatable_open"
          >
            {{ item.shift_type && item.shift_type.name }}
            <template v-slot:input>
              <v-text-field
                v-model="datatable_search_textbox"
                @input="getSearchRecords('search_shift_type', $event)"
                label="Search Shift Type"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.action="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="120" dense>
              <v-list-item @click="ScheduleItem(item, 'view')">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>
              <v-list-item @click="ScheduleItem(item, 'edit')">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-card>
    <v-row>
      <v-col md="12" class="float-right">
        <div class="float-right">
          <v-pagination
            v-model="pagination.current"
            :length="pagination.total"
            @input="onPageChange"
            :total-visible="12"
          ></v-pagination>
        </div>
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    datatable_search_textbox: "",
    datatable_searchById: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    displayNoRecords: false,
    from_date: new Date().toJSON().slice(0, 10),
    from_menu: false,
    to_date: new Date().toJSON().slice(0, 10),
    to_menu: false,

    from_menu: [],
    to_menu: [],

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    Module: "Employee Schedule",
    shift_types: [],
    manual_shift: {},
    options: {},
    options_dialog: {},
    endpoint: "scheduled_employees_index",
    endpoint_dialog: "scheduled_employees_list",
    search: "",
    shifts_for_filter: [],
    dialog_search: "",
    snackbar: false,
    dialog: false,
    editDialog: false,

    loading: false,
    loading_dialog: false,
    isEdit: false,
    total: 0,
    total_dialog: 0,
    headers: [
      {
        text: "#",
      },
      {
        text: "E.ID",
        align: "left",
        sortable: false,
        value: "system_user_id",
      },
      {
        text: "Name",
        align: "left",
        sortable: false,
        value: "display_name",
      },
      {
        text: "Schedule Start",
        align: "left",
        sortable: false,
        value: "from_date",
      },
      {
        text: "Schedule Start",
        align: "left",
        sortable: false,
        value: "to_date",
      },
      {
        text: "Shift Type",
        align: "left",
        sortable: false,
        value: "shift_type",
      },
      {
        text: "Schedule",
        align: "left",
        sortable: false,
        value: "schedule",
      },
      {
        text: "OT",
        align: "left",
        sortable: false,
        value: "schedule.isOverTime",
      },

      {
        text: "Actions",
        align: "center",
        value: "action",
        sortable: false,
      },
    ],
    headers_table: [
      {
        text: "#",
        align: "center",
        value: "sno",
        sortable: false,
      },
      {
        text: "Emp Id",
        align: "left",
        sortable: true,
        value: "employee_id",
      },
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "employee.first_name",
      },
      {
        text: "	Current Schedule Name",
        align: "left",
        sortable: true,
        value: "roster.name",
      },
      {
        text: "Schedule Start",
        align: "left",
        sortable: true,
        value: "show_from_date",
      },
      {
        text: "Schedule Start",
        align: "left",
        sortable: true,
        value: "show_to_date",
      },
      {
        text: "OT",
        align: "left",
        sortable: true,
        value: "isOverTime",
      },
      {
        text: "Shift Name",
        align: "left",
        sortable: true,
        value: "shift.name",
      },
      {
        text: "Shift Type",
        align: "left",
        sortable: true,
        value: "shift_type.name",
      },

      {
        text: "Actions",
        align: "center",
        value: "action",
        sortable: false,
      },
    ],
    department_ids: ["---"],
    sub_department_ids: ["---"],
    employee_ids: [],
    shift_id: null,
    shift_type_id: "",
    isOverTime: false,
    is_edit: false,
    shift_slug: "",
    employees: [],
    employees_dialog: [],
    departments: [],
    sub_departments: [],
    shifts: [],
    ids: [],
    response: "",
    data: [],

    errors: [],
    headers_ids: [],
    rosters: [],
    rosterFirstValue: "",
    headers_dialog: [
      {
        text: "E.ID",
        align: "left",
        sortable: false,
        value: "system_user_id",
      },
      {
        text: "Name",
        sortable: true,
        value: "employee.first_name",
      },
      {
        text: "Department",
        sortable: false,
        value: "department.name",
      },
    ],

    deleteIds: [],
    schedules_temp_list: [],
    empId: "",
  }),

  computed: {},

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
      if (!this.is_edit) {
        this.getDepartments(this.options);
        this.getDataFromApiForDialog();
      }
      this.getShiftTypes(this.options);
    },
    options: {
      handler() {
        //this.getDataFromApi();
      },
      deep: true,
    },
    options_dialog: {
      handler() {
        if (!this.is_edit) {
          this.getDataFromApiForDialog();
        }
      },
      deep: true,
    },
    search() {
      this.pagination.current = 1;
      this.searchIt();
    },
  },
  created() {
    this.loading = true;
    this.loading_dialog = true;
    this.get_rosters();
    this.getDataFromApi();
    this.options = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      },
    };
  },

  methods: {
    gotoCreateSchedule () {
      this.$router.push(`/employee_schedule/create`);
    },
    datatable_save() {},
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
      //this.datatable_search_textbox = '';
    },
    onPageChange() {
      this.getDataFromApi();
    },

    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    ScheduleItem(item, type) {
      this.empId = item.employee_id;
      let id = item.employee_id;
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios
        .get(`get_roster_by_employee/${id}`, { params: options })
        .then(({ data }) => {
          type == "edit" ? (this.isEdit = true) : (this.isEdit = false);
          this.schedules_temp_list = data;
          this.editDialog = true;
        });
    },

    set_date_save(from_menu, from, index) {
      from_menu.save(from);
      let toDate = this.setSevenDays(from);
      this.schedules_temp_list[index].to_date = toDate;
    },

    setSevenDays(selected_date) {
      const date = new Date(selected_date);
      date.setDate(date.getDate() + 6);
      let datetime = new Date(date);
      let d = datetime.getDate();
      d = d < "10" ? "0" + d : d;
      let m = datetime.getMonth() + 1;
      m = m < 10 ? "0" + m : m;
      let y = datetime.getFullYear();
      return `${y}-${m}-${d}`;
    },

    update() {
      let payload = {
        schedules: this.schedules_temp_list,
        deleteIds: this.deleteIds,
        company_id: this.$auth.user.company.id,
      };
      this.process(this.$axios.put(`schedule_update/${this.empId}`, payload));
    },

    removeItem(i, item) {
      if (item.id) {
        this.deleteIds.push(item.id);
      }
      this.schedules_temp_list.splice(i, 1);
    },

    get_rosters() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get("roster_list", { params: options }).then(({ data }) => {
        this.rosters = data;
        this.addRow(data[0].schedule_id);
        this.rosterFirstValue = data[0].schedule_id;
      });
    },

    addRow(id) {
      let item = {
        schedule_id: id,
        from_date: new Date().toJSON().slice(0, 10),
        to_date: new Date().toJSON().slice(0, 10),
        is_over_time: false,
      };
      if (this.schedules_temp_list.length < 5) {
        this.schedules_temp_list.push(item);
      }
    },

    runShiftTypeFunction() {
      this.getShifts(this.shift_type_id);
    },

    close() {
      this.dialog = false;
      this.is_edit = false;
    },

    getShifts(shift_type_id) {
      if (this.shift_type_id == 3) {
        this.shift_id = 0;
        this.shifts = [];
        return;
      }

      let options = {
        params: {
          shift_type_id: shift_type_id,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios
        .get("shift_by_type", options)
        .then(({ data }) => {
          this.shifts = data;
          // this.shifts.unshift({ id: "", name: "Select Shift" });
          // this.shifts_for_filter = data;
        })
        .catch((err) => console.log(err));
    },

    getShiftTypes(options) {
      this.$axios
        .get("shift_type", options)
        .then(({ data }) => {
          this.shift_types = data;
          this.shift_types.unshift({ id: "", name: "Select Shift Type" });
        })
        .catch((err) => console.log(err));
    },

    runShiftFunction() {
      this.shifts = this.shifts.filter((e) => e.id !== "---");
    },

    getDepartments(options) {
      this.$axios
        .get("departments", options)
        .then(({ data }) => {
          this.departments = data.data;
          this.departments.unshift({ id: "---", name: "Select All" });
        })
        .catch((err) => console.log(err));
    },

    employeesByDepartment() {
      this.loading_dialog = true;

      const { page, itemsPerPage } = this.options_dialog;

      let options = {
        params: {
          department_ids: this.department_ids,
          per_page: itemsPerPage,
          page: page,
          company_id: this.$auth.user.company.id,
        },
      };

      if (!this.department_ids.length) {
        this.employees_dialog = [];
        this.total_dialog = 0;
        this.loading_dialog = false;
        return;
      }

      this.$axios.get("employeesByDepartment", options).then(({ data }) => {
        this.employees_dialog = data.data;
        this.total_dialog = data.total;
        this.loading_dialog = false;
      });
    },

    getEmployeesBySubDepartment() {
      this.loading_dialog = true;

      const { page, itemsPerPage } = this.options_dialog;

      let options = {
        params: {
          department_ids: this.department_ids,
          sub_department_ids: this.sub_department_ids,
          per_page: itemsPerPage,
          page: page,
          company_id: this.$auth.user.company.id,
        },
      };

      if (!this.sub_department_ids.length) {
        this.loading_dialog = false;
        return;
      }

      this.$axios
        .get(`employeesBySubDepartment`, options)
        .then(({ data }) => {
          this.employees_dialog = data.data;
          this.total_dialog = data.total;
          this.loading_dialog = false;
        })
        .catch((err) => console.log(err));
    },

    subDepartmentsByDepartment() {
      this.options.params.department_ids = this.department_ids;

      this.$axios
        .get(`sub-departments-by-departments`, this.options)
        .then(({ data }) => {
          this.sub_departments = data;
          this.sub_departments.unshift({
            id: "---",
            name: "Select All",
          });
        })
        .catch((err) => console.log(err));
    },

    runMultipleFunctions() {
      this.employeesByDepartment();
      this.subDepartmentsByDepartment();
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    getSearchRecords(filter_column = "", filter_value = "") {
      this.getDataFromApi(this.endpoint, filter_column, filter_value);
    },
    //main
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      this.loading = true;

      let page = this.pagination.current;

      let options = {
        params: {
          per_page: this.pagination.per_page,
          page: page,
          company_id: this.$auth.user.company.id,
        },
      };

      if (filter_value != "") options.params[filter_column] = filter_value;

      this.$axios.get(url, options).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.employees = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;

        if (this.employees.length == 0) {
          this.displayNoRecords = true;
        }
      });

      this.loading = false;
    },

    getDataFromApiForDialog(url = this.endpoint_dialog) {
      this.loading_dialog = true;

      const { page, itemsPerPage } = this.options_dialog;

      let options = {
        params: {
          per_page: itemsPerPage,
          page: page,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.employees_dialog = data.data;
        this.total_dialog = data.total;
        this.loading_dialog = false;
      });
    },

    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
      }
    },

    dialogSearchIt(e) {
      if (e.length == 0) {
        this.getDataFromApiForDialog();
      } else if (e.length > 2) {
        this.employees_dialog = this.employees.filter(({ display_name: fn }) =>
          fn.includes(e)
        );
      }
    },

    delteteSelectedRecords() {
      let just_ids = this.ids.map((e) => e.schedule.id);

      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`schedule_employee/delete/selected`, {
            ids: just_ids,
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
              alert("1");
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
          })
          .catch((err) => console.log(err));
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete("schedule_employees/" + item.employee.system_user_id)
          .then(({ data }) => {
            const index = this.employees.indexOf(item);
            this.employees.splice(index, 1);
            this.snackbar = data.status;
            this.response = data.message;
            this.getDataFromApiForDialog();
          })
          .catch((err) => console.log(err));
    },

    save() {
      this.loading_dialog = true;
      if (this.employee_ids && this.employee_ids.length == 0) {
        this.loading_dialog = false;
        alert("Atleast 1 Employee must be selected");
        return;
      }
      this.errors = [];

      let payload = {
        shift_type_id: this.shift_type_id,
        shift_id: this.shift_id,
        company_id: this.$auth.user.company.id,
        isOverTime: this.isOverTime ? 1 : 0,
        employee_ids: this.employee_ids.map((e) => e.system_user_id),

        from_date: this.from_date,
        to_date: this.to_date,
      };

      if (this.is_edit) {
        this.process(
          this.$axios.post(
            `schedule_employees/${payload.employee_ids}`,
            payload
          )
        );
      } else {
        this.process(this.$axios.post(`schedule_employees`, payload));
      }
    },

    process(method) {
      method
        .then(({ data }) => {
          if (!data.status) {
            if (data?.custom_errors) {
              this.custom_errors = data.custom_errors;
              this.errors = [];
            }
            if (data?.errors) {
              this.errors = data.errors;
              this.custom_errors = [];
            }
            this.loading_dialog = false;
            return;
          }
          this.response = data.message;
          this.snackbar = true;
          this.loading_dialog = false;
          this.editDialog = false;
          this.getDataFromApi();
          this.getDataFromApiForDialog();
        })
        .catch((err) => console.log(err));
    },
  },
};
</script>
