<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-5">
      <v-col cols="6">
        <h3>Device</h3>
        <div>Dashboard / Device</div>
      </v-col>
      <v-col cols="6">

      </v-col>
    </v-row>
    <v-data-table v-model="ids" show-select item-key="id" :headers="headers" :items="devices"
      :server-items-length="total" :loading="loading" :options.sync="options" :footer-props="{
        itemsPerPageOptions: [5, 10, 15],
      }" class="elevation-1">
      <template v-slot:top>
        <v-toolbar flat color="">
          <v-toolbar-title>List</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>

          <v-text-field @input="searchIt" v-model="search" label="Search" single-line hide-details></v-text-field>
        </v-toolbar>
      </template>
      <template v-slot:item.status="{ item }">
        <v-chip small class="p-2 mx-1" :color="item.status.name == 'active'?'primary':'error'">
          {{ item.status.name == "active" ? "online" : "offline" }}
        </v-chip>
      </template>
    </v-data-table>
  </div>
</template>
<script>
export default {
  data: () => ({
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      { text: "Location", align: "left", value: "location", sortable: false },
      { text: "Name", align: "left", value: "name", sortable: false },
      { text: "Short Name", align: "left", value: "short_name", sortable: false },
      { text: "Device Id", align: "left", value: "device_id", sortable: false },
      { text: "Type", align: "left", value: "device_type", sortable: false },
      { text: "Status", align: "left", value: "status", sortable: false },
    ],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    devices: [],
    errors: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New device" : "Edit device";
    },
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
      deep: true,
    },
  },
  created() {
    this.loading = true;
  },

  methods: {
    can(permission) {
      let user = this.$auth;
      return;
      return (
        (user && user.permissions.some((e) => e.permission == permission)) ||
        user.master
      );
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user?.company?.id,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        let items = data.data;

        if (sortBy.length === 1 && sortDesc.length === 1) {
          items = this.sorting(items, sortBy, sortDesc);
        }

        this.devices = items;
        this.total = data.total;
        this.loading = false;
      });
    },

    sorting(items, sortBy, sortDesc) {
      return items.sort((a, b) => {
        const sortA = a[sortBy[0]];
        const sortB = b[sortBy[0]];

        if (sortDesc[0]) {
          if (sortA < sortB) return 1;
          if (sortA > sortB) return -1;
          return 0;
        } else {
          if (sortA < sortB) return -1;
          if (sortA > sortB) return 1;
          return 0;
        }
      });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },









  },
};
</script>
