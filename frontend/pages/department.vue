<template>
  <div v-if="can(`department_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class=" ">
      <v-col cols="6">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }}</div>
      </v-col>
    </v-row>

    <div v-if="can(`employee_view`)">
      <v-row>
        <v-dialog v-model="dialogForm" :fullscreen="false" width="500px">
          <v-card elevation="0">
            <v-toolbar color="background" dense flat dark>
              <span>{{ formTitle }} {{ Model }}</span>
            </v-toolbar>
            <v-divider class="py-0 my-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row class="mt-4">
                  <v-col cols="12">
                    <v-text-field
                      v-model="editedItem.name"
                      placeholder="Departments"
                      outlined
                      dense
                    ></v-text-field>
                    <span v-if="errors && errors.name" class="error--text">{{
                      errors.name[0]
                    }}</span>
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
                      <v-btn class="primary" @click="save">Save</v-btn>
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
              <v-spacer></v-spacer>
              <v-toolbar-items>
                <v-btn
                  @click="newItem"
                  class="primary ms-4 pt-4 pb-4 toolbar-button-design"
                  color="primary"
                  >Add {{ Model }} +
                </v-btn>
              </v-toolbar-items>
            </v-toolbar>
            <v-text-field
              class="form-control py-0 ma-1 mb-0 w-25 float-start custom-text-box floating shadow-none"
              placeholder="Search..."
              solo
              flat
              @input="searchIt"
              v-model="search"
              :hide-details="true"
            ></v-text-field>
            <table>
              <tr>
                <th class="ps-5">#</th>
                <th>Department Code</th>
                <th>Department</th>
                <th>Sub Department</th>
                <th>View Sub Departments</th>
              </tr>
              <v-progress-linear
                v-if="loading"
                :active="loading"
                :indeterminate="loading"
                absolute
                color="primary"
              ></v-progress-linear>
              <tr v-for="(item, index) in data" :key="index">
                <td class="ps-5">
                  <b>{{ ++index }}</b>
                </td>
                <td>{{ caps(item.id) }}</td>
                <td>{{ caps(item.name) }}</td>
                <td>
                  <span v-if="item.children.length > 0">
                    <v-chip
                      small
                      class="primary ma-1"
                      v-for="(sub_dep, index) in item.children"
                      :key="index"
                    >
                      {{ caps(sub_dep.name) }}
                    </v-chip>
                  </span>
                  <p v-else>---</p>
                </td>
                <td>
                  <v-btn
                    @click="gotoSubdepartments(item)"
                    class="primary ms-4 pt-4 pb-4 toolbar-button-design"
                    color="primary"
                    ><span class="mdi mdi-view-list">Manage</span>
                  </v-btn>
                </td>
                <td class="text-center">
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
                </td>
              </tr>
            </table>
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
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
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
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    newItem() {
      this.dialogForm = true;
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
    getDataFromApi(url = this.endpoint) {
      this.loading = true;
      let page = this.pagination.current;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id,
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
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      let payload = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company.id,
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
<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  text-align: left;
  padding: 5px;
}

tr:nth-child(even) {
  background-color: #e9e9e9;
}

input[type="text"]:focus.custom-text-box {
  border: 2px solid #5fafa3 !important;
}

.toolbar-button-design {
  height: 38px !important;
  /* vertical-align: bottom; */
  margin: auto;
  border-radius: 5px;
}
</style>
