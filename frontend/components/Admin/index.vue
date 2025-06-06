<template>
  <div v-if="can(`admin_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div>
      <v-dialog persistent v-model="AdminDialog" width="400px">
        <WidgetsClose @click="close" left="390" />
        <v-card>
          <v-alert dense flat class="grey lighten-3">
            {{ formTitle }} {{ Model }}
          </v-alert>

          <v-card-text>
            <v-container fluid>
              <v-row>
                <v-col cols="12">
                  <v-autocomplete
                    class="pb-0"
                    v-model="editedItem.branch_id"
                    :items="branchesList"
                    dense
                    placeholder="Select Branch"
                    outlined
                    item-value="id"
                    item-text="branch_name"
                    label="Branch"
                    hide-details
                  >
                  </v-autocomplete>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.name"
                    label="Name"
                    outlined
                    dense
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.email"
                    label="Email"
                    outlined
                    dense
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.password"
                    label="Password"
                    outlined
                    dense
                    hide-details
                    :append-icon="password_state ? 'mdi-eye' : 'mdi-eye-off'"
                    :type="password_state ? 'text' : 'password'"
                    @click:append="password_state = !password_state"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.password_confirmation"
                    label="Confirm Password"
                    outlined
                    dense
                    hide-details
                    :append-icon="
                      password_confirmation_state ? 'mdi-eye' : 'mdi-eye-off'
                    "
                    :type="password_confirmation_state ? 'text' : 'password'"
                    @click:append="
                      password_confirmation_state = !password_confirmation_state
                    "
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-autocomplete
                    :items="roles"
                    item-text="name"
                    item-value="id"
                    v-model="editedItem.role_id"
                    label="Role"
                    outlined
                    dense
                    hide-details
                  ></v-autocomplete>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    type="number"
                    v-model="editedItem.order"
                    label="Order"
                    outlined
                    dense
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col v-if="errResponse" cols="12" class="red--text">
                  {{ errResponse }}
                </v-col>
                <v-col cols="12" class="text-right">
                  <v-btn
                    :loading="actionLoading"
                    color="primary"
                    small
                    @click="save"
                    >Save</v-btn
                  >
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
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
                @click="getDataFromApi()"
              >
                <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
              </v-btn>
              <v-spacer></v-spacer>
              &nbsp;
              <v-btn
                v-if="can(`admin_create`)"
                @click="openDialog"
                small
                class="primary"
              >
                Admin +
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
              v-if="can(`admin_view`)"
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
              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item
                      v-if="can(`admin_edit`)"
                      @click="editItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can(`admin_delete`)"
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
    roles: [],
    password_state: false,
    password_confirmation_state: false,
    options: {},
    totalRowsCount: 0,
    AdminDialog: false,
    snack: false,
    snackColor: "",
    snackText: "",
    Model: "Admin",
    endpoint: "admin",
    search: "",
    snackbar: false,
    loading: false,
    actionLoading: false,
    total: 0,
    errResponse: null,
    editedIndex: -1,
    editedItem: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      role_id: 0,
      order: 0,
    },
    defaultItem: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      role_id: 0,
      order: 0,
    },
    response: "",
    data: [],
    errors: [],
    headers_table: [
      {
        text: "Name",
        align: "left",
        sortable: false,
        value: "name",
      },
      {
        text: "Email",
        align: "left",
        sortable: false,
        value: "email",
      },
      {
        text: "Role",
        align: "left",
        sortable: false,
        value: "role.name",
      },
      {
        text: "Order",
        align: "left",
        sortable: false,
        value: "order",
      },
      { text: "Options", align: "left", sortable: false, value: "options" },
    ],
    branchesList: [],
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
    this.editedItem.company_id = this.$auth.user.company_id;
    this.loading = true;
    this.getDataFromApi();

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
          company_id: this.editedItem.company_id,
        },
      })
      .then(({ data }) => {
        this.roles = data.data;
      });
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      if (url == "") url = this.endpoint;
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
          company_id: this.editedItem.company_id,
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
    openDialog() {
      this.AdminDialog = true;
      // this.editedItem = Object.assign({},this.editedItem);
    },
    editItem(item) {
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);

      if (item.email) {
        this.editedItem.password = "********";
        this.editedItem.password_confirmation = "********";
      }

      this.AdminDialog = true;
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
      this.AdminDialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    save() {
      this.actionLoading = true;

      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, this.editedItem)
          .then(({ data }) => {
            this.getDataFromApi();
            this.snackbar = data.status;
            this.response = data.message;
            this.close();
            this.AdminDialog = false;
            this.actionLoading = false;
          })
          .catch((err) => {
            this.errResponse = err?.response?.data?.message || null;
            this.actionLoading = false;
          });
      } else {
        this.$axios
          .post(this.endpoint, this.editedItem)
          .then(({ data }) => {
            this.getDataFromApi();
            this.snackbar = data.status;
            this.response = data.message;
            this.close();
            this.errors = [];
            this.search = "";
            this.AdminDialog = false;
            this.actionLoading = false;
          })
          .catch((err) => {
            this.errResponse = err?.response?.data?.message || null;
            this.actionLoading = false;
          });
      }
    },
  },
};
</script>
