<template>
  <div v-if="can(`employee_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-5">
      <v-col cols="6" sm="6" md="6">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }}</div>
      </v-col>
    </v-row>
    <v-row>
      <v-col xs="12" sm="12" lg="3" cols="12">
        <v-select
          @change="getDataFromApi(`employee`)"
          v-model="pagination.per_page"
          :items="[50, 100, 500, 1000]"
          placeholder="Per Page Records"
          solo
          flat
        ></v-select>
      </v-col>
      <v-col xs="12" sm="12" lg="3" cols="12">
        <v-select
          @change="getDataFromApi(`employee`)"
          v-model="department_id"
          item-text="name"
          item-value="id"
          :items="departments"
          placeholder="Department"
          solo
          flat
        ></v-select>
      </v-col>
      <v-col xs="12" sm="12" lg="3" cols="12">
        <v-text-field
          class=""
          placeholder="Search..."
          solo
          flat
          @input="searchIt"
          v-model="search"
        ></v-text-field>
      </v-col>
    </v-row>

    <div v-if="can(`employee_view`)">
      <v-row>
        <v-col>
          <v-card class="mb-5" elevation="0">
            <v-toolbar color="background" dark flat>
              <v-toolbar-title>Salary Details</v-toolbar-title>
            </v-toolbar>
            <table class="employee-table">
              <tr>
                <th v-for="(item, index) in headers" :key="index">
                  {{ item.text }}
                </th>
              </tr>
              <v-progress-linear
                v-if="loading"
                :active="loading"
                :indeterminate="loading"
                absolute
                color="primary"
              ></v-progress-linear>
              <tr v-for="(item, index) in data" :key="index">
                <td class="text-center">
                  <b>{{ ++index }}</b>
                </td>
                <td>{{ item.system_user_id || "---" }}</td>
                <td>{{ item.first_name || "---" }}</td>
                <td>{{ caps(item && item.designation.name) }}</td>
                <td>{{ caps(item.department.name) }}</td>
                <td>{{ item.payroll && item.payroll.basic_salary }}</td>
                <td>{{ item.payroll && item.payroll.net_salary }}</td>
                <td>
                  <v-btn
                    :to="`/payroll/salary/${item.system_user_id}_${item.id}`"
                    class="background white--text"
                    small
                    >Generate Slip</v-btn
                  >
                </td>
                <td>
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="editItem(item)">
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small> mdi-pencil </v-icon>
                          Edit
                        </v-list-item-title>
                      </v-list-item>
                      <!-- <v-list-item @click="res(item.id)">
                        <v-list-item-title style="cursor:pointer">
                          <v-icon color="primary" small>
                            mdi-eye
                          </v-icon>
                          Select
                        </v-list-item-title>
                      </v-list-item> -->
                    </v-list>
                  </v-menu>
                </td>
              </tr>
            </table>
          </v-card>
          <v-row>
            <v-col lg="12" class="float-right">
              <div class="float-right">
                <v-pagination
                  v-model="pagination.current"
                  :length="pagination.total"
                  @input="onPageChange"
                  :total-visible="5"
                ></v-pagination>
              </div>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
      <div></div>
    </div>
    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    tab: null,
    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    options: {},
    Model: "Payroll",
    endpoint: "employee",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    paymentMethod: ["Bank Transfer", "Cash", "Cheque"],
    Allowance: [
      "Transport",
      "Travel",
      "Entertainment",
      "Housing",
      "Uniform",
      "Uniform",
      "Medical/health",
    ],
    headers: [
      { text: "#" },
      { text: "EID" },
      { text: "Name" },
      { text: "Designation" },
      { text: "Department" },
      { text: "Basic Salary" },
      { text: "Net Salary" },
      { text: "Payslip" },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    departments: [],
    department_id: "",
    work: {
      first_name: "",
      last_name: "",
      department: "",
      sub_department: "",
      designation: "",
      role: "",
      employee_id: "",
      system_user_id: "",
      user: "",
      profile_picture: "",
      phone_number: "",
      whatsapp_number: "",
      joining_date: "",
    },
    BankInfo: {
      bank_name: "",
      account_no: "",
      account_title: "",
      iban: "",
      address: "",
      remark: "",
      company_id: "",
      employee_id: "",
    },
    salary: {
      basic_salary: "",
      payment_method: "",
      remark: "",
    },
    allowance: {
      name: "",
      amount: "",
      remark: "",
    },
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
    department_id() {
      this.pagination.current = 1;
      this.getDataFromApi();
    },
  },
  created() {
    this.loading = true;
    this.getDepartments();
  },
  mounted() {
    this.getDataFromApi();
  },

  methods: {
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
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    res(id) {
      this.$axios.get(`employee/${id}`).then(({ data }) => {
        this.work = { ...data };
        this.getBankInfo(data.employee_id);
      });
    },
    getBankInfo(id) {
      this.$axios.get(`bankinfo/${id}`).then(({ data }) => {
        this.BankInfo = {
          ...data,
        };
      });
    },

    getDepartments() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
        this.departments.unshift({ name: "All", id: "" });
      });
    },
    getDataFromApi(url = this.endpoint) {
      this.loading = true;
      let page = this.pagination.current;
      let department_id = this.department_id;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          department_id: department_id,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },

    editItem(item) {
      this.$router.push(`/employees/${item.id}`);
    },

    delteteSelectedRecords() {
      let just_ids = this.ids.map((e) => e.id);
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: just_ids,
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
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
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      let salary = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, salary)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              const index = this.data.findIndex(
                (item) => item.id == this.editedItem.id
              );
              this.data.splice(index, 1, {
                id: this.editedItem.id,
                name: this.editedItem.name,
              });
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, salary)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((res) => console.log(res));
      }
    },
  },
};
</script>

<style scoped>
table.employee-table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  /* border: 1px solid #dddddd; */
  text-align: left;
  padding: 8px;
}

table.employee-table tr:nth-child(even) {
  background-color: #e9e9e9;
}
</style>
