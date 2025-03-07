<template>
  <div v-if="can(`department_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div>
      <v-navigation-drawer
        v-model="dialogNew"
        bottom
        temporary
        right
        fixed
        style="width: 300px"
      >
        <v-card>
          <v-card-title dense class="popup_background">
            New Department
            <v-spacer></v-spacer>
            <v-icon @click="dialogNew = false" outlined dark>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text class="mt-4">
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-autocomplete
                    class="pb-0"
                    v-model="payload.branch_id"
                    :items="branchesList"
                    dense
                    placeholder="Select Branch"
                    outlined
                    item-value="id"
                    item-text="branch_name"
                    label="Branch"
                    :hide-details="!errors.branch_id"
                    :error-messages="
                      errors && errors.branch_id && errors.branch_id[0]
                    "
                  >
                  </v-autocomplete>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="payload.name"
                    label="Department Name"
                    outlined
                    dense
                    :hide-details="!errors.name"
                    :error-messages="errors && errors.name && errors.name[0]"
                  ></v-text-field>
                </v-col>

                <v-col cols="12" class="pb-0 mb-0"><b>Add Manager(s)</b></v-col>

                <v-col cols="12">
                  <v-row
                    no-gutters
                    v-for="(item, index) in payload.managers"
                    :key="index"
                  >
                    <v-col cols="12">
                      <v-text-field
                        class="my-2"
                        dense
                        outlined
                        v-model="item.name"
                        label="Manager Name"
                        :hide-details="!errors[`managers.${index}.name`]"
                        :error-messages="
                          errors &&
                          errors[`managers.${index}.name`] &&
                          errors[`managers.${index}.name`][0]
                        "
                      ></v-text-field>

                      <v-text-field
                        class="my-2"
                        dense
                        outlined
                        type="email"
                        v-model="item.email"
                        label="Email"
                        :hide-details="!errors[`managers.${index}.email`]"
                        :error-messages="
                          errors &&
                          errors[`managers.${index}.email`] &&
                          errors[`managers.${index}.email`][0]
                        "
                      ></v-text-field>

                      <v-text-field
                        class="mt-2"
                        dense
                        outlined
                        v-model="item.password"
                        label="Password"
                        :hide-details="!errors[`managers.${index}.password`]"
                        :error-messages="
                          errors &&
                          errors[`managers.${index}.password`] &&
                          errors[`managers.${index}.password`][0]
                        "
                      ></v-text-field>

                      <v-autocomplete
                        class="mt-2"
                        label="Role"
                        :items="roles"
                        item-text="name"
                        item-value="id"
                        placeholder="Select"
                        v-model="item.role_id"
                        :hide-details="!errors[`managers.${index}.role_id`]"
                        :error-messages="
                          errors &&
                          errors[`managers.${index}.role_id`] &&
                          errors[`managers.${index}.role_id`][0]
                        "
                        dense
                        outlined
                      ></v-autocomplete>
                    </v-col>
                    <v-col cols="12" class="mt-3">
                      <v-divider></v-divider>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12" class="text-right">
                  <v-icon @click="removeItem(index)" title="Delete"
                    >mdi-trash-can-outline</v-icon
                  >
                  <v-icon
                    v-if="payload && payload.managers"
                    title="Add - Maximum 3 managers"
                    :disabled="payload.managers.length >= 3"
                    @click="addItem"
                    >mdi-plus-circle-outline</v-icon
                  >
                </v-col>
                <v-card-actions>
                  <v-btn class="primary" @click="submit">Save</v-btn>
                </v-card-actions>
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-navigation-drawer>
      <v-row>
        <v-col md="12" lg="12">
          <!-- <Back color="primary" /> -->

          <v-card class="mb-5 mt-2 rounded-md" elevation="0">
            <v-toolbar class="rounded-md" dense flat>
              <v-toolbar-title
                ><span> {{ Model }} List</span></v-toolbar-title
              >
              <!-- 
                <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                dense
                class="ma-0 px-0"
                x-small
                :ripple="false"
                text
                title="Reload"
                @click="clearFilters()"
              >
                <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
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
                @click="toggleFilter"
              >
                <v-icon dark white>mdi-filter</v-icon>
              </v-btn>
              <!-- </template>
                  <span>Filter</span>
                </v-tooltip> -->

              <v-spacer></v-spacer>
              &nbsp;
              <v-btn
                v-if="can(`department_create`)"
                @click="newDepartment"
                small
                class="primary"
              >
                Department +
              </v-btn>
            </v-toolbar>
            <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
              {{ snackText }}

              <template v-slot:action="{ attrs }">
                <v-btn v-bind="attrs" text @click="snack = false">
                  Close
                </v-btn>
              </template>
            </v-snackbar>
            <v-data-table
              dense
              :headers="headers_table"
              :items="data"
              model-value="data.id"
              :loading="loading"
              :options.sync="options"
              :footer-props="{
                itemsPerPageOptions: [10, 50, 100, 500, 1000],
              }"
              class="elevation-0"
              :server-items-length="totalRowsCount"
            >
              <template v-slot:item.manager1="{ item }">
                {{
                  (item.managers &&
                    item.managers[0] &&
                    item.managers[0].name) ||
                  "---"
                }}
                <div class="secondary-value">
                  {{
                    (item.managers &&
                      item.managers[0] &&
                      item.managers[0].email) ||
                    "---"
                  }}
                  <br />
                  {{
                    (item.managers &&
                      item.managers[0] &&
                      item.managers[0].whatsapp_number) ||
                    "---"
                  }}
                </div>
              </template>
              <template v-slot:item.manager2="{ item }">
                {{
                  (item.managers &&
                    item.managers[1] &&
                    item.managers[1].name) ||
                  "---"
                }}
                <div class="secondary-value">
                  {{
                    (item.managers &&
                      item.managers[1] &&
                      item.managers[1].email) ||
                    "---"
                  }}
                  <br />
                  {{
                    (item.managers &&
                      item.managers[1] &&
                      item.managers[1].whatsapp_number) ||
                    "---"
                  }}
                </div>
              </template>
              <template v-slot:item.manager3="{ item }">
                {{
                  (item.managers &&
                    item.managers[2] &&
                    item.managers[2].name) ||
                  "---"
                }}
                <div class="secondary-value">
                  {{
                    (item.managers &&
                      item.managers[2] &&
                      item.managers[2].email) ||
                    "---"
                  }}
                  <br />
                  {{
                    (item.managers &&
                      item.managers[2] &&
                      item.managers[2].whatsapp_number) ||
                    "---"
                  }}
                </div>
              </template>

              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item
                      v-if="can(`department_edit`)"
                      @click="editItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can(`department_delete`)"
                      @click="deleteItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="error" small> mdi-delete </v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    payload: {
      branch_id: 0,
      name: "",
      managers: [{ name: "", email: "", password: "", role_id: 0 }],
    },
    branchesList: [],
    dialogNew: false,
    show1: false,
    dialogFormDesignation: false,
    showFilters: false,
    options: {},
    filters: {},
    isFilter: false,
    generateLogsDialog: false,
    totalRowsCount: 0,
    new_Designation_name: "",
    departments: [],

    New_sub_DepartmentName: "",
    Newdepartment_id: "",
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    dialogForm: false,
    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    Model: "Departments",
    options: {},
    endpoint: "departments",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,

    editedIndex: -1,
    editedItem: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
    defaultItem: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
    response: "",
    data: [],
    errors: [],
    headers_table: [
      {
        text: "Department",
        align: "left",
        value: "name",
      },
      {
        text: "Manager 1",
        align: "left",
        value: "manager1",
      },
      {
        text: "Manager 2",
        align: "left",
        value: "manager2",
      },
      {
        text: "Manager 3",
        align: "left",
        value: "manager3",
      },
      {
        text: "Last Updated Date",
        align: "left",
        value: "updated_at",
      },
      { text: "Options", align: "left", sortable: false, value: "options" },
    ],
    branchesList: [],
    roles: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    },
  },

  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },

    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
  },

  created() {
    this.loading = true;
    this.$axios
      .get(`branches_list`, {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
        },
      })
      .then(({ data }) => {
        this.branchesList = data;
        this.branch_id = this.$auth.user.branch_id || "";
      });

    this.$axios
      .get(`role`, {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
        },
      })
      .then(({ data }) => {
        this.roles = data.data;
      });

    this.headers_table.splice(1, 0, {
      text: "Branch",
      align: "left",
      sortable: true,
      key: "branch_id",
      value: "branch.branch_name",
      filterable: true,
      width: "10%",
      filterSpecial: true,
    });

    this.getDataFromApi();
  },

  methods: {
    addItem() {
      this.payload.managers.push({ name: "", email: "", password: "" });
    },
    removeItem(index) {
      this.payload.managers.splice(index, 1);
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
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    newDepartment() {
      this.editedIndex = -1;
      this.dialogNew = true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    onPageChange() {
      this.getDataFromApi();
    },
    applyFilters() {
      this.getDataFromApi();
      this.from_menu_filter = false;
      this.to_menu_filter = false;
    },
    toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};

      this.isFilter = false;
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      if (url == "") url = this.endpoint;
      this.loading = true;
      this.loading = true;

      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          //department_ids: this.$auth.user.assignedDepartments,

          ...this.filters,
        },
      };
      if (filter_column != "") {
        this.payloadOptions.params[filter_column] = filter_value;
      }
      this.$axios.get(url, this.payloadOptions).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.data = data.data;
        this.departments = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
        this.totalRowsCount = data.total;
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
      this.editedIndex = this.data.indexOf(item);
      this.payload = Object.assign({}, item);
      this.dialogNew = true;
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            const index = this.data.indexOf(item);
            this.data.splice(index, 1);
            this.snackbar = data.status;
            this.response = data.message;
          })
          .catch((err) => console.log(err));
    },

    close() {
      this.errors = [];
      //this.dialog = false;
      this.dialogNew = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    submit() {
      this.errors = [];
      this.editedIndex === -1 ? this.store() : this.update();
    },

    store() {
      this.payload.company_id = this.$auth.user.company_id;

      this.$axios
        .post(this.endpoint, this.payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = data.status;
            this.response = data.message;
            this.getDataFromApi();
            this.close();
          }
        })
        .catch((res) => console.log(res));
    },

    update() {
      this.$axios
        .put(`${this.endpoint}/${this.payload.id}`, this.payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = data.status;
            this.response = data.message;
            this.getDataFromApi();
            this.close();
          }
        })
        .catch((res) => console.log(res));
    },
  },
};
</script>
