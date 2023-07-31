<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <!-- <v-col xs="12" sm="12" md="3" cols="12">
        <v-select class="form-control" @change="getDataFromApi(`device`)" v-model="pagination.per_page"
          :items="[10, 25, 50, 100]" placeholder="Per Page Records" solo hide-details flat></v-select>
      </v-col> -->
      <!-- <v-col xs="12" sm="12" md="3" cols="12">
        <v-text-field class="form-control py-0 custom-text-box floating shadow-none" placeholder="Search..." solo flat
          @input="searchIt" v-model="search" hide-details></v-text-field>
      </v-col> -->
    </v-row>

    <v-card class="mb-5 mt-3" elevation="0">
      <v-toolbar class="rounded-md" color="background" dense flat dark>
        <v-toolbar-title><span> Devices List</span></v-toolbar-title>

        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              dense
              class="ma-0 px-0"
              x-small
              :ripple="false"
              text
              v-bind="attrs"
              v-on="on"
            >
              <v-icon color="white" class="ml-2" @click="getDataFromApi()" dark
                >mdi mdi-reload</v-icon
              >
            </v-btn>
          </template>
          <span>Reload</span>
        </v-tooltip>

        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              x-small
              :ripple="false"
              text
              v-bind="attrs"
              v-on="on"
              @click="toggleFilter()"
            >
              <v-icon dark white>mdi-filter</v-icon>
            </v-btn>
          </template>
          <span>Filter</span>
        </v-tooltip>

        <v-spacer></v-spacer>

        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              x-small
              :ripple="false"
              text
              v-bind="attrs"
              v-on="on"
              @click="updateDevicesHealth"
            >
              <v-icon dark white>mdi-cached</v-icon>
            </v-btn>
          </template>
          <span>Sync Devices</span>
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
        :headers="headers_table"
        :items="data"
        model-value="data.id"
        :loading="loading"
        :footer-props="{
          itemsPerPageOptions: [50, 100, 500, 1000],
        }"
        class="elevation-1"
        :options.sync="options"
        :server-items-length="totalRowsCount"
      >
        <template v-slot:header="{ props: { headers } }">
          <tr v-if="isFilter">
            <td
              style="width: 50px"
              v-for="header in headers"
              :key="header.text"
            >
              <v-text-field
                clearable
                :hide-details="true"
                v-if="header.filterable && !header.filterSpecial"
                v-model="filters[header.value]"
                :id="header.value"
                @input="applyFilters(header.key, $event)"
                outlined
                dense
                autocomplete="off"
              ></v-text-field>
              <v-select
                :hide-details="true"
                @change="applyFilters('status', $event)"
                item-value="value"
                item-text="title"
                v-model="filters[header.text]"
                outlined
                dense
                v-else-if="header.filterSpecial && header.text == 'Status'"
                :items="[
                  { value: '', title: 'All' },
                  { value: '1', title: 'Online' },
                  {
                    value: '2',
                    title: 'Offline',
                  },
                ]"
              ></v-select>
            </td>
          </tr>
        </template>
        <template v-slot:item.name="{ item }">
          {{ caps(item.name) }}
        </template>
        <template v-slot:item.short_name="{ item }">
          {{ caps(item.short_name) }}
        </template>
        <template v-slot:item.location="{ item }">
          {{ caps(item.location) }}
        </template>
        <template v-slot:item.device_id="{ item }">
          {{ item.device_id }}
        </template>
        <template v-slot:item.device_type="{ item }">
          {{ caps(item.device_type) }}
        </template>
        <template v-slot:item.status_id="{ item }">
          <v-chip
            small
            class="p-2 mx-1"
            :color="item.status.name == 'active' ? 'primary' : 'error'"
          >
            {{ item.status.name == "active" ? "online" : "offline" }}
          </v-chip>
        </template>
        <template v-slot:item.status="{ item }">
          <v-chip
            small
            class="p-2"
            color="primary"
            @click="open_door(item.device_id)"
          >
            Open
          </v-chip>
          <v-chip
            small
            class="p-2 mx-1"
            color="primary"
            @click="open_door_always(item.device_id)"
          >
            Open Always
          </v-chip>

          <v-chip
            small
            class="p-2"
            color="error"
            @click="open_door_always(item.device_id)"
          >
            Close
          </v-chip>
        </template>
        <template v-slot:item.sync_date_time="{ item }">
          <v-chip
            small
            class="p-2 mx-1"
            @click="sync_date_time(item)"
            :color="'primary'"
          >
            {{
              item.sync_date_time == "---"
                ? "click to sync"
                : item.sync_date_time
            }}
          </v-chip>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
