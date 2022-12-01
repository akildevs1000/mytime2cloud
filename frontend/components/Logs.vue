<template>
  <div>
    <v-row>
      <v-col xs="12" sm="12" md="3" cols="12">
        <input
          class="form-control py-3 custom-text-box floating shadow-none"
          placeholder="Search..."
          @input="searchIt"
          v-model="search"
          type="text"
        />
      </v-col>
    </v-row>
    <v-data-table
      v-model="ids"
      item-key="id"
      :headers="headers"
      :items="data"
      :server-items-length="total"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [50, 100, 500, 1000]
      }"
      class="elevation-1 mt-5"
    ></v-data-table>
  </div>
</template>
<script>
export default {
  data: () => ({
    Model: "Logs",
    options: {},
    endpoint: "attendance_logs",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    headers: [
      {
        text: "UserID",
        align: "center",
        sortable: false,
        value: "UserID"
      },
      { text: "DeviceID", align: "center", sortable: false, value: "DeviceID" },
      { text: "LogTime", align: "center", sortable: false, value: "LogTime" }
    ],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
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
  mounted() {},
  created() {
    this.loading = true;
    let options = {
      params: {
        per_page: this.options.itemsPerPage,
        company_id: this.$auth.user.company.id
      }
    };
  },
  methods: {
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
        this.$store.commit("logs", data);
        this.loading = false;
      });
    },
    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
      }
    }
  }
};
</script>
