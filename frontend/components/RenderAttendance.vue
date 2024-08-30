<template>
  <div>
    <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
      {{ response }}
    </v-snackbar>
    <v-card-text>
      <v-container>
        <v-row>
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
            style="width: 100%"
          >
            <v-row>
              <v-col cols="3">
                <v-autocomplete
                  label="Branch"
                  @change="getDepartments"
                  placeholder="Branch"
                  outlined
                  dense
                  v-model="branch_id"
                  x-small
                  clearable
                  :items="[
                    { id: null, branch_name: 'All Branches' },
                    ...branches,
                  ]"
                  item-value="id"
                  item-text="branch_name"
                  :hide-details="true"
                ></v-autocomplete>
              </v-col>
              <v-col cols="3">
                <v-autocomplete
                  @change="getEmployees"
                  label="Departments"
                  outlined
                  dense
                  v-model="department_id"
                  x-small
                  clearable
                  :items="departments"
                  item-text="name"
                  item-value="id"
                  :hide-details="true"
                ></v-autocomplete>
              </v-col>
              <v-col cols="3">
                <v-autocomplete
                  placeholder="Employee Device Id"
                  v-model="editItems.UserIDs"
                  :items="employees"
                  :item-text="`name_with_user_id`"
                  item-value="system_user_id"
                  dense
                  outlined
                  multiple
                >
                  <template
                    v-if="employees.length && !system_user_id"
                    #prepend-item
                  >
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
                          {{
                            selectAllEmployee ? "Unselect All" : "Select All"
                          }}
                        </v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </template>
                  <template v-slot:selection="{ item, index }">
                    <span v-if="index === 0 && editItems.UserIDs.length == 1">{{
                      item.name
                    }}</span>
                    <span
                      v-else-if="
                        index === 1 &&
                        editItems.UserIDs.length == employees.length
                      "
                      class=" "
                    >
                      All Selected
                    </span>
                    <span v-else-if="index === 1" class=" ">
                      Selected {{ editItems.UserIDs.length }} Employee(s)
                    </span>
                  </template>
                </v-autocomplete>
              </v-col>
              <v-col cols="3">
                <DateRangePickerCommon @selected-dates="handleDatesFilter" />
              </v-col>

              <v-col cols="12">
                <v-card outlined>
                  <ul style="height: 150px; overflow-y: scroll">
                    <li v-for="(item, index) in result" :key="index">
                      <div>{{ item }}</div>
                    </li>
                  </ul>
                </v-card>
              </v-col>
            </v-row>
          </v-form>
        </v-row>
      </v-container>
    </v-card-text>
    <v-card-actions>
      <v-spacer></v-spacer>
      <span class="mx-2"> </span>

      <v-btn
        :loading="loading"
        class="primary"
        small
        @click="renderByType(endpoint)"
        >Click to Re-generate Attendance</v-btn
      >
      <!-- <v-btn class="background" dark small @click="renderByType(`render_off`)">
        Week Off</v-btn
      >
      <v-btn class="error" small @click="renderByType(`render_absent`)"
        >Absent</v-btn
      > -->
    </v-card-actions>
  </div>
</template>

<script>
import DateRangePickerCommon from "./Snippets/DateRangePickerCommon.vue";

export default {
  props: ["endpoint", "shift_type_id", "system_user_id"],
  data: () => ({
    valid: false,
    snackbar: false,
    loading: false,
    response: null,
    selectAllEmployee: false,

    result: [],
    editItems: {
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      UserIDs: [],
      dates: [],
      time: null,
    },
    employees: [],
    branch_id: 0,
    department_id: 0,
    branches: [],
    departments: [],
    isCompany: true,
  }),
  computed: {
    isIndeterminateEmployee() {
      return (
        this.editItems.UserIDs.length > 0 &&
        this.editItems.UserIDs.length < this.employees.length
      );
    },
  },
  watch: {
    selectAllEmployee(value) {
      if (value) {
        this.editItems.UserIDs = this.employees.map((e) => e.system_user_id);
      } else {
        this.editItems.UserIDs = [];
      }
    },
  },
  async created() {
    this.getBranches();
  },
  methods: {
    getBranches() {
      if (this.$auth.user.branch_id) {
        this.branch_id = this.$auth.user.branch_id;

        this.isCompany = false;
        return;
      }

      this.$axios
        .get("branch", {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.branches = data.data;
        });
    },

    async getDepartments() {
      let config = {
        params: {
          branch_id: this.branch_id,
          company_id: this.$auth.user.company_id,
        },
      };
      try {
        const { data } = await this.$axios.get(`department-list`, config);
        this.departments = data;
      } catch (error) {
        console.error("Error fetching departments:", error);
      }
    },

    async getEmployees() {
      let config = {
        params: {
          order_by: "name",
          company_id: this.$auth.user.company_id,
          branch_id: this.branch_id,
          department_id: this.department_id,
        },
      };

      try {
        const { data } = await this.$axios.get(`employee-list`, config);
        this.employees = data;

        if (this.system_user_id) {
          const filteredEmployees = this.employees.filter(
            (e) => e.system_user_id == this.system_user_id
          );

          this.employees = filteredEmployees;
        }
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    },

    toggleEmployeeSelection() {
      this.selectAllEmployee = !this.selectAllEmployee;
    },
    handleDatesFilter(dates) {
      this.editItems.dates = dates;
    },

    async renderByType(type) {
      const delay = (ms) => new Promise((res) => setTimeout(res, ms));
      const { UserID, date, reason, UserIDs, dates } = this.editItems;
      if (!UserIDs.length || !dates.length) {
        alert("System User Id and Date field is required");
        return;
      }
      this.result = ["Processing..."];
      let renderProcessingStatus = false;
      this.loading = true;

      const chunkSize = 10;

      for (let i = 0; i < UserIDs.length; i += chunkSize) {
        const UserIDs_chunk = UserIDs.slice(i, i + chunkSize);
        // do whatever

        let payload = {
          params: {
            date,
            UserID,
            updated_by: this.$auth.user.id,
            company_ids: [this.$auth.user.company_id],
            manual_entry: true,
            reason,
            employee_ids: UserIDs_chunk,
            dates,
            shift_type_id: this.shift_type_id,
          },
        };

        // return;
        let endpoint = "/" + type;
        if (type != "render_off" && type != "render_absent") {
          endpoint = "render_logs";
        }
        await this.$axios
          .get(endpoint, payload)
          .then(({ data }) => {
            //this.loading = false;

            if (endpoint !== "render_logs") {
              this.result = [payload];
              return;
            }
            renderProcessingStatus = true;
            this.result = data;
          })
          .catch((e) => console.log(e));

        await delay(1000 * 2);
        renderProcessingStatus = true;
      } //for
      this.loading = false;
      this.$emit("update-data-table");
      // if (!renderProcessingStatus) {
      //   this.$emit("update-data-table");
      // }
    },
  },
  components: { DateRangePickerCommon },
};
</script>
