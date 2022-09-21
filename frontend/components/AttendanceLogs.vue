<template>
  <div>
    <v-row class="mt-5">
      <v-col md="6">
        <h3>Attendance Logs</h3>
        <div>Dashboard / Attendance Logs</div>
      </v-col>
      <v-col md="6">
        <div class="text-right">
          <v-btn small color="primary" class="mb-2" @click="fetch_logs"
            >Fetch Logs</v-btn
          >
        </div>
      </v-col>

      <v-col md="12">
        <v-tabs>
          <v-tab @click="fetch_report(null)"> All </v-tab>
          <v-tab @click="fetch_report(`daily`)"> Daily </v-tab>
          <v-tab @click="fetch_report(`monthly`)"> Monthly </v-tab>
          <v-tab @click="fetch_report(`yearly`)"> Yearly </v-tab>
          <v-tab-item v-for="n in 4" :key="n">
            <v-card>
              <v-data-table
                :headers="headers"
                :items="data"
                :options.sync="options"
                :server-items-length="total"
                :loading="loading"
                :footer-props="{
                  'items-per-page-options': [5, 10, 30, 50, 100]
                }"
                class="elevation-1"
              >
                <template v-slot:top>

                  <v-toolbar flat color="">
                    <v-toolbar-title>{{caps(type)}}</v-toolbar-title>
                    <v-divider class="mx-2" inset vertical></v-divider>
                    <v-text-field
                      @input="searchIt"
                      v-model="search"
                      label="Search"
                      single-line
                      hide-details
                    ></v-text-field>
                  </v-toolbar>
                </template>
              </v-data-table>
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  data() {
    return {
      headers: [
        { text: "Id", value: "UserID" },
        { text: "Name", value: "employee.full_name" },
        { text: "Department", value: "employee.department.name" },
        { text: "Designation", value: "employee.designation.name" },
        { text: "At", value: "LogTime" },
        { text: "Device Id", value: "DeviceID" },
        { text: "Device Name", value: "device.name" },
        { text: "Device Location", value: "device.location" }
      ],
      data: [],
      title: `Attendance Logs`,
      endpoint: "attendance_logs",
      ids: [],
      data: [],
      search: "",
      loading: false,
      page: 1,
      total: 0,
      type: null,
      page_options: {},

      options: {
        per_page: 5
      },
      pagination: {}
    };
  },

  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true
    }
  },

  async created() {},

  methods: {
    caps(str) {
      if (!str) {
        str = "all data";
      }
      return str.replace(/\b\w/g, c => c.toUpperCase());
    },
    fetch_report(type) {
      this.type = type;
      this.search = "";
      this.getDataFromApi();
    },
    fetch_logs() {
      this.loading = true;
      this.$axios
        .post(`attendance_logs`, { params: this.options })
        .then(({ data }) => {
          this.getDataFromApi();
          this.loading = false;
        });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(
          `attendance_logs_by_company/${this.$auth.user.company.id}/search/${e}`
        );
      }
    },

    getDataFromApi(
      url = `attendance_logs_by_company/${this.$auth.user.company.id}`
    ) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      this.$axios
        .get(url, {
          params: { per_page: itemsPerPage, page: page, type: this.type }
        })
        .then(({ data }) => {
          this.data = data.data;
          this.total = data.total;
          this.loading = false;
        });
    }
  }
};
</script>
<style>
.v-data-table-header-mobile {
  display: none !important;
}
</style>
