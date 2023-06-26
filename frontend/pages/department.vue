<template>
  <div v-if="can(`department_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="can(`employee_view`)">
      <v-row>
        <v-dialog
          v-model="dialogFormDesignation"
          :fullscreen="false"
          width="500px"
        >
          <v-card elevation="0">
            <v-toolbar color="background" dense flat dark>
              <span>New Designation</span>
            </v-toolbar>
            <v-divider class="py-0 my-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row class="mt-2">
                  <v-col cols="12">
                    <v-text-field
                      v-model="new_Designation_name"
                      placeholder="Designation"
                      outlined
                      dense
                    ></v-text-field>
                    <span v-if="errors && errors.name" class="error--text">{{
                      errors.name[0]
                    }}</span>
                  </v-col>
                  <v-col cols="12">
                    <v-autocomplete
                      v-model="new_designation_department_id"
                      :items="departments"
                      item-text="name"
                      item-value="id"
                      placeholder="Select Departments"
                      outlined
                      dense
                    >
                    </v-autocomplete>
                    <span
                      v-if="errors && errors.department_id"
                      class="error--text"
                      >{{ errors.department_id[0] }}</span
                    >
                  </v-col>
                  <v-card-actions>
                    <v-col md="6" lg="6" style="padding: 0px">
                      <v-btn class="error" @click="close">
                        Cancel
                      </v-btn></v-col
                    >
                    <v-col
                      md="6"
                      lg="6"
                      class="text-right"
                      style="padding: 0px"
                    >
                      <v-btn class="primary" @click="savenewDesignation"
                        >Save</v-btn
                      >
                    </v-col>
                  </v-card-actions>
                </v-row>
              </v-container>
            </v-card-text>
          </v-card>
        </v-dialog>
        <v-dialog
          v-model="dialogFormSubdepartment"
          :fullscreen="false"
          width="500px"
        >
          <v-card elevation="0">
            <v-toolbar color="background" dense flat dark>
              <span>New Sub Department </span>
            </v-toolbar>
            <v-divider class="py-0 my-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row class="mt-2">
                  <v-col cols="12">
                    <v-text-field
                      v-model="New_sub_DepartmentName"
                      placeholder="Sub Department"
                      outlined
                      dense
                    ></v-text-field>
                    <span v-if="errors && errors.name" class="error--text">{{
                      errors.name[0]
                    }}</span>
                  </v-col>
                  <v-col cols="12">
                    <v-autocomplete
                      v-model="Newdepartment_id"
                      :items="departments"
                      item-text="name"
                      item-value="id"
                      placeholder="Select Departments"
                      outlined
                      dense
                    >
                    </v-autocomplete>
                    <span
                      v-if="errors && errors.department_id"
                      class="error--text"
                      >{{ errors.department_id[0] }}</span
                    >
                  </v-col>

                  <v-card-actions>
                    <v-col md="6" lg="6" style="padding: 0px">
                      <v-btn class="error" @click="close">
                        Cancel
                      </v-btn></v-col
                    >
                    <v-col
                      md="6"
                      lg="6"
                      class="text-right"
                      style="padding: 0px"
                    >
                      <v-btn class="primary" @click="saveSubDepartment"
                        >Save</v-btn
                      >
                    </v-col>
                  </v-card-actions>
                </v-row>
              </v-container>
            </v-card-text>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogForm" :fullscreen="false" width="500px">
          <v-card elevation="0">
            <v-toolbar color="background" dense flat dark>
              <span>{{ formTitle }} {{ Model }}</span>
            </v-toolbar>
            <v-divider class="py-0 my-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row class="">
                  <v-col md="12" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >Department Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.name"
                      type="text"
                      v-model="editedItem.name"
                      :error="errors.name"
                      :error-messages="
                        errors && errors.name ? errors.name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="12" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >Email<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.email"
                      type="text"
                      v-model="editedItem.email"
                      :error="errors.email"
                      :error-messages="
                        errors && errors.email ? errors.email[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="12" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >Password<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.password"
                      type="text"
                      v-model="editedItem.password"
                      :error="errors.password"
                      :error-messages="
                        errors && errors.password ? errors.password[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-col md="12" sm="12" cols="12" class="pa-0 text-right">
                      <v-btn small dark class="background" @click="close">
                        Cancel
                      </v-btn>
                      <v-btn small class="primary" @click="save">Save</v-btn>
                    </v-col>
                  </v-card-actions>
                </v-row>
              </v-container>
            </v-card-text>
          </v-card>
        </v-dialog>
        <v-col md="12" lg="12">
          <v-card class="mb-5 rounded-md" elevation="0">
            <v-toolbar class="rounded-md" color="background" dense flat dark>
              <v-toolbar-title
                ><span> {{ Model }} List</span></v-toolbar-title
              >
              <a
                style="padding-left: 10px"
                title="Reload Page/Reset Form"
                @click="getDataFromApi()"
                ><v-icon class="mx-1">mdi mdi-reload</v-icon></a
              >
              <v-spacer></v-spacer>
              <v-btn @click="newDesignation" small class="primary">
                Designation +
              </v-btn>
              &nbsp;
              <v-btn @click="newSubDepartment" small class="primary">
                Sub Department +
              </v-btn>
              &nbsp;
              <v-btn @click="newItem" small class="primary">
                {{ Model }} +
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
              :footer-props="{
                itemsPerPageOptions: [10, 50, 100, 500, 1000],
              }"
              class="elevation-1"
            >
              <template v-slot:item.sno="{ item, index }">
                {{ ++index }}
              </template>
              <template v-slot:item.id="{ item }">
                <v-edit-dialog
                  large
                  save-text="Reset"
                  cancel-text="Ok"
                  style="margin-left: 4%"
                  @save="getDataFromApi()"
                  @open="datatable_open"
                >
                  {{ caps(item.id) }}
                  <template v-slot:input>
                    <v-text-field
                      @input="
                        getDataFromApi('', 'serach_department_id', $event)
                      "
                      v-model="datatable_search_textbox"
                      label="Search Department Code"
                    ></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.name="{ item }">
                <v-edit-dialog
                  large
                  save-text="Reset"
                  cancel-text="Ok"
                  style="margin-left: 4%"
                  @save="getDataFromApi()"
                  @open="datatable_open"
                >
                  {{ caps(item.name) }}
                  <template v-slot:input>
                    <v-text-field
                      @input="
                        getDataFromApi('', 'serach_department_name', $event)
                      "
                      v-model="datatable_search_textbox"
                      label="Search Department name"
                    ></v-text-field>
                  </template>
                </v-edit-dialog>
              </template>
              <template v-slot:item.sub_dep.name="{ item }">
                <v-edit-dialog
                  large
                  save-text="Reset"
                  cancel-text="Ok"
                  style="margin-left: 4%"
                  @save="getDataFromApi()"
                  @open="datatable_open"
                >
                  <span v-if="item.children.length > 0">
                    <v-chip
                      small
                      class="primary ma-1"
                      v-for="(sub_dep, index) in item.children.slice(0, 3)"
                      :key="index"
                    >
                      {{ caps(sub_dep.name) }}
                    </v-chip>
                  </span>
                  <p v-else>---</p>
                  <template v-slot:input>
                    <v-text-field
                      @input="
                        getDataFromApi('', 'serach_sub_department_name', $event)
                      "
                      v-model="datatable_search_textbox"
                      label="Search Sub Department name"
                    ></v-text-field>
                  </template>
                </v-edit-dialog>
                <v-chip
                  small
                  class="primary ma-1"
                  style="color: black"
                  @click="gotoSubdepartments(item)"
                  v-if="item.children.length > 3"
                >
                  View all..
                </v-chip>
              </template>
              <template v-slot:item.designations="{ item }">
                <v-edit-dialog
                  large
                  save-text="Reset"
                  cancel-text="Ok"
                  style="margin-left: 4%"
                  @save="getDataFromApi()"
                  @open="datatable_open"
                >
                  <span v-if="item.designations.length > 0">
                    <v-chip
                      small
                      class="primary ma-1"
                      v-for="(designation, index) in item.designations.slice(
                        0,
                        3
                      )"
                      :key="index"
                    >
                      {{ caps(designation.name) }}
                    </v-chip>
                  </span>
                  <p v-else>---</p>
                  <template v-slot:input>
                    <v-text-field
                      @input="
                        getDataFromApi('', 'serach_designation_name', $event)
                      "
                      v-model="datatable_search_textbox"
                      label="Search   Designation name"
                    ></v-text-field>
                  </template>
                </v-edit-dialog>
                <v-chip
                  small
                  class="primary ma-1"
                  style="color: black"
                  to="/designation"
                  v-if="item.designations.length > 3"
                >
                  View all
                </v-chip>
              </template>
              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item @click="gotoSubdepartments(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="primary" small> mdi-view-list </v-icon>
                        View
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item @click="editItem(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="deleteItem(item)">
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
      <div>
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
    </div>

    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    dialogFormDesignation: false,

    new_Designation_name: "",
    new_designation_department_id: "",
    departments: [],

    New_sub_DepartmentName: "",
    Newdepartment_id: "",
    dialogFormSubdepartment: false,
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
      { text: "#", align: "left", sortable: true, value: "sno", width: "50px" },
      {
        text: "Department Code",
        align: "left",
        sortable: true,
        value: "id",
        width: "150px",
      },
      { text: "Department", align: "left", sortable: true, value: "name" },
      {
        text: "Sub Department",
        align: "left",
        sortable: true,
        value: "sub_dep.name",
      },
      {
        text: "Designations",
        align: "left",
        sortable: true,
        value: "designations",
      },

      { text: "Options", align: "left", sortable: true, value: "options" },
    ],
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
  },

  created() {
    this.loading = true;
    this.getDataFromApi();
  },

  methods: {
    datatable_save() {},
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
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    newItem() {
      this.dialogForm = true;
    },
    newSubDepartment() {
      this.dialogFormSubdepartment = true;
    },
    newDesignation() {
      this.dialogFormDesignation = true;
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
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      if (url == "") url = this.endpoint;
      this.loading = true;
      this.loading = true;
      let page = this.pagination.current;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id,
        },
      };
      if (filter_column != "") {
        options.params[filter_column] = filter_value;
      }
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
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
      this.editedItem = Object.assign({}, item);
      //this.dialog = true;
      this.dialogForm = true;
    },
    gotoSubdepartments(item) {
      this.$router.push(`/sub-department?id=${item.id}`);
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
          .then((res) => {
            if (!res.data.status) {
              this.errors = res.data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = res.data.status;
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
            const index = this.data.indexOf(item);
            this.data.splice(index, 1);
            this.snackbar = data.status;
            this.response = data.message;
          })
          .catch((err) => console.log(err));
    },

    close() {
      //this.dialog = false;
      this.dialogForm = false;
      this.dialogFormSubdepartment = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    savenewDesignation() {
      let payload = {
        name: this.new_Designation_name.toLowerCase(),
        department_id: this.new_designation_department_id,
        company_id: this.$auth.user.company.id,
      };

      this.$axios
        .post("designation", payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.getDataFromApi();
            this.snackbar = data.status;
            this.response = data.message;
            this.dialogForm = false;
            this.dialogFormDesignation = false;
            this.close();
            this.errors = [];
            this.search = "";
            this.new_Designation_name = "";
            this.new_designation_department_id = "";
          }
        })
        .catch((res) => console.log(res));
    },
    saveSubDepartment() {
      let payload = {
        name: this.New_sub_DepartmentName.toLowerCase(),
        department_id: this.Newdepartment_id,
        company_id: this.$auth.user.company.id,
      };

      this.$axios
        .post("sub-departments", payload)
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
            this.New_sub_DepartmentName = "";
            this.Newdepartment_id = "";
            his.dialogFormSubdepartment = false;
          }
        })
        .catch((res) => console.log(res));
    },
    save() {
      let payload = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company.id,
        email: this.editedItem.email,
        password: this.editedItem.email,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              const index = this.data.findIndex(
                (item) => item.id == this.editedItem.id
              );
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.dialogForm = false;
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, payload)
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
              this.dialogForm = false;
            }
          })
          .catch((res) => console.log(res));
      }
    },
  },
};
</script>
