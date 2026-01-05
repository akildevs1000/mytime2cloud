<template>
  <span>
    <v-data-table
      dense
      :headers="headers"
      :items="companies"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [100, 500, 1000],
      }"
      class="elevation-1 pa-3"
    >
      <template v-slot:top>
        <v-toolbar flat dense class="mb-5">
          Companies
          <v-icon color="primary" right class="mt-1" @click="getDataFromApi()"
            >mdi-reload</v-icon
          >
          <v-spacer></v-spacer>
          <DesktopCompanyCreate
            :endpoint="`${baseUrl}/company`"
            @response="getDataFromApi"
          />
        </v-toolbar>
      </template>
      <template v-slot:item.options="{ item }">
        <v-menu bottom left>
          <template v-slot:activator="{ on, attrs }">
            <div class="text-center">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </div>
          </template>
          <v-list width="120" dense>
            <v-list-item>
              <v-list-item-title>
                <DesktopCompanyEdit
                  :endpoint="`${baseUrl}/company`"
                  :item="item"
                  @response="getDataFromApi"
                />
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                <DesktopDevice
                  :endpoint="`${baseUrl}/device`"
                  :item="item"
                  @response="getDataFromApi"
                />
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                <DesktopBatchFileCreator
                  :endpoint="`${baseUrl}/device`"
                  :devices="item.devices"
                />
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                <DesktopCompanyDelete
                  :id="item.id"
                  :endpoint="`${baseUrl}/company`"
                  @response="getDataFromApi"
                />
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>
  </span>
</template>

<script>
let date = new Date();

let d = date.getDate();
let m = (date.getMonth() + 1).toString().padStart(2, "0");
let y = date.getFullYear();
let currentDate = y + "-" + m + "-" + d;

export default {
  props: ["baseUrl"],
  data: () => ({
    currentDate,
    filters: {},
    options: {},
    loading: false,
    response: "",
    companies: [],
    errors: [],
    headers: [
      {
        text: "Ref #",
        value: "id",
      },
      {
        text: "Company Name",
        value: "name",
      },
      {
        text: "Contact Person Name",
        value: "contact_person_name",
      },
      {
        text: "Phone Number",
        value: "number",
      },
      {
        text: "Email",
        value: "email",
      },
      {
        text: "Location",
        value: "location",
      },
      {
        text: "Total Devices",
        value: "devices_count",
      },
      {
        text: "Created At",
        value: "created_at",
      },
      {
        text: "Action",
        align: "center",
        sortable: false,
        value: "options",
      },
    ],
    componentKey: 1,
  }),

  async created() {
    this.getDataFromApi();
  },
  mounted() {},
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    getRandomId() {
      return ++this.componentKey;
    },
    async getDataFromApi() {
      this.loading = true;
      try {
        let { data } = await this.$axios.get(`${this.baseUrl}/company`);
        this.companies = data.data;
      } catch (error) {
        console.error("Failed to fetch companies:", error);
        this.companies = [];
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
