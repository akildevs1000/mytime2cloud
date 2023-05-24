<template>
  <div v-if="can(`employee_schedule_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialog" width="900">
      <v-card>
        <v-card-title class="text-h5">
          Sync Device(s)
          <v-spacer></v-spacer>
          <v-btn class="primary" small fab @click="addRow(rosterFirstValue)">
            <b>+</b>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>
        <!-- {{ schedules_temp_list }} <br /> -->
        <!-- {{ rosters }} -->
        <v-data-table
          v-model="device_ids"
          show-select
          item-key="id"
          :headers="headerdevices_dialog"
          :items="devices_dialog"
          :server-items-length="total_dialog"
          :footer-props="{
            itemsPerPageOptions: [50, 100, 500, 1000],
          }"
        >
        </v-data-table>

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
          <v-btn small fab color="background" dark to="/employee_schedule">
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-card class="mb-5 rounded-md mt-3" elevation="0">
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
                  @change="runMultipleFunctions"
                  v-model="department_ids"
                  multiple
                  x-small
                  :items="sub_departments"
                  item-value="id"
                  item-text="name"
                  :disabled="is_edit == true ? true : false"
                ></v-autocomplete>

                <div class="mb-1">Search Employee</div>
                <v-text-field
                  outlined
                  @input="dialogSearchIt"
                  dense
                  v-model="employee_search"
                  append-icon="mdi-magnify"
                  single-line
                  hide-details
                ></v-text-field>
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
                  <!-- <v-text-field
                    @input="dialogSearchIt"
                    dense
                    v-model="dialog_search"
                    append-icon="mdi-magnify"
                    single-line
                    hide-details
                  ></v-text-field> -->
                  <v-btn dark small color="primary" @click="arrangeShift">
                    Sync Device(s)
                  </v-btn>
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
                itemsPerPageOptions: [50, 100, 500, 1000],
              }"
            >
              <template v-slot:item.imgUrl="{ item }">
                <!-- <span>{{ item.profile_picture }}</span> -->

                <v-img
                  style="
                    border-radius: 50%;
                    height: 50px;
                    width: 50px !important;
                  "
                  :src="item.profile_picture || '/no-profile-image.jpg'"
                ></v-img>
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    from_date: null,
    from_menu: false,

    from_menu: [],
    to_menu: [],

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },

    Module: "Employee Photo Upload",
    schedules_temp_list: [
      // {
      //   schedule_id: 1,
      //   from_date: new Date().toJSON().slice(0, 10),
      //   to_date: new Date().toJSON().slice(0, 10),
      //   is_over_time: false,
      // },
    ],
    options: {},
    options_dialog: {},
    endpoint: "scheduled_employees",
    endpoint_dialog: "scheduled_employees_list",
    search: "",
    dialog_search: "",
    snackbar: false,
    dialog: false,
    employee_search: "",
    loading: false,
    loading_dialog: false,
    total: 0,
    total_dialog: 0,

    department_ids: ["---"],
    sub_department_ids: ["---"],
    employee_ids: [],
    device_ids: [],
    payload: {
      schedule_id: [1],
      from_date: [new Date().toJSON().slice(0, 10)],
      to_date: [new Date().toJSON().slice(0, 10)],
      is_over_time: [false],
    },
    isOverTime: false,
    is_edit: false,
    employees: [],
    employees_dialog: [],
    devices_dialog: [],
    departments: [],
    sub_departments: [],
    shifts: [
      {
        id: 1,
        name: "Week 1",
      },
      {
        id: 2,
        name: "Week 2",
      },
    ],
    ids: [],
    response: "",
    data: [],
    rosters: [],
    rosterFirstValue: "",
    max_date: [],
    min_date: [],
    errors: [],
    headers_ids: [],

    headers_dialog: [
      {
        text: "E.ID",
        align: "left",
        sortable: true,
        value: "system_user_id",
      },
      {
        text: "Name",
        sortable: true,
        value: "display_name",
      },
      {
        text: "Department",
        sortable: true,
        value: "department.name",
      },
      {
        text: "Photo/Pic",
        sortable: true,
        value: "imgUrl",
      },
    ],
    headerdevices_dialog: [
      {
        text: "Device Id",
        align: "left",
        sortable: true,
        value: "device_id",
      },
      {
        text: "Name",
        sortable: true,
        value: "name",
      },
      {
        text: "Location",
        sortable: true,
        value: "location",
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
        this.getDataFromApi();
      }
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
    options_dialog: {
      handler() {
        this.runMultipleFunctions();
        if (!this.is_edit) {
          this.getDataFromApi();
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
    // this.loading_dialog = true;
    this.get_rosters();
    this.options = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      },
    };

    this.getDepartments(this.options);
    // this.getDataFromApi();

    this.getDevicesfromApi();
  },

  methods: {
    getDevicesfromApi() {
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          cols: ["id", "location", "name", "device_id"],
        },
      };
      this.$axios.get("device", options).then(({ data }) => {
        this.devices_dialog = data.data;
      });
    },
    arrangeShift() {
      if (!this.employee_ids.length) {
        alert("Atleast one employee must be selected.");
        return;
      }
      this.dialog = true;
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
    removeItem(i) {
      this.schedules_temp_list.splice(i, 1);
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

    set_date_save(from_menu, from, index) {
      from_menu.save(from);
      return;
      let toDate = this.setSevenDays(from, index);
      this.schedules_temp_list[index].to_date = toDate;
      console.log(this.schedules_temp_list);
    },

    set_to_date_save(from_menu, from, index) {
      from_menu.save(from);
      let toDate = this.setSevenDays(from);
      this.schedules_temp_list[index].to_date = toDate;
      console.log(this.schedules_temp_list);
    },

    setSevenDays(selected_date, index) {
      const date = new Date(selected_date);
      date.setDate(date.getDate() + 6);
      let datetime = new Date(date);
      let d = datetime.getDate();
      d = d < "10" ? "0" + d : d;
      let m = datetime.getMonth() + 1;
      m = m < 10 ? "0" + m : m;
      let y = datetime.getFullYear();
      this.max_date[index] = `${y}-${m}-${d}`;
      this.min_date[index] = `${y}-${m}-${d}`;
      console.log(this.max_date);
      return `${y}-${m}-${d}`;
    },

    get_rosters() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get("roster_list", { params: options }).then(({ data }) => {
        this.rosters = data;
        this.addRow(data[0].schedule_id);
        this.rosterFirstValue = data[0].schedule_id;
        console.log(this.rosterFirstValue);
        console.log(this.rosters);
      });
    },

    close() {
      this.dialog = false;
      this.is_edit = false;
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
          search: this.employee_search,
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

    dialogSearchIt(e) {
      this.employeesByDepartment();
      this.getEmployeesBySubDepartment();
    },

    // dialogSearchIt(e) {
    //   if (e.length == 0) {
    //     this.employeesByDepartment();
    //   } else if (e.length > 2) {
    //     this.employees_dialog = this.employees.filter(({ display_name: fn }) =>
    //       fn.includes(e)
    //     );
    //   }
    // },

    getEmployeesBySubDepartment() {
      this.loading_dialog = true;

      const { page, itemsPerPage } = this.options_dialog;

      let options = {
        params: {
          department_ids: this.department_ids,
          sub_department_ids: this.sub_department_ids,
          per_page: itemsPerPage,
          search: this.employee_search,
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
    //main
    getDataFromApi(url = this.endpoint) {
      this.loading = false;

      let page = this.pagination.current;

      let options = {
        params: {
          per_page: this.pagination.per_page,
          page: page,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.employees = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },
    // getDataFromApi(url = this.endpoint_dialog) {
    //   this.loading_dialog = true;

    //   const { page, itemsPerPage } = this.options_dialog;

    //   let options = {
    //     params: {
    //       per_page: itemsPerPage,
    //       page: page,
    //       company_id: this.$auth.user.company.id,
    //     },
    //   };

    //   this.$axios.get(url, options).then(({ data }) => {
    //     this.employees_dialog = data.data;
    //     this.total_dialog = data.total;
    //     this.loading_dialog = false;
    //   });
    // },
    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
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
            this.getDataFromApi();
          })
          .catch((err) => console.log(err));
    },

    save() {
      this.loading_dialog = true;
      this.errors = [];

      var personListArray = [];

      this.employee_ids.forEach((item) => {
        let person = {
          name: item.display_name,
          userCode: parseInt(item.system_user_id),
          expiry: "2089-12-31 23:59:59",
          timeGroup: 1,
          faceImage: item.profile_picture,
        };
        personListArray.push(person);
      });

      let payload = {
        personList: personListArray,
        snList: this.device_ids.map((e) => e.device_id),
      };

      console.log(payload);

      return;
      if (this.is_edit) {
        this.process(
          this.$axios.post(
            `schedule_employees/${payload.employee_ids}`,
            payload
          )
        );
      } else {
        this.process(this.$axios.post(`store_schedule_arrange`, payload));
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
          this.dialog = false;
          this.response = data.message;
          this.snackbar = true;
          this.loading_dialog = false;
          this.getDataFromApi();
        })
        .catch((err) => console.log(err));
    },
  },
};
</script>

<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  text-align: left;
  padding: 7px;
}

tr:nth-child(even) {
  background-color: #e9e9e9;
}

.custom-text-box {
  border-radius: 2px !important;
  border: 1px solid #dbdddf !important;
}
input[type="text"]:focus.custom-text-box {
  border: 2px solid #5fafa3 !important;
}

select.custom-text-box {
  border: 2px solid #5fafa3 !important;
}

select:focus {
  outline: none !important;
  border-color: #5fafa3;
  box-shadow: 0 0 0px #5fafa3;
}
</style>
