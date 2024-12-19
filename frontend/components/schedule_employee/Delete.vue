<template>
  <v-dialog v-model="editDialog" :key="key" :width="500">
    <WidgetsClose left="490" @click="editDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on" style="max-width: 140px">
        <v-btn dense class="ma-2 px-1 primary" fill dark small>
          Delete Schedule
        </v-btn>
      </span>
    </template>
    <v-card>
      <v-alert dense class="grey lighten-3">
        <v-row no-gutters>
          <v-col>
            <div>Delete Schedule</div>
          </v-col>
        </v-row>
      </v-alert>

      <v-card-text>
        <v-row>
          <v-col md="12">
            <v-autocomplete
              label="Branch"
              @change="filterDepartmentsByBranch"
              cols="1"
              :hide-details="true"
              item-value="id"
              item-text="branch_name"
              v-model="filterPopupBranchId"
              outlined
              dense
              clearable
              :items="branchesList"
            ></v-autocomplete>
          </v-col>
          <v-col md="12">
            <v-autocomplete
              label="Departments"
              height="40px"
              class="announcement-dropdown1"
              outlined
              dense
              @change="employeesByDepartment"
              v-model="filterDepartmentIds"
              :items="departments"
              multiple
              item-text="name"
              item-value="id"
              placeholder="Departments"
              :hide-details="true"
            >
              <template v-if="departments.length" #prepend-item>
                <v-list-item @click="toggleDepartmentSelection">
                  <v-list-item-action>
                    <v-checkbox
                      @click="toggleDepartmentSelection"
                      v-model="selectAllDepartment"
                      :indeterminate="isIndeterminateDepartment"
                      :true-value="true"
                      :false-value="false"
                    ></v-checkbox>
                  </v-list-item-action>
                  <v-list-item-content>
                    <v-list-item-title>
                      {{ selectAllDepartment ? "Unselect All" : "Select All" }}
                    </v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </template>
              <template v-slot:selection="{ item, index }">
                <span v-if="index === 0 && filterDepartmentIds.length == 1">{{
                  item.name
                }}</span>
                <span
                  v-else-if="
                    index === 1 &&
                    filterDepartmentIds.length == departments.length
                  "
                  class=" "
                >
                  All Selected
                </span>
                <span v-else-if="index === 1" class=" ">
                  {{ filterDepartmentIds.length }} Seleted
                  <!-- Selected {{ filterDepartmentIds.length }} Department(s) -->
                </span>
              </template>
            </v-autocomplete>
          </v-col>
          <v-col md="12">
            <v-autocomplete
              label="Employees"
              height="40px"
              class="announcement-dropdown1"
              outlined
              dense
              v-model="filterEmployeeIds"
              :items="employees_dialog"
              multiple
              item-text="name_with_user_id"
              item-value="system_user_id"
              placeholder="Employees"
              :error-messages="
                errors && errors.employees ? errors.employees[0] : ''
              "
              color="background"
              :hide-details="true"
            >
              <template v-if="employees_dialog.length" #prepend-item>
                <v-list-item @click="toggleEmployeeSelection">
                  <v-list-item-action>
                    <v-checkbox
                      @click="toggleEmployeeSelection"
                      v-model="selectAllEmployee"
                      :indeterminate="isIndeterminateEmployee"
                      :true-value="true"
                      :false-value="false"
                    ></v-checkbox>
                  </v-list-item-action>
                  <v-list-item-content>
                    <v-list-item-title>
                      {{ selectAllEmployee ? "Unselect All" : "Select All" }}
                    </v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </template>
              <template v-slot:selection="{ item, index }">
                <span v-if="index === 0 && filterEmployeeIds.length == 1"
                  >{{ item.first_name }} {{ item.last_name }}
                  {{ item.schedules_count }}</span
                >
                <span
                  v-else-if="
                    index === 1 &&
                    filterEmployeeIds.length == employees_dialog.length
                  "
                  class=" "
                >
                  All Selected
                </span>
                <span v-else-if="index === 1" class=" ">
                  {{ filterEmployeeIds.length }} Seleted
                  <!-- Selected  Employee(s) -->
                </span>
              </template>
            </v-autocomplete>
          </v-col>
          <v-col>
            <v-btn
              :loading="loading"
              block
              dark
              small
              color="primary"
              @click="delteteSelectedRecords"
            >
              Submit
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  data: () => ({
    schedulePopupWidth: "800",
    key: 1,
    selectAllEmployee: false,
    selectAllDepartment: false,
    filterPopupBranchId: "",
    filterDepartmentIds: [],
    filterEmployeeIds: [],
    filterScheduledEmp: "",
    branchesList: [],
    branch_id: null,
    filters: {},
    snack: false,
    options: { perPage: 20 },
    snackbar: false,
    dialog: false,
    editDialog: false,

    loading: false,
    isEdit: false,
    total: 0,
    total_dialog: 0,
    department_ids: [],
    employees: [],
    employees_dialog: [],
    departments: [],
    sub_departments: [],
    ids: [],
    response: "",
    data: [],
    errors: [],
  }),

  computed: {
    isIndeterminateDepartment() {
      return (
        this.filterDepartmentIds.length > 0 &&
        this.filterDepartmentIds.length < this.departments.length
      );
    },
    isIndeterminateEmployee() {
      return (
        this.filterEmployeeIds.length > 0 &&
        this.filterEmployeeIds.length < this.employees_dialog.length
      );
    },
  },

  watch: {
    filterDepartmentIds(value) {
      this.filterEmployeeIds = [];
    },
    filterEmployeeIds(value) {},

    selectAllDepartment(value) {
      if (value) {
        this.filterDepartmentIds = this.departments.map((e) => e.id);
        this.employeesByDepartment();
      } else {
        this.filterDepartmentIds = [];

        this.employeesByDepartment();
      }
    },
    selectAllEmployee(value) {
      if (value) {
        this.filterEmployeeIds = this.employees_dialog.map(
          (e) => e.system_user_id
        );
      } else {
        this.filterEmployeeIds = [];
      }
    },
    dialog(val) {
      val || this.close();
    },
  },
  created() {
    const today = new Date();

    this.date_from = today.toISOString().slice(0, 10);
    this.date_to = today.toISOString().slice(0, 10);
    if (this.$auth.user.branch_id == null || this.$auth.user.branch_id == 0) {
      let branch_header = [
        {
          text: "Branch",
          align: "left",
          sortable: true,
          value: "branch_id",
          filterable: true,
          filterName: "branch_id",
          filterSpecial: true,
        },
      ];
    }
    this.options = {
      params: {
        per_page: 20,
        company_id: this.$auth.user.company_id,
      },
    };

    this.getbranchesList();
  },

  methods: {
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeeSelection() {
      this.selectAllEmployee = !this.selectAllEmployee;
    },
    getEmployees(url = "employee") {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.employees_dialog = data.data;
      });
    },
    filterDepartmentsByBranch() {
      this.selectAllDepartment = false;

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: this.filterPopupBranchId,
        },
      };
      this.getDepartments(options);
    },
    filterEmployees() {
      this.filters["schedules_count"] = this.filterScheduledEmp;
      this.filterEmployeeIds = [];
    },
    getbranchesList() {
      this.$axios.get(`branches_list`, this.payloadOptions).then(({ data }) => {
        this.branchesList = data;
      });
    },

    close() {
      this.editDialog = false;
      this.filterPopupBranchId = "";
      this.filterDepartmentIds = [];
      this.filterEmployeeIds = [];
      this.response = "";
    },

    getDepartments(options) {
      this.$axios
        .get("departments", options)
        .then(({ data }) => {
          this.departments = data.data;
        })
        .catch((err) => console.log(err));
    },

    employeesByDepartment() {
      let options = {
        params: {
          department_ids: this.filterDepartmentIds,
          per_page: 10000,
          page: 1,
          company_id: this.$auth.user.company_id,
        },
      };

      if (!this.filterDepartmentIds.length) {
        this.employees_dialog = [];
        this.total_dialog = 0;
        return;
      }

      this.$axios
        .get("employees_with_schedule_count", options)
        .then(({ data }) => {
          this.employees_dialog = data.data.filter(
            (e) => e.schedule_active.id != null
          );

          this.total_dialog = data.total;
        });
    },

    delteteSelectedRecords() {
      this.loading = true;
      let filteredScheduledIds = this.employees_dialog
        .filter((employee) =>
          this.filterEmployeeIds.includes(employee.system_user_id)
        )
        .flatMap((employee) =>
          employee.schedule_all.map((schedule) => schedule.id)
        );

      // filter data based on  this.filterEmployeeIds (array) from this.employees_dialog
      let payload = {
        ids: filteredScheduledIds,
      };

      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`schedule_employee/delete-all`, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
              this.loading = false;
            } else {
              this.$emit("response", "Schedule(s) has been deleted");
              this.close();
              this.loading = false;
            }
          })
          .catch((err) => {
            this.$emit("response", "Error Occured");
          });
    },
  },
};
</script>
