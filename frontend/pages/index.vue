<template>
  <div v-if="can(`dashboard_access`)">
    <div v-if="!loading">
      <v-dialog
        v-model="dialogGeneralreport"
        :fullscreen="false"
        max-width="80%"
      >
        <v-card>
          <!-- <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn class="error" small @click="closeDialogGeneralreport">
                Close <span class="mdi mdi-close-circle"></span>
              </v-btn>
            </v-card-actions> -->
          <v-card-text style="padding: 0px">
            <v-container style="max-width: 100%; padding: 0px">
              <v-row>
                <v-col cols="12">
                  <iframe
                    v-if="iframeDisplay"
                    :src="iframeUrl"
                    frameborder="0"
                  ></iframe>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
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
        <v-col
          v-for="(i, index) in items"
          :key="index"
          xs="12"
          sm="12"
          cols="12"
          md="4"
          lg="4"
          xl="3"
        >
          <div class="card p-2" :class="i.color" style="min-height: 168px">
            <div class="card-statistic-3">
              <div class="card-icon card-icon-large">
                <i :class="i.icon"></i>
              </div>
              <div class="card-content">
                <h4 class="card-title text-capitalize">{{ i.title }}</h4>
                <span class="data-1"> {{ i.value }}</span>
                <p class="mb-0 text-sm">
                  <span
                    class="handcursor font-11"
                    @click="showDialogGeneralreport(i.link)"
                  >
                    <span class="mr-2">
                      <v-icon dark small>mdi-arrow-right</v-icon>
                    </span>
                    <span class="text-nowrap regular-font"
                      >View General Report</span
                    >
                  </span>
                </p>
                <p class="mb-0 text-sm">
                  <span
                    class="handcursor font-11"
                    @click="showDialogGeneralreport(i.multi_in_out)"
                  >
                    <span class="mr-2">
                      <v-icon dark small>mdi-arrow-right</v-icon>
                    </span>
                    <span class="text-nowrap regular-font"
                      >View Multi In/Out Report</span
                    >
                  </span>
                </p>
              </div>
            </div>
          </div>
        </v-col>
        <v-col xs="12" sm="12" cols="12" md="4" lg="4" xl="3">
          <div class="card p-2 l-bg-purple-dark" style="min-height: 168px">
            <div class="card-statistic-3">
              <div class="card-icon card-icon-large">
                <i class="fas mdi mdi-account-tie"></i>
              </div>
              <div class="card-content text-center">
                <h4 class="card-title text-capitalize">Total Employees</h4>
                <span class="data-1" style="font-size: 50px">
                  {{ total_employees_count_display }}</span
                >
              </div>
            </div>
          </div>
        </v-col>
        <v-col xs="12" sm="12" cols="12" md="4" lg="4" xl="3">
          <div
            class="card p-2"
            style="min-height: 168px; background-color: rgb(193 14 14 / 6%)"
          >
            <div class="card-statistic-3" style="padding: 0px">
              <div class="card-content">
                <div class="card-icon card-icon-large">
                  <i class="mdi mdi-chart-arc d-lg-none d-xl-none"></i>
                </div>
                <PIE2 :items="items2" />
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" md="12" xl="12">
            <DailyLogs />
        </v-col>
        <!-- <v-col cols="12" md="4" xl="4">
          <v-card flat class="w-100">
          <PIE :items="items" />
          </v-card>
        </v-col> -->
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
      cards: [
        {
          id: 1,
          title: "Card 1",
          description: "This is the description of Card 1",
          image: "path/to/image1.jpg",
        },
        {
          id: 2,
          title: "Card 2",
          description: "This is the description of Card 2",
          image: "path/to/image2.jpg",
        },
        {
          id: 3,
          title: "Card 3",
          description: "This is the description of Card 3",
          image: "path/to/image3.jpg",
        },
      ],
      currentValue: 0,
      targetValue: 100, // The target value for the animation
      animationDuration: 4000, // Animation duration in milliseconds
      total_employees_count_api: "",
      total_employees_count_display: "0",
      first_login_auth: 1,
      loading: true,
      dialogGeneralreport: false,
      generalreportIframeSrc: "",
      iframeDisplay: false,
      logs: [],
      iframeUrl: "",
      items2: [],
      online_device_count: 0,
      offline_device_count: 0,
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
      options: {},
      series: [44, 55, 41, 17, 15],
    };
  },
  created() {
    this.updateCartcart2();
    this.initialize();
    this.first_login_auth = this.$auth.user.first_login;

    // this.generalreportIframeSrc=  this.$axios.defaults.baseURL +
    //     "daily?company_id=8&status=SA&daily_date=2023-05-31&department_id=-1&report_type=Daily",
  },
  mounted() {
    this.updateCartcart2();

    this.animateNumberEmployeesCount();
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
    animateNumberEmployeesCount() {
      const startTimestamp = Date.now();
      const startValue = this.currentValue;

      const animate = () => {
        const elapsed = Date.now() - startTimestamp;

        if (elapsed >= this.animationDuration) {
          // Animation finished, set the final value
          this.total_employees_count_display = this.total_employees_count_api;
        } else {
          // Calculate the animated value based on time and start/target values
          const progress = elapsed / this.animationDuration;
          this.total_employees_count_display = Math.round(
            startValue +
              (this.total_employees_count_api - startValue) * progress
          );

          // Request next animation frame
          requestAnimationFrame(animate);
        }
      };

      // Start the animation
      requestAnimationFrame(animate);
    },

    updateLink(url) {
      if (
        this.$axios.defaults.baseURL !=
        "https://stagingbackend.ideahrms.com/api"
      ) {
        url = url.replace(
          "https://stagingbackend.ideahrms.com/api",
          this.$axios.defaults.baseURL
        );
      }

      return url;
    },
    closeDialogGeneralreport() {
      this.iframeDisplay = false;
      this.dialogGeneralreport = false;
      //this.iframeUrl = "#";
    },
    showDialogGeneralreport(iframeUrl) {
      this.iframeDisplay = false;
      this.iframeUrl = this.updateLink(iframeUrl);
      this.dialogGeneralreport = true;

      this.iframeDisplay = true;
    },
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
    updateCartcart2() {
      this.$axios
        .get(`getdevicesstatuscount/${this.$auth.user.company.id}`)
        .then((res) => {
          //this.logs = res.data;
          this.online_device_count = res.data.online;
          this.offline_device_count = res.data.offline;
          console.log("res", res);

          this.items2 = [
            {
              title: "Active",
              value: res.data.online,
            },
            {
              title: "Offline",
              value: res.data.offline,
            },
          ];
        });
    },
    initialize() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        this.items = data;

        this.total_employees_count_api = data[0].total_employees_count;

        if (this.items.length > 0) {
          this.loading = false;
        }
      });
    },
  },
};
</script>
<style scoped src="@/assets/dashboard.css"></style>
