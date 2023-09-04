<template>
  <div v-if="can(`shift_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-card elevation="0" v-if="can(`shift_view`)">
      <v-toolbar class="rounded-md" color="background" dense flat dark>
        <v-toolbar-title
          ><span> {{ Model }} List</span></v-toolbar-title
        >
        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn dense x-small :ripple="false" text v-bind="attrs" v-on="on">
              <v-icon color="white" @click="getDataFromApi()" dark
                >mdi mdi-reload</v-icon
              >
            </v-btn>
          </template>
          <span>Reload</span>
        </v-tooltip>
        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn x-small :ripple="false" text v-bind="attrs" v-on="on">
              <v-icon dark white @click="toggleFilter">mdi-filter</v-icon>
            </v-btn>
          </template>
          <span>Filter</span>
        </v-tooltip>
        <!-- <a style="padding-left: 10px" title="Reload Page/Reset Form" @click="getDataFromApi"><v-icon class="mx-1">mdi
            mdi-reload</v-icon></a> -->
        <v-spacer></v-spacer>

        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              dense
              x-small
              :ripple="false"
              text
              v-bind="attrs"
              v-on="on"
              @click="goToCreate"
            >
              <v-icon color="white" @click="getDataFromApi()" dark
                >mdi mdi-plus-circle</v-icon
              >
            </v-btn>
          </template>
          <span>Add New Shift Details</span>
        </v-tooltip>
      </v-toolbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-data-table
        dense
        :server-items-length="total"
        :headers="headers_table"
        :items="data"
        model-value="data.id"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100, 500, 1000],
        }"
        class="elevation-1"
      >
        <template v-slot:header="{ props: { headers } }">
          <tr v-if="isFilter">
            <td v-for="header in headers" :key="header.text">
              <v-text-field
                clearable
                :hide-details="true"
                v-if="header.filterable && !header.filterSpecial"
                v-model="filters[header.key]"
                :id="header.value"
                @input="applyFilters(header.key, $event)"
                outlined
                dense
                autocomplete="off"
              ></v-text-field>
            </td>
          </tr>
        </template>
        <template v-slot:item.sno="{ item, index }">
          <b>{{ ++index }}</b>
        </template>

        <template v-slot:item.autoshift_count="{ item }">
          <v-icon v-if="item && item.autoshift_count" color="success darken-1"
            >mdi-check</v-icon
          >
          <v-icon v-else color="error">mdi-close</v-icon>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-icon color="primary" small class="mr-2" @click="editItem(item)">
            mdi-pencil
          </v-icon>
          <v-icon color="error" small @click="deleteItem(item)">
            mdi-delete
          </v-icon>
        </template>
      </v-data-table>
    </v-card>

    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    isFilter: false,
    filters: {},
    shifts: [],
    datatable_search_textbox: "",
    datatable_searchById: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",

    options: {},
    Model: "Shift",
    endpoint: "shift",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      { text: "Name" },
      { text: "Type" },
      { text: "In" },
      { text: "Start In" },
      { text: "Ending In" },
      { text: "Late In" },
      { text: "Gap In" },
      { text: "Out" },
      { text: "Start Out" },
      { text: "Ending Out" },
      { text: "Early Out" },
      { text: "Gap Out" },
      { text: "Absent In" },
      { text: "Absent Out" },
      { text: "Working Hrs" },
      { text: "OT Interval" },
      // { text: "Off Days" },
      { text: "Actions" },
    ],
    headers_table: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        key: "name",
        value: "name",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Shift Type",
        align: "left",
        sortable: true,
        key: "shift_type_name",
        value: "shift_type.name",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Auto Shift",
        align: "left",
        sortable: true,
        key: "autoshift_count",
        value: "autoshift_count",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "In",
        align: "left",
        sortable: true,
        key: "on_duty_time",
        value: "on_duty_time",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Start In",
        align: "left",
        sortable: true,
        key: "beginning_in",
        value: "beginning_in",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Ending In",
        align: "left",
        sortable: true,
        key: "ending_in",
        value: "ending_in",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Late In",
        align: "left",
        sortable: true,
        key: "late_time",
        value: "late_time",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Gap In",
        align: "left",
        sortable: true,
        key: "gap_in",
        value: "gap_in",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Out",
        align: "left",
        sortable: true,
        key: "off_duty_time",
        value: "off_duty_time",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Start Out",
        align: "left",
        sortable: true,
        key: "beginning_out",
        value: "beginning_out",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Ending Out",
        align: "left",
        sortable: true,
        key: "ending_out",
        value: "ending_out",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Early Out",
        align: "left",
        sortable: true,
        key: "early_time",
        value: "early_time",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Gap Out",
        align: "left",
        sortable: true,
        key: "gap_out",
        value: "gap_out",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Absent In",
        align: "left",
        sortable: true,
        key: "absent_min_in",
        value: "absent_min_in",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Absent Out",
        align: "left",
        sortable: true,
        key: "absent_min_out",
        value: "absent_min_out",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Working Hrs",
        align: "left",
        sortable: true,
        key: "working_hours",
        value: "working_hours",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "OT Interval",
        align: "left",
        sortable: true,
        key: "overtime_interval",
        value: "overtime_interval",
        filterable: false,
        filterSpecial: false,
      },
      // { text: "Off Days" },
      {
        text: "Actions",
        align: "left",
        sortable: false,
        key: "actions",
        value: "actions",
      },
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
    this.getDataFromApi();
    // this.getShifts();
  },

  methods: {
    getShifts() {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
      };
      this.$axios.get("shift", { params: options }).then(({ data }) => {
        this.shifts = data.data;
        //this.shifts.unshift({ name: "All Shifts", id: "" });
      });
    },
    toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },
    goToCreate() {
      this.$router.push(`/shift/create`);
    },
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
      //this.datatable_search_textbox = '';
    },
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
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    getRecords(filter_column = "", filter_value = "") {
      this.getDataFromApi(this.endpoint, filter_column, filter_value);
    },
    applyFilters() {
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          ...this.filters,
        },
      };
      if (filter_column != "") options.params[filter_column] = filter_value;

      this.$axios.get(url, options).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
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
