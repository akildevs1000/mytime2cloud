<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-card>
      <v-row>
        <v-col cols="12" class="text-right" style="padding-top: 0px">
          <v-row>
            <v-col class="text-left pa-5"
              ><h4>Whatsapp Notifications Log</h4></v-col
            >
            <v-col class="text-right" style="max-width: 750px">
              <v-row>
                <v-col class="mt-2">
                  <v-icon @click="getDataFromApi()">mdi-refresh</v-icon>
                </v-col>
                <v-col style="">
                  <v-text-field
                    style="padding-top: 7px; float: right; width: 200px"
                    height="20"
                    class="employee-schedule-search-box"
                    @input="getDataFromApi(0)"
                    v-model="commonSearch"
                    label="Search Number and message"
                    dense
                    outlined
                    type="text"
                    append-icon="mdi-magnify"
                    clearable
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col style="">
                  <CustomFilter
                    style="float: right; padding-top: 5px; z-index: 9999"
                    @filter-attr="filterAttr"
                    :default_date_from="date_from"
                    :default_date_to="date_to"
                    :defaultFilterType="1"
                    :height="'40px'"
                /></v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-data-table
            :headers="headers"
            :items="items"
            :server-items-length="totalRowsCount"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }"
            class="elevation-0"
          >
            <template v-slot:item.sno="{ item, index }">
              {{
                currentPage
                  ? (currentPage - 1) * perPage +
                    (cumulativeIndex + items.indexOf(item))
                  : "-"
              }} </template
            ><template v-slot:item.sent_status="{ item }">
              {{ item.sent_status ? "Sent" : "Pending" }}
            </template>
            <template v-slot:item.building_type="{ item }">
              <div>
                {{ getBuildingTypeName(item.customer?.building_type_id) }}
              </div>
              <!-- <small style="font-size: 12px; color: #6c7184">
                  {{ item.landmark }}
                </small> -->
            </template>
            <template v-slot:item.email="{ item }">
              {{ item.email != "" ? item.email : item.wahtsapp }} </template
            ><template v-slot:item.status_datetime="{ item }">
              <div>
                {{
                  item.status_datetime != ""
                    ? $dateFormat.formatDateMonthYear(item.status_datetime)
                    : "---"
                }}
              </div>
            </template>
            <template v-slot:item.created_datetime="{ item }">
              <div>
                {{
                  item.created_datetime != ""
                    ? $dateFormat.formatDateMonthYear(item.created_datetime)
                    : "---"
                }}
              </div>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-card>
  </div>
</template>

<script>
export default {
  props: ["customer_id"],
  data() {
    return {
      filter_customer_id: null,
      customersList: [],
      date_from: null,
      date_to: null,
      dialogEventsList: false,
      snackbar: false,
      response: "",
      key: "",
      eventId: "",
      dialogAddCustomerNotes: false,
      dialogNotesList: false,
      date_from: "",
      date_to: "",
      loading: false,
      commonSearch: "",
      options: { perPage: 10 },
      cumulativeIndex: 1,
      perPage: 10,
      currentPage: 1,
      totalRowsCount: 0,
      headers: [
        { text: "#", value: "sno", sortable: false },
        {
          text: "Whatsapp Number",
          value: "whatsapp_number",
          sortable: false,
        },
        {
          text: "Status",
          value: "sent_status",
          sortable: false,
        },
        { text: "Sent Time", value: "status_datetime", sortable: false },
        { text: "Created Time", value: "created_datetime", sortable: false },
        {
          text: "message",
          value: "message",
          sortable: false,
        },
      ],
      items: [],
    };
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  async created() {
    let today = new Date();
    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;
    //this.getDataFromApi();
    await this.getCustomersList();
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async getCustomersList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/customers_list`, options).then(({ data }) => {
        this.customersList = [
          { id: null, building_name: "All Customers" },
          ...data,
        ];
      });
    },
    getBuildingTypeName(id) {
      if (this.$store.state.storeAlarmControlPanel.BuildingTypes) {
        let filter =
          this.$store.state.storeAlarmControlPanel.BuildingTypes.filter(
            (buildingType) => buildingType.id == id
          );

        if (filter[0]) return filter[0].name;
        else return "---";
      } else return "---";
    },

    showEvents(item) {
      this.dialogEventsList = true;
      this.date_from = item.armed_datetime;
      this.date_to = item.disarm_datetime;
    },
    viewNotes(item) {
      this.key = this.key + 1;
      this.eventId = item.id;
      this.dialogNotesList = true;
    },

    addNotes(item) {
      this.eventId = item.id;
      this.dialogAddCustomerNotes = true;
    },
    closeDialog() {
      this.dialogAddCustomerNotes = false;
      this.getDataFromApi();
      this.$emit("closeDialog");
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    downloadOptions(option) {
      let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;

      if (this.eventFilter) {
        filterSensorname = this.eventFilter;
      }

      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/device_armed_logs_print_pdf";
      if (option == "excel") url += "/device_armed_logs_export_excel";
      if (option == "download") url += "/device_armed_logs_download_pdf";
      url += "?company_id=" + this.$auth.user.company_id;
      url += "&date_from=" + this.date_from;
      url += "&date_to=" + this.date_to;
      if (this.commonSearch) url += "&common_search=" + this.commonSearch;

      //  url += "&alarm_status=" + this.filterAlarmStatus;

      window.open(url, "_blank");
    },

    getDataFromApi() {
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;
      if (!page > 0) return false;
      let options = {
        params: {
          page: page,
          //sortBy: sortedBy,
          sortDesc: sortedDesc,
          perPage: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
          customer_id: this.customer_id,
          date_from: this.date_from,
          date_to: this.date_to,
          filter_customer_id: this.filter_customer_id,
          commonSearch: this.commonSearch,
        },
      };

      try {
        this.$axios.get(`whatsapp_messages_logs`, options).then(({ data }) => {
          this.items = data.data;

          this.totalRowsCount = data.total;
          this.loading = false;
        });
      } catch (e) {}
    },
  },
};
</script>
