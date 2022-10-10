<template>
  <div v-if="can(`shift_access`)">
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
            v-if="can(`shift_create`)"
            small
            color="primary"
            to="/shift/create1"
            class="mb-2"
            >{{ Model }} +</v-btn
          >
        </div>
      </v-col>
    </v-row>
    <v-data-table
      v-if="can(`shift_view`)"
      v-model="ids"
      item-key="id"
      :headers="headers"
      :items="data"
      :server-items-length="total"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [5, 10, 15],
      }"
      class="elevation-1"
    >
      <template v-slot:item.days="{ item }">
        <v-chip
          class="primary ma-1"
          small
          v-for="(day, index) in item.days"
          :key="index"
          >{{ day }}</v-chip
        >
      </template>
      <template v-slot:item.time_table="{ item }">
        <span v-if="item.time_table">
          <v-tooltip v-if="item && item.time_table" top color="primary">
            <template v-slot:activator="{ on, attrs }">
              <div class="primary--text" v-bind="attrs" v-on="on">
                {{ item.time_table.on_duty_time }} -
                {{ item.time_table.off_duty_time }}
              </div>
            </template>
            <div
              v-for="(time_table, index) in getDataForToolTip(item)"
              :key="index"
            >
              {{ caps(index) }}: {{ time_table || "---" }}
            </div>
          </v-tooltip>
        </span>
        <span v-else>---</span>
      </template>
      <template v-slot:item.action="{ item }">
        <v-icon
          v-if="can(`shift_edit`)"
          color="secondary"
          small
          @click="editItem(item)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          v-if="can(`shift_delete`)"
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
    Model: "Shift",
    endpoint: "shift",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      {
        text: "Name",
        align: "left",
        sortable: false,
        value: "name",
      },
      {
        text: "Beginning Date",
        align: "left",
        sortable: false,
        value: "beginning_date",
      },
      {
        text: "OT Interval",
        align: "left",
        sortable: false,
        value: "overtime",
      },
      {
        text: "Min Hrs",
        align: "left",
        sortable: false,
        value: "working_hours",
      },
      {
        text: "Cycle Number",
        align: "left",
        sortable: false,
        value: "cycle_number",
      },
      {
        text: "Cycle Unit",
        align: "left",
        sortable: false,
        value: "cycle_unit",
      },

      {
        text: "Holidays",
        align: "left",
        sortable: false,
        value: "days",
      },
      {
        text: "Shift Type",
        align: "left",
        sortable: false,
        value: "shift_type.name",
      },
      {
        text: "Time Slots",
        align: "left",
        sortable: false,
        value: "time_table",
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
    getDataForToolTip(item) {
      if (item && !item.time_table) {
        return {};
      }

      let time_table = item.time_table;

      return {
        on_duty_time: time_table.on_duty_time || "---",
        off_duty_time: time_table.off_duty_time || "---",
        late_time: time_table.late_time || "---",
        early_time: time_table.early_time || "---",
        beginning_in: time_table.beginning_in || "---",
        ending_in: time_table.ending_in || "---",
        beginning_out: time_table.beginning_out || "---",
        ending_out: time_table.ending_out || "---",
        absent_min_in: time_table.absent_min_in || "---",
        absent_min_out: time_table.absent_min_out || "---",
      };
    },

    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
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
      this.$router.push(`/shift/${item.id}`);
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