<script>
export default {
  data: () => ({
    showFilters: false,
    filters: {},
    isFilter: false,
    totalRowsCount: 0,
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",

    Model: "Device",
    pagination: {
      current: 1,
      total: 0,
      per_page: 100,
    },
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    data: [],
    loading: false,
    total: 0,
    headers: [
      { text: "&nbsp #" },
      { text: "Name" },
      { text: "Short Name" },
      { text: "Location" },
      { text: "Device Id" },
      { text: "Type" },
      { text: "Status" },
      { text: "Door" },
      { text: "Time Sync" },
    ],
    headers_table: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "name",
        filterable: true,
      },
      {
        text: "Short Name",
        align: "left",
        sortable: true,
        value: "short_name",
        filterable: true,
      },
      {
        text: "Location",
        align: "left",
        sortable: true,
        value: "location",
        filterable: true,
      },
      {
        text: "Device Id",
        align: "left",
        sortable: true,
        value: "device_id",
        filterable: true,
      },
      {
        text: "Type",
        align: "left",
        sortable: true,
        value: "device_type",
        filterable: true,
      },
      {
        text: "Status",
        align: "center",
        sortable: true,
        value: "status_id",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Door",
        align: "center",
        sortable: false,
        value: "status",
        filterable: false,
      },
      {
        text: "Time Sync",
        align: "left",
        sortable: true,
        value: "sync_date_time",
        filterable: false,
      },
    ],
    editedIndex: -1,
    response: "",
    errors: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New device" : "Edit device";
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
    datatable_save() {},
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
    },
    sync_date_time(item) {
      let dt = new Date();

      let year = dt.getFullYear();
      let month = dt.getMonth() + 1;
      let day = dt.getDate();

      let hours = dt.getHours();
      hours = hours < 10 ? "0" + hours : hours;

      let minutes = dt.getMinutes();
      minutes = minutes < 10 ? "0" + minutes : minutes;

      let seconds = dt.getSeconds();
      seconds = seconds < 10 ? "0" + seconds : seconds;

      let sync_able_date_time = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

      let options = {
        params: {
          sync_able_date_time: sync_able_date_time,
        },
      };

      this.$axios
        .get(`sync_device_date_time/${item.device_id}`, options)
        .then(({ data }) => {
          if (data.status) {
            const index = this.data.findIndex((row) => row.id == item.id);
            this.data.splice(index, 1, data.record);
          }

          this.snackbar = true;
          this.response = data.message;
        });
    },
    open_door(device_id) {
      let options = {
        params: { device_id },
      };
      this.$axios.get(`open_door`, options).then(({ data }) => {
        this.snackbar = true;
        this.response = data;
        // this.getDataFromApi();
      });
    },
    open_door_always(device_id) {
      let options = {
        params: { device_id },
      };
      this.$axios.get(`open_door_always`, options).then(({ data }) => {
        this.snackbar = true;
        this.response = data.message;
        this.getDataFromApi();
      });
    },
    close_door(device_id) {
      let options = {
        params: { device_id },
      };
      this.$axios.get(`close_door`, options).then(({ data }) => {
        this.snackbar = true;
        this.response = data.message;
        this.getDataFromApi();
      });
    },
    can(permission) {
      let user = this.$auth;
      return;
      return (
        (user && user.permissions.some((e) => e.permission == permission)) ||
        user.master
      );
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
    applyFilters() {
      this.getDataFromApi();
    },
    toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};

      this.isFilter = false;
      this.getDataFromApi();
    },
    async getDataFromApi(
      url = this.endpoint,
      filter_column = "",
      filter_value = ""
    ) {
      if (url == "") url = this.endpoint;
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
          company_id: this.$auth.user.company.id,
          ...this.filters,
        },
      };
      if (filter_column != "") {
        if (filter_column == "serach_status_name") {
          options.params[filter_column] =
            filter_value.toLowerCase() == "online"
              ? "active"
              : filter_value.toLowerCase() == "offline"
              ? "inactive"
              : "";
        } else options.params[filter_column] = filter_value;
      }
      await this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.totalRowsCount = data.total;
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },
    async updateDevicesHealth() {
      let options = {
        params: {
          company_id: this.$auth.user.company.id,
        },
      };

      await this.$axios
        .get("/check_device_health", options)
        .then(({ data }) => {
          this.snackbar = true;
          this.response = data;
          this.getDataFromApi();
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
