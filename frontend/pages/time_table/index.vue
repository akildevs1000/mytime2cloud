<template>
  <div v-if="can(`time_table_access`)">
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
            v-if="can(`time_table_create`)"
            small
            color="primary"
            to="/time_table/create"
            class="mb-2"
            >{{ Model }} +</v-btn
          >
        </div>
      </v-col>
    </v-row>
    <v-data-table
      v-if="can(`time_table_view`)"
      v-model="ids"
      item-key="id"
      :headers="headers"
      :items="data"
      :server-items-length="total"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [50, 100, 500,1000],
      }"
      class="elevation-1"
    >
      <!-- <template v-slot:top>
        <v-toolbar flat color="">
          <v-toolbar-title>List</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>

          <v-text-field
            @input="searchIt"
            v-model="search"
            label="Search"
            single-line
            hide-details
          ></v-text-field>
        </v-toolbar>
      </template> -->
      <template v-slot:item.action="{ item }">
        <v-icon
          v-if="can(`time_table_edit`)"
          color="secondary"
          small
          @click="editItem(item)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          v-if="can(`time_table_delete`)"
          color="error"
          small
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
      <template v-slot:item.off_days="{ item }">
        <v-chip
          small
          class="primary ma-1"
          v-for="(off_day, index) in item.off_days"
          :key="index"
          >{{ off_day }}</v-chip
        >
      </template>
    </v-data-table>
    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    options: {},
    Model: "Time Slots",
    endpoint: "time_table",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      {
        text: "On Duty Time",
        align: "left",
        sortable: false,
        value: "on_duty_time",
      },
      {
        text: "Off Duty Time",
        align: "left",
        sortable: false,
        value: "off_duty_time",
      },
      {
        text: "Break Start",
        align: "left",
        sortable: false,
        value: "break_time_start",
      },
      {
        text: "Break Start",
        align: "left",
        sortable: false,
        value: "break_time_end",
      },
      { text: "Late Time", align: "left", sortable: false, value: "late_time" },
      {
        text: "Early Time",
        align: "left",
        sortable: false,
        value: "early_time",
      },
      {
        text: "Beginning In",
        align: "left",
        sortable: false,
        value: "beginning_in",
      },
      {
        text: "Beginning Out",
        align: "left",
        sortable: false,
        value: "beginning_out",
      },
      {
        text: "Ending In",
        align: "left",
        sortable: false,
        value: "ending_in",
      },
      {
        text: "Ending Out",
        align: "left",
        sortable: false,
        value: "ending_out",
      },
      {
        text: "Count as Workday",
        align: "left",
        sortable: false,
        value: "count_as_workday",
      },
      {
        text: "Absent Min In",
        align: "left",
        sortable: false,
        value: "absent_min_in",
      },
      {
        text: "Absent Min Out",
        align: "left",
        sortable: false,
        value: "absent_min_out",
      },

      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    response: "",
    data: [],
    errors: [],
  }),

  watch: {
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
    caps(str) {
      return str.replace(/\b\w/g, (c) => c.toUpperCase());
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          page: page,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
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
      this.$router.push(`/time_table/${item.id}`);
    },

    delteteSelectedRecords() {
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: this.ids.map((e) => e.id),
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
  },
};
</script>
