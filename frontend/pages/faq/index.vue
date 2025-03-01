<template>
  <v-container>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div>
      <v-dialog
        persistent
        v-model="FaqDialog"
        :fullscreen="false"
        width="600px"
      >
        <WidgetsClose left="590" @click="close" />
        <v-card>
          <v-alert dense flat class="primary white--text">
            <span>{{ isEditingItem ? "Edit" : "New" }} FAQ </span>
          </v-alert>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    hide-details
                    v-model="payload.question"
                    label="Question"
                    outlined
                    dense
                  ></v-text-field>
                  <span v-if="errors && errors.question" class="error--text">{{
                    errors.question[0]
                  }}</span>
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    rows="3"
                    hide-details
                    v-model="payload.answer"
                    label="Answer"
                    outlined
                    dense
                  ></v-textarea>
                  <span v-if="errors && errors.answer" class="error--text">{{
                    errors.answer[0]
                  }}</span>
                </v-col>

                <v-col cols="12">
                  <div class="text-right">
                    <v-btn class="grey white--text" @click="close">Close</v-btn>
                    <v-btn class="primary" @click="submit">Save</v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>

      <v-container fluid>
        <v-card>
          <v-row>
            <v-col md="12" lg="12">
              <!-- <Back color="primary" /> -->

              <v-card class="mb-5 mt-2 rounded-md" elevation="0">
                <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
                  {{ snackText }}

                  <template v-slot:action="{ attrs }">
                    <v-btn v-bind="attrs" text @click="snack = false">
                      Close
                    </v-btn>
                  </template>
                </v-snackbar>

                <v-card-title>
                  <div>
                    {{ Model }}
                    <v-btn
                      dense
                      class="px-0"
                      x-small
                      :ripple="false"
                      text
                      title="Reload"
                      @click="getDataFromApi"
                    >
                      <v-icon class="ml-2" dark>mdi-reload</v-icon>
                    </v-btn>
                  </div>
                  <div class="px-2">
                    <v-text-field
                      outlined
                      dense
                      v-model="search"
                      append-icon="mdi-magnify"
                      label="Search"
                      single-line
                      hide-details
                      @input="debouncedSearch"
                    ></v-text-field>
                  </div>

                  <v-spacer></v-spacer>
                  &nbsp;
                  <v-btn @click="FaqDialog = true" small class="primary">
                    FAQ +
                  </v-btn>
                </v-card-title>
                <v-data-table
                  dense
                  :headers="headers_table"
                  :items="frequently_asked_questions"
                  model-value="data.id"
                  :loading="loading"
                  :options.sync="options"
                  :footer-props="{
                    itemsPerPageOptions: [100, 200, 500],
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
                        <v-list-item @click="editItem(item)">
                          <v-list-item-title style="cursor: pointer">
                            <v-icon color="secondary" small>
                              mdi-pencil
                            </v-icon>
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
        </v-card>
      </v-container>
    </div>
  </v-container>
</template>
<script>
export default {
  layout: "master",
  data: () => ({
    options: {},
    totalRowsCount: 0,
    frequently_asked_questions: [],

    payload: {},
    FaqDialog: false,
    snack: false,
    snackColor: "",
    snackText: "",
    dialogForm: false,
    Model: "FAQs",
    options: {},
    endpoint: "faqs",
    search: "",
    debounceTimer: null, // Timer for debouncing
    snackbar: false,
    loading: false,
    total: 0,

    isEditingItem: false,

    response: "",
    data: [],
    errors: [],
    headers_table: [
      {
        text: "Question",
        align: "left",
        sortable: false,
        value: "question",
      },
      {
        text: "Answer",
        align: "left",
        sortable: false,
        value: "answer",
      },
      { text: "Options", align: "left", sortable: false, value: "options" },
    ],
  }),

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
    debouncedSearch(query) {
      clearTimeout(this.debounceTimer); // Clear the previous timer

      this.debounceTimer = setTimeout(() => {
        if (query.trim().length < 2) {
          return;
        }

        this.getDataFromApi();
      }, 300);
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
          query: this.search,
        },
      };
      this.$axios.get(this.endpoint, this.payloadOptions).then(({ data }) => {
        this.frequently_asked_questions = data.data;
        this.loading = false;
        this.totalRowsCount = data.total;
      });
    },

    editItem(item) {
      this.isEditingItem = true;
      this.payload = Object.assign({}, item);
      this.FaqDialog = true;
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
            this.getDataFromApi();
          })
          .catch((err) => console.log(err));
    },

    close() {
      setTimeout(() => {
        this.FaqDialog = false;
        this.payload = {};
        this.isEditingItem = false;
      }, 300);
    },
    submit() {
      if (this.isEditingItem) {
        this.$axios
          .put(this.endpoint + "/" + this.payload.id, this.payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.FaqDialog = false;
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, this.payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.FaqDialog = false;
            }
          })
          .catch((res) => console.log(res));
      }
    },
  },
};
</script>
