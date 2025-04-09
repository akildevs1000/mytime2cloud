<template>
  <div v-if="can(`web_login_logs_access`)">
    <v-card class="mb-5 mt-2 rounded-md" elevation="0">
      <v-toolbar class="rounded-md" dense flat>
        <v-toolbar-title><span> Activity</span></v-toolbar-title>
        <v-row>
          <v-col>
            <div class="px-1 mt-3" style="width: 100%">
              <v-btn
                dense
                class="ma-0 px-0"
                x-small
                :ripple="false"
                text
                title="Reload"
              >
                <v-icon class="ml-2" @click="getRecords()" dark
                  >mdi-reload</v-icon
                >
              </v-btn>
            </div>
          </v-col>
          <v-col>
            <div class="px-1 mt-3" style="width: 100%">
              <v-autocomplete
                style="width: 100%"
                label="Action"
                outlined
                dense
                :items="['Login', 'Report']"
                :hide-details="true"
              >
              </v-autocomplete>
            </div>
          </v-col>
          <v-col class="text-right">
            <div class="px-1 mt-3" style="width: 100%">
              <CustomFilter
                @filter-attr="filterAttr"
                :defaultFilterType="1"
                height="40px"
              />
            </div>
          </v-col>
        </v-row>
      </v-toolbar>
      <v-data-table
        class="pt-5"
        dense
        :headers="headers"
        :items="logs"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100],
        }"
        :server-items-length="totalRowsCount"
      >
        <template v-slot:item.options="{ item }">
          <v-icon
            color="secondary"
            small
            class="mr-2"
            @click="openDialog(item)"
          >
            mdi-eye
          </v-icon>
        </template>

        <template v-slot:item.action_by="{ item }">
          {{
            item.user.employee
              ? item.user.employee.first_name
              : item?.user?.name
          }}
        </template>

        <template v-slot:item.description="{ item }">
          {{ extractBeforePayload(item.description) }}
        </template>
      </v-data-table>
      <v-dialog v-model="viewDialog" max-width="600px">
        <WidgetsClose left="590" @click="viewDialog = false" />
        <v-card>
          <div class="primary">
            <div class="px-4 py-2 white--text">Data</div>
          </div>
          <v-container fluid>
            <v-simple-table dense>
              <tbody>
                <tr v-for="(value, key) in parsedPayload" :key="key">
                  <td>
                    {{ key }}
                  </td>
                  <td>{{ value }}</td>
                </tr>
              </tbody>
            </v-simple-table>
          </v-container>
        </v-card>
      </v-dialog>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data() {
    return {
      viewDialog: false,
      selectedItem: null,
      loading: false,
      items: [],
      emptyLogmessage: "",
      number_of_records: 5,
      logs: [],
      url: process.env.SOCKET_ENDPOINT,
      socket: null,
      totalRowsCount: 0,

      total: 0,
      options: {},
      headers: [
        {
          text: "Action By",
          align: "left",
          sortable: false,
          filterable: true,
          value: "action_by",
        },
        {
          text: "Action",
          align: "left",
          sortable: false,
          filterable: true,

          value: "action",
        },

        {
          text: "Action Type",
          align: "left",
          sortable: false,
          filterable: true,
          value: "type",
        },

        {
          text: "Description",
          align: "left",
          sortable: false,
          filterable: true,
          value: "description",
        },

        {
          text: "Type",
          align: "left",
          sortable: false,
          filterable: true,
          value: "type",
        },

        {
          text: "Date Time",
          align: "left",
          sortable: false,
          filterable: true,
          value: "date_time", //edit purpose
        },

        {
          text: "Options",
          align: "left",
          sortable: false,
          filterable: true,
          value: "options", //edit purpose
        },
      ],
      branch_id: null,
      isCompany: true,
      dateRange: { from: null, to: null },
    };
  },
  watch: {
    options: {
      handler() {
        this.getRecords();
      },
      deep: true,
    },
  },
  async created() {
    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }
  },
  computed: {
    parsedPayload() {
      const marker = " Payload:";
      const index = this.selectedItem?.description.indexOf(marker);
      if (index !== -1) {
        const jsonString = this.selectedItem?.description
          .substring(index + marker.length)
          .trim();
        try {
          return JSON.parse(jsonString);
        } catch (e) {
          return { error: "Invalid JSON" };
        }
      }
      return {};
    },
  },
  methods: {
    filterAttr({ from, to }) {
      this.dateRange = { from, to };

      console.log("🚀 ~ filterAttr ~ this.dateRange:", this.dateRange);
      this.getRecords();
    },
    extractBeforePayload(text) {
      if (typeof text === "string") {
        const index = text.indexOf("{");
        return index !== -1 ? text.substring(0, index) : text;
      }
      return "";
    },
    openDialog(item) {
      this.selectedItem = item;
      this.viewDialog = true;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getRecords() {
      //let filter_value = this.datatable_search_textbox;
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      if (page == 1) this.loading = true;
      let itemsPerPage1 = itemsPerPage;
      if (!itemsPerPage1) itemsPerPage1 = 5;
      let options = {
        params: {
          branch_id: this.branch_id,
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage1,
          company_id: this.$auth.user.company_id,
          ...this.dateRange,
        },
      };

      this.$axios.get(`activity`, options).then(({ data }) => {
        this.totalRowsCount = data.total;
        this.logs = data.data;
        this.loading = false;
      });
    },
  },
};
</script>
