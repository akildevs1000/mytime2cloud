<template>
  <div v-if="can(`${permission_name}_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div>
      <v-dialog v-model="dialogNew" persistent width="400px">
        <WidgetsClose @click="dialogNew = false" left="390" />
        <v-card>
          <v-card-title dense class="popup_background">
            <span>New {{ Model }}</span>
          </v-card-title>
          <v-card-text>
            <v-row class="pt-5">
              <v-col cols="12">
                <v-text-field
                  v-model="payload.name"
                  :label="`${Model} Name`"
                  outlined
                  dense
                  :hide-details="!errors.name"
                  :error-messages="errors && errors.name && errors.name[0]"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <div class="text-right">
                  <v-btn small class="primary" @click="submit">Save</v-btn>
                </div>
              </v-col>
            </v-row>
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
                v-if="can(`${permission_name}_create`)"
                @click="newItem"
                small
                class="primary"
              >
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
              :headers="headers"
              :items="items"
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
                      v-if="can(`${permission_name}_edit`)"
                      @click="editItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can(`${permission_name}_delete`)"
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
  props:["Model","permission_name","endpoint","headers"],
  data: () => ({
    payload: {
      name: "",
    },
    dialogNew: false,
    options: {},
    isFilter: false,
    generateLogsDialog: false,
    totalRowsCount: 0,
    items: [],

    snack: false,
    snackColor: "",
    snackText: "",
    dialogForm: false,
    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },

    snackbar: false,
    loading: false,
    total: 0,

    editedIndex: -1,
    response: "",
    errors: [],
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
    this.getDataFromApi();
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    newItem() {
      this.editedIndex = -1;
      this.dialogNew = true;
    },
    getDataFromApi() {
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
        },
      };
      this.$axios.get(this.endpoint, this.payloadOptions).then(({ data }) => {
        this.items = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
        this.totalRowsCount = data.total;
      });
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
            this.dialogNew = false;
            this.payload = {
              name: "",
            };
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
            this.dialogNew = false;
            this.payload = {
              name: "",
            };
          }
        })
        .catch((res) => console.log(res));
    },
  },
};
</script>
