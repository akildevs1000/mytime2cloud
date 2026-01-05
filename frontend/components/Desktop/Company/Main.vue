<template>
  <span>
    <style scoped>
      .status-chip {
        font-size: 12px;
        height: 22px;
        padding: 0 10px;
        text-transform: capitalize;
        border-radius: 6px;
      }

      /* Active */
      .status-active {
        background-color: #e6f4ea !important;
        color: #2e7d32 !important;
      }

      /* Expired */
      .status-expired {
        background-color: #fdecea !important;
        color: #c62828 !important;
      }

      /* Suspended */
      .status-suspended {
        background-color: #fff4e5 !important;
        color: #ef6c00 !important;
      }

      /* Fallback */
      .status-unknown {
        background-color: #f0f0f0 !important;
        color: #616161 !important;
      }
    </style>
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
          <v-toolbar-title>Companies</v-toolbar-title>

          <v-spacer></v-spacer>

          <v-text-field
            v-model="search"
            @input="searchIt(search)"
            style="max-width: 250px"
            height="30px"
            class="custom-text-field-height pt-7 pr-3"
            color="black"
            outlined
            dense
            prepend-inner-icon="mdi-magnify"
            placeholder="Search"
          ></v-text-field>
          <DesktopCompanyCreate
            :endpoint="`${baseUrl}/company`"
            @response="getDataFromApi"
          />
        </v-toolbar>
      </template>
      <template v-slot:item.status="{ item }">
        <v-chip small class="status-chip" :class="statusClass(item.status)">
          {{ item.status }}
        </v-chip>
      </template>
      <template v-slot:item.expiry_date="{ item }">
        <span class="expiry-date">
          {{ formatExpiry(item.expiry_date) }}
        </span>
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
          <v-list width="130" dense>
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
                <DesktopCompanyGenerateLicenseKey
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
    search: "",
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

      { text: "Status", value: "status", align: "center" }, // 👈 add this

      {
        text: "Created At",
        value: "created_at",
      },
      {
        text: "Expiry",
        value: "expiry_date",
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
    formatExpiry(date) {
      if (!date) return "-";
      const d = new Date(date);
      return d
        .toLocaleDateString("en-GB", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        })
        .replace(/ /g, "-");
    },
    statusClass(status) {
      switch (status) {
        case "active":
          return "status-active";
        case "expired":
          return "status-expired";
        case "suspended":
          return "status-suspended";
        default:
          return "status-unknown";
      }
    },
    getRandomId() {
      return ++this.componentKey;
    },

    searchIt(e) {
      this.search = (e ?? "").trim();

      clearTimeout(this.debounceTimer);

      this.debounceTimer = setTimeout(() => {
        // reset page on new search
        this.page = 1;

        if (this.search.length === 0) {
          this.getDataFromApi();
        } else if (this.search.length > 1) {
          this.getDataFromApi(this.search);
        }
        // if length === 1 do nothing (your existing rule)
      }, 500);
    },

    async getDataFromApi(search = "") {
      this.loading = true;

      try {
        const params = {
          per_page: this.per_page,
          page: this.page,
        };

        // only send search if it is valid
        if (search && search.length > 1) {
          params.search = search;
        }

        const { data } = await this.$axios.get(`${this.baseUrl}/company`, {
          params,
        });

        // Laravel pagination structure
        this.companies = data.data || [];

        // OPTIONAL: if you want pagination meta:
        // this.total = data.total;
        // this.last_page = data.last_page;
      } catch (error) {
        console.error("Failed to fetch companies:", error);
        this.companies = [];
      } finally {
        this.loading = false;
      }
    },

    onPageChange(newPage) {
      this.page = newPage;

      // if currently searching, keep filtering
      if (this.search && this.search.length > 1) {
        this.getDataFromApi(this.search);
      } else {
        this.getDataFromApi();
      }
    },
  },
};
</script>
