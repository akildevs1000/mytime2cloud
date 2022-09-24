<template>
  <div>
    <v-row>
      <v-col md="12">
        <v-alert v-if="first_login && first_login_auth" outlined class="error lighten-1" dark dense prominent
          border="left">
          <v-icon style="margin-top: -3px" size="22">
            mdi-alert-circle-outline</v-icon>
          <label>For security reasons you need to change your password after first
            login.</label>
          <nuxt-link class="white--text" to="/setting">
            <u>click here</u>
          </nuxt-link>
        </v-alert>
      </v-col>

      <v-col cols="12" sm="12" md="12">
        <v-row>
          <v-col cols="12" sm="6" md="4" v-for="(i, index) in items" :key="index">
            <v-card :style="`border-left: 5px solid #${i.border_color}`">
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4">
                    {{ i.title }}
                  </div>
                  <v-list-item-title class="text-h5 mb-1">
                    {{ i.value }}
                  </v-list-item-title>
                </v-list-item-content>

                <v-list-item-avatar size="60" :class="i.color">
                  <v-icon dark> {{ i.icon }}</v-icon>
                </v-list-item-avatar>
              </v-list-item>
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="12" sm="12" md="8">
        <v-row>
          <v-col cols="12">
            <v-card>
              <div class="heading display-3 overline text-center">
                <b>Daily Attendance Report</b>
              </div>
              <GChart type="ColumnChart" :data="chartData" />
            </v-card>
          </v-col>

          <v-col cols="12">
            <v-card>
              <div class="heading display-3 overline text-center">
                <b>Monthly Attendance Report</b>
              </div>
              <GChart type="BarChart" :data="barChartData" style="height: 500px; max-width: 1200px" />
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="12" sm="12" md="4">
        <v-row>
          <v-col cols="12">
            <v-card>
              <GChart type="PieChart" :data="pieChartData" />
            </v-card>
          </v-col>
          <v-col cols="12">
            <Logs />
          </v-col>
          <v-col cols="12">
            <Activity class="mt-2" />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>

export default {
  data() {
    return {
      // items: [
      //   {
      //     color: "#1F7087",
      //     src: "https://cdn.vuetifyjs.com/images/cards/foster.jpg",
      //     title: "Supermodel",
      //     artist: "Foster the People"
      //   },
      //   {
      //     color: "#952175",
      //     src: "https://cdn.vuetifyjs.com/images/cards/halcyon.png",
      //     title: "Halcyon Days",
      //     artist: "Ellie Goulding"
      //   }
      // ],
      pieChartData: [
        ["Total Employee", "Present", "Absence", "Lates"],

        ["Total Employee", 33, 4, 7],
        ["Total Employee", 17, 6, 3],
        ["Total Employee", 41, 9, 1],
      ],
      // pieChartData: [
      //   [
      //     "Total Employee",
      //     "Scheduled Employees",
      //     "Present",
      //     "Absence",
      //     "Lates",
      //     "On Leave",
      //     "Late Coming"
      //   ],
      //   [33, 4, 7, 33, 4, 7, 3],
      //   [33, 4, 7, 33, 4, 7, 3],
      //   [33, 4, 7, 33, 4, 7, 3],
      //   [33, 4, 7, 33, 4, 7, 3],
      //   [33, 4, 7, 33, 4, 7, 3],
      //   [33, 4, 7, 33, 4, 7, 3]
      // ],
      barChartData: [
        ["Month", "Present", "Absence", "Lates"],
        ["Jan", 33, 4, 7],
        ["Feb", 33, 4, 7],
        ["Mar", 17, 6, 3],
        ["Apr", 41, 9, 1],
        ["May", 41, 9, 1],
        ["Jun", 41, 9, 1],
        ["Jul", 41, 9, 1],
        ["Aug", 41, 9, 1],
        ["Sep", 41, 9, 1],
        ["Oct", 41, 9, 1],
        ["Nov", 41, 9, 1],
        ["Dec", 41, 9, 1],
      ],
      items: [
        {
          title: "Total Employee",
          value: "100",
          icon: "mdi-account",
          color: "primary",
          border_color: "4B7BEC",
        },
        {
          title: "Scheduled Employees",
          value: "75 / 100",
          icon: "mdi-history",
          color: "orange darken-3",
          border_color: "EF6C00",
        },
        {
          title: "Present",
          value: "75 / 100",
          icon: "mdi-check",
          color: "success",
          border_color: "00E676",
        },
        {
          title: "Absence",
          value: "20 / 100",
          icon: "mdi-cancel",
          color: "error",
          border_color: "DD2C00",
        },
        {
          title: "On Leave",
          value: "5 / 100",
          icon: "mdi-home",
          color: "blue-grey darken-1",
          border_color: "526C78",
        },
        {
          title: "Late Coming",
          value: "5 / 100",
          icon: "mdi-clock",
          color: "error",
          border_color: "DD2C00",
        },
      ],
      chartData: [
        ["Date", "Present", "Absence", "Lates"],
        [1, 33, 4, 7],
        [2, 17, 6, 3],
        [3, 41, 9, 1],
        [4, 41, 9, 1],
        [5, 41, 9, 1],
        [6, 41, 9, 1],
        [7, 41, 9, 1],
        [8, 41, 9, 1],
        [9, 41, 9, 1],
        [10, 41, 9, 1],
        [11, 33, 4, 7],
        [12, 17, 6, 3],
        [13, 41, 9, 1],
        [14, 41, 9, 1],
        [15, 41, 9, 1],
        [16, 41, 9, 1],
        [17, 41, 9, 1],
        [18, 41, 9, 1],
        [19, 41, 9, 1],
        [20, 41, 9, 1],
        [21, 33, 4, 7],
        [22, 17, 6, 3],
        [23, 41, 9, 1],
        [24, 41, 9, 1],
        [25, 41, 9, 1],
        [26, 41, 9, 1],
        [27, 41, 9, 1],
        [28, 41, 9, 1],
        [29, 41, 9, 1],
        [30, 41, 9, 1],
      ],
      headers: [
        { text: "UserID", value: "UserID" },
        { text: "LogTime", value: "LogTime" },
        { text: "DeviceID", value: "DeviceID" },
        { text: "SerialNumber", value: "SerialNumber" },
      ],
      data: [],
      first_login_auth: 1,
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
  methods: {
    initialize() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        // this.items = data;
      });
    },
  },
};
</script>
