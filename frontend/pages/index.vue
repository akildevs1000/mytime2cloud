<template>
  <div v-if="can(`dashboard_access`)">
    <div v-if="!loading">
      <v-row>
        <v-col md="12">
          <!-- <v-alert
          v-if="first_login && first_login_auth"
          outlined
          class="error lighten-1"
          dark
          dense
          prominent
        >
          <v-icon style="margin-top: -3px" size="22">
            mdi-alert-circle-outline</v-icon
          >
          <label class="text-white"
            >For security reasons you need to change your password after first
            login.</label
          >
          <nuxt-link class="white--text" to="/setting">
            <u>click here</u>
          </nuxt-link>
        </v-alert> -->
        </v-col>
        <div
          class="col-xl-3 col-lg-6 text-uppercase"
          v-for="(i, index) in items"
          :key="index"
        >
          <div class="card p-2" :class="i.color">
            <div class="card-statistic-3">
              <div class="card-icon card-icon-large">
                <i :class="i.icon"></i>
              </div>
              <div class="card-content">
                <h4 class="card-title text-capitalize">{{ i.title }}</h4>
                <span class="data-1"> {{ i.value }}</span>
                <p class="mb-0 text-sm">
                  <span class="mr-2">
                    <v-icon dark small>mdi-arrow-right</v-icon>
                  </span>
                  <a
                    class="text-nowrap text-white"
                    target="_blank"
                    :href="i.link"
                  >
                    <span class="text-nowrap">View General Report</span>
                  </a>
                </p>
                <p class="mb-0 text-sm">
                  <span class="mr-2">
                    <v-icon dark small>mdi-arrow-right</v-icon>
                  </span>
                  <a
                    class="text-nowrap text-white"
                    target="_blank"
                    :href="i.multi_in_out"
                  >
                    <span class="text-nowrap">View Multi In/Out Report</span>
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </v-row>

      <v-row>
        <v-col cols="12" md="8" xl="8">
          <v-card flat>
            <DailyLogs />
          </v-card>
        </v-col>
        <v-col cols="12" md="4" xl="4">
          <v-card flat>
            <PIE :items="items" />
          </v-card>
        </v-col>
      </v-row>

      <v-row class="mt-4">
        <v-col md="12" cols="12" sm="12">
          <v-card elevation="0">
            <ComboChart />
          </v-card>
        </v-col>
      </v-row>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data() {
    return {
      first_login_auth: 1,
      loading: true,

      logs: [],

      total_items: [],
      items_by_cities: [],
      test_headers: [
        {
          text: "Customer",
          align: "left",
          sortable: false,
          value: "company_name",
        },
        {
          text: "Order Total",
          align: "left",
          sortable: false,
          value: "order_total",
        },
      ],
      orders: "",
      products: "",
      customers: "",
      daily_orders: "",
      weekly_orders: "",
      monthly_orders: "",
      items: [],
      chartData: [
        ["Month", "On Time", "Absence", "Lates"],
        ["Apr", 33, 4, 7],
        ["Mar", 17, 6, 3],
        ["Feb", 41, 9, 1],
      ],
      chartOptions: {
        chart: {
          title: "Company Performance",
          subtitle: "Sales, Expenses, and Profit: 2014-2017",
        },
      },
      BublechartOptions: {
        colorAxis: { colors: ["yellow", "red"] },
      },
    };
  },
  created() {
    this.initialize();
    this.first_login_auth = this.$auth.user.first_login;
  },
  computed: {
    first_login() {
      return this.$store.state.first_login;
    },
  },
  filters: {
    get_decimal_value: function (value) {
      if (!value) return "";
      return (Math.round(value * 100) / 100).toFixed(2);
    },
    get_comma_seperator: function (x) {
      if (!x) return "";
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    can(per) {
      let { is_master, permissions: p } =
        this.$auth.user || this.$auth.user.permissions;

      if (p.some((e) => e == per) || is_master) return true;

      this.$router.push(`/attendance_report`);
    },

    getColor(calories) {
      if (calories > 400) return "red";
      else if (calories > 200) return "orange";
      else return "green";
    },
    initialize() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        this.items = data;

        if (this.items.length > 0) {
          this.loading = false;
        }
      });
    },
  },
};
</script>

<style scoped src="@/assets/dashtem.css"></style>
