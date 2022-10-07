<template>
  <div v-if="can(`employee_schedule_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialog" width="1100">
      <v-card>
        <v-card-title class="text-h5"> Schedule Employees </v-card-title>
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

                  <div class="mb-1" v-if="manual_shift.slug == `manual_shift`">
                    Shifts
                  </div>
                  <v-autocomplete
                    v-if="manual_shift.slug == `manual_shift`"
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
                  itemsPerPageOptions: [50, 100, 500,1000],
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
    <v-row class="mt-5 mb-5">
      <v-col cols="6">
        <h3>{{ Module }}</h3>
        <div>Dashboard / {{ Module }}</div>
      </v-col>
      <v-col cols="6">
        <div class="text-right">
          <v-btn
            v-if="can(`employee_schedule_delete`)"
            small
            color="error"
            class="mr-2 mb-2"
            @click="delteteSelectedRecords"
            >Delete Selected Records</v-btn
          >

          <v-btn
            v-if="can(`employee_schedule_create`)"
            small
            color="primary"
            @click="dialog = true"
            class="mb-2"
          >
            {{ Module }} +</v-btn
          >
        </div>
      </v-col>
    </v-row>
    <!-- <v-row>
      <v-toolbar flat class="mb-5">
        <v-col md="8">
          <v-toolbar-title>{{ Module }}s List</v-toolbar-title>
        </v-col>
        <v-col>
          <v-text-field
            @input="searchIt"
            v-model="search"
            label="Search"
            single-line
            hide-details
            dense
            append-icon="mdi-magnify"
          ></v-text-field>
        </v-col>
      </v-toolbar>
    </v-row> -->

    <v-data-table
      v-if="can(`employee_schedule_list_view`)"
      v-model="ids"
      show-select
      item-key="id"
      :headers="headers"
      :items="employees"
      :server-items-length="total"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [50, 100, 500,1000],
      }"
      class="elevation-1"
    >
      <template v-slot:top> </template>
      <template v-slot:item.schedule="{ item }">
        <span v-if="item.schedule && item.schedule.shift">
          {{ item.schedule.shift.name }}
        </span>
        <span v-else>---</span>
      </template>
      <template v-slot:item.shift_type="{ item }">
        <span v-if="item.schedule && item.schedule.shift_type">
          {{ item.schedule.shift_type.name }}
        </span>
        <span v-else>---</span>
      </template>

      <template v-slot:item.schedule.isOverTime="{ item }">
        <v-icon
          v-if="item.schedule && item.schedule.isOverTime"
          color="success darken-1"
          >mdi-check</v-icon
        >
        <v-icon v-else color="error">mdi-close</v-icon>
      </template>
      <template v-slot:item.employee_ids="{ item }">
        <v-chip
          class="ma-1"
          small
          color="primary"
          v-for="(pa, idx) in item.employee_ids"
          :key="idx"
        >
          {{ pa.name }}
        </v-chip>
      </template>
      <template v-slot:item.action="{ item }">
        <v-icon
          v-if="can(`assign_permission_edit`)"
          color="secondary"
          small
          class="mr-2"
          @click="editItem(item)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          v-if="can(`assign_permission_delete`)"
          color="error"
          small
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
      <template v-slot:no-data>
        <!-- <v-btn color="primary" @click="initialize">Reset</v-btn> -->
      </template>
    </v-data-table>
    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    Module: "Employee Schedule",
    shift_types: [],
    manual_shift: {},
    options: {},
    options_dialog: {},
    endpoint: "scheduled_employees",
    endpoint_dialog: "not_scheduled_employees",
    search: "",
    shifts_for_filter: [],
    dialog_search: "",
    snackbar: false,
    dialog: false,

    loading: false,
    loading_dialog: false,
    total: 0,
    total_dialog: 0,
    headers: [
      {
        text: "E.ID",
        align: "left",
        sortable: false,
        value: "system_user_id",
      },
      {
        text: "First Name",
        align: "left",
        sortable: false,
        value: "first_name",
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

      { text: "Actions", align: "center", value: "action", sortable: false },
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

    headers_dialog: [
      {
        text: "E.ID",
        align: "left",
        sortable: false,
        value: "system_user_id",
      },
      {
        text: "First Name",
        sortable: false,
        value: "first_name",
      },
      // {
      //   text: "Profile Image",
      //   sortable: false,
      //   value: "profile_picture"
      // },
      {
        text: "Department",
        sortable: false,
        value: "department.name",
      },
      {
        text: "Sub Department",
        sortable: false,
        value: "sub_department.name",
      },
    ],
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
        this.getDataFromApi();
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
  },
  created() {
    this.loading = true;
    this.loading_dialog = true;

    this.options = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      },
    };

    this.getShifts(this.options);
  },

  methods: {
    editItem(item) {
      this.is_edit = true;
      this.total_dialog = 1;
      this.employees_dialog = [];
      this.employees_dialog.unshift(item);
      this.shift_type_id = item.schedule.shift_type.id;
      this.isOverTime = item.schedule.isOverTime;
      this.manual_shift = item.schedule.shift_type;
      this.shift_id = item.schedule.shift_id;
      this.getShifts(this.options);
      this.dialog = true;
      this.loading_dialog = true;
      setTimeout(() => {
        this.loading_dialog = false;
      }, 700);
    },

    runShiftTypeFunction() {
      this.manual_shift = this.shift_types.find(
        (e) => e.id == this.shift_type_id
      );
      this.getShifts(this.options);
    },
    close() {
      this.dialog = false;
      this.is_edit = false;
    },
    getShifts(options) {
      this.$axios
        .get("shift", options)
        .then(({ data }) => {
          this.shifts = data.data;
          this.shifts = data.data.filter((e) =>
            e.shift_type.slug == "manual_shift" ? e : false
          );
          this.shifts.unshift({ id: "", name: "Select Shift" });
          this.shifts_for_filter = data.data;
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
    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          page: page,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.employees = data.data;
        this.total = data.total;
        this.loading = false;
      });
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
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },

    dialogSearchIt(e) {
      if (e.length == 0) {
        this.getDataFromApiForDialog();
      } else if (e.length > 2) {
        this.employees_dialog = this.employees.filter(({ first_name: fn }) =>
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
          .delete("schedule_employees/" + item.system_user_id)
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
      let stId = this.shift_type_id;
      let s_filter = this.shifts_for_filter;
      let s_id = this.shift_id;
      let shiftId =
        stId == 3
          ? s_id
          : s_filter.find((e) => (e.shift_type_id == stId ? e : null));

      if (shiftId == undefined) {
        alert("Please create shift before schedule");
        return;
      }
      console.log(shiftId);
      this.errors = [];
      let payload = {
        shift_id: shiftId.id,
        shift_type_id: stId,
        isOverTime: this.isOverTime ? 1 : 0,
        employee_ids: this.employee_ids.map((e) => e.system_user_id),
        company_id: this.$auth.user.company.id,
      };

      let updatePayload = {
        shift_type_id: this.shift_type_id,
        shift_id: shiftId,
        company_id: this.$auth.user.company.id,
        isOverTime: this.isOverTime ? 1 : 0,
      };

      if (this.is_edit) {
        this.$axios
          .put(`schedule_employees/${payload.employee_ids}`, updatePayload)
          .then(({ data }) => {
            if (!data.status) {
              this.loading_dialog = false;
              return;
            }
            this.response = data.message;
            this.snackbar = true;
            this.loading_dialog = false;
            this.getDataFromApi();
            this.getDataFromApiForDialog();
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post("schedule_employees", payload)
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
            this.response = "Employees has been scheduled";
            this.snackbar = true;
            this.loading_dialog = false;
            this.getDataFromApi();
            this.getDataFromApiForDialog();
          })
          .catch((err) => console.log(err));
      }
    },
  },
};
</script>
