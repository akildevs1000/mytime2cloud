<template>
  <div v-if="can(`designation_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-5">
      <v-col cols="6">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }}</div>
      </v-col>
      <v-col cols="6">
        <div class="text-right">
          <v-btn
            v-if="can(`designation_delete`)"
            small
            color="error"
            class="mr-2 mb-2"
            @click="delteteSelectedRecords"
            >Delete Selected Records</v-btn
          >

          <!-- <v-btn
            v-if="can(`designation_create`)"
            small
            color="primary"
            @click="dialog = true"
            class="mb-2"
            >{{ Model }} +</v-btn
          > -->
        </div>
      </v-col>
    </v-row>

    <!-- <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">{{ formTitle }} {{ Model }}</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.name"
                  label="Designation"
                ></v-text-field>
                <span v-if="errors && errors.name" class="error--text">{{
                  errors.name[0]
                }}</span>
              </v-col>
              <v-col cols="12">
                <v-autocomplete
                  label="Select Department"
                  v-model="editedItem.department_id"
                  :items="departments"
                  item-text="name"
                  item-value="id"
                >
                </v-autocomplete>
                <span
                  v-if="errors && errors.department_id"
                  class="error--text"
                  >{{ errors.department_id[0] }}</span
                >
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn class="error" small @click="close"> Cancel </v-btn>
          <v-btn class="primary" small @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog> -->

    <v-row>
      <v-col md="4">
        <v-card>
          <v-toolbar color="primary" dense flat dark>
            <v-card-title>
              <span class="headline">{{ formTitle }} {{ Model }}</span>
            </v-card-title>
          </v-toolbar>

          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.name"
                    label="Designation"
                  ></v-text-field>
                  <span v-if="errors && errors.name" class="error--text">{{
                    errors.name[0]
                  }}</span>
                </v-col>
                <v-col cols="12">
                  <v-autocomplete
                    label="Select Department"
                    v-model="editedItem.department_id"
                    :items="departments"
                    item-text="name"
                    item-value="id"
                  >
                  </v-autocomplete>
                  <span
                    v-if="errors && errors.department_id"
                    class="error--text"
                    >{{ errors.department_id[0] }}</span
                  >
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-card-actions>
            <v-btn class="primary ml-4" @click="save">Save</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col md="8">
        <v-toolbar color="primary" dense flat dark>
          <v-card-title>
            <span class="headline">Designation List</span>
          </v-card-title>
        </v-toolbar>

        <v-toolbar style="background-color: white" flat>
          <v-row class="py-3">
            <!-- <v-col md="6"></v-col> -->
            <v-col md="12">
              <v-toolbar flat color="">
                <v-text-field
                  @input="searchIt"
                  v-model="search"
                  hide-details
                  outlined
                  dense
                  label="Search"
                ></v-text-field>
              </v-toolbar> </v-col
          ></v-row>
        </v-toolbar>

        <v-data-table
          v-if="can(`designation_view`)"
          v-model="ids"
          show-select
          item-key="id"
          :headers="headers"
          :items="data"
          :server-items-length="total"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [5, 10, 15]
          }"
          class="elevation-1"
        >
          <!-- <template v-slot:top>
            <div class="text-right">
              <v-toolbar class="w-100" flat color="">
                <v-toolbar-title>Search</v-toolbar-title>
                <v-divider class="mx-4" inset vertical></v-divider>
                <v-text-field
                  @input="searchIt"
                  v-model="search"
                  hide-details
                  outlined
                  dense
                ></v-text-field>
              </v-toolbar>
            </div>
          </template> -->
          <template v-slot:item.action="{ item }">
            <v-icon
              v-if="can(`designation_edit`)"
              color="secondary"
              small
              class="mr-2"
              @click="editItem(item)"
            >
              mdi-pencil
            </v-icon>
            <v-icon
              v-if="can(`designation_delete`)"
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
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    Model: "Designation",
    options: {},
    endpoint: "designation",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      { text: "Designation", align: "left", sortable: false, value: "name" },
      {
        text: "Department",
        align: "left",
        sortable: false,
        value: "department.name"
      },
      { text: "Actions", align: "center", value: "action", sortable: false }
    ],
    editedIndex: -1,
    editedItem: { name: "", department_id: "" },
    defaultItem: { name: "", department_id: "" },
    response: "",
    data: [],
    departments: [],
    errors: []
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true
    }
  },
  created() {
    this.loading = true;
    this.getDepartments();
  },

  methods: {
    getDepartments() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id
        }
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
      });
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id
        }
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
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
      this.dialog = true;
    },

    delteteSelectedRecords() {
      let just_ids = this.ids.map(e => e.id);
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: just_ids
          })
          .then(res => {
            if (!res.data.status) {
              this.errors = res.data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = res.data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
          })
          .catch(err => console.log(err));
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
          .catch(err => console.log(err));
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      let payload = {
        name: this.editedItem.name.toLowerCase(),
        department_id: this.editedItem.department_id,
        company_id: this.$auth.user.company.id
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              const index = this.data.findIndex(
                item => item.id == this.editedItem.id
              );
              this.data.splice(index, 1, payload);
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
            }
          })
          .catch(err => console.log(err));
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
            }
          })
          .catch(res => console.log(res));
      }
    }
  }
};
</script>
