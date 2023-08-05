<template>
  <v-card class="mb-5 rounded-md" elevation="1">
    <v-toolbar class="rounded-md" color="background" dense flat dark>
      <v-toolbar-title
        ><span> {{ Model }} </span></v-toolbar-title
      >
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
      <v-spacer></v-spacer>

      <!-- <v-tooltip top color="primary">
        <template v-slot:activator="{ on, attrs }">
          <v-btn
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            v-bind="attrs"
            v-on="on"
            @click="changeChartType(`pie`)"
          >
            <v-icon color="white" class="ml-2" dark>mdi-chart-pie </v-icon>
          </v-btn>
        </template>
        <span>Pie</span>
      </v-tooltip>

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
            @click="changeChartType(`donut`)"
          >
            <v-icon color="white" class="ml-2" dark>mdi-chart-donut </v-icon>
          </v-btn>
        </template>
        <span>Donut</span>
      </v-tooltip> -->
      <v-tooltip top color="primary">
        <template v-slot:activator="{ on, attrs }">
          <v-btn
            to="/device"
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            v-bind="attrs"
            v-on="on"
          >
            <v-icon color="white" class="ml-2" dark>mdi mdi-eye-outline</v-icon>
          </v-btn>
        </template>
        <span>Device List</span>
      </v-tooltip>
    </v-toolbar>
    <div class="center-both" style="min-height: 300px">
      <PiePreloader v-if="loading" />
      <div v-else-if="!data.length">No record found</div>
      <div v-else id="DevicePieId"></div>
    </div>
  </v-card>
</template>
<script>
export default {
  data: () => ({
    Model: "Device Status",
    data: [],
    chartOptions: {
      title: {
        align: "center",
        margin: 0,
      },
      colors: ["#23bdb8", "#f48665", "#289cf5", "#8e4cf1"],

      series: [],
      chart: {
        width: 300, //200 //275
        type: "pie",
      },
      labels: [],
      // plotOptions: {
      //   pie: {
      //     startAngle: -90,
      //     endAngle: 270,
      //   },
      // },
      dataLabels: {
        enabled: true,
        style: {
          fontSize: "10px",
        },
      },
      legend: {
        show: true,
        fontSize: "10px",
      },
      responsive: [
        {
          breakpoint: 480,
          options: {
            chart: {
              width: 250, //200 //275
            },
            legend: {
              position: "bottom",
            },
          },
        },
      ],
    },
    loading: true,
  }),
  mounted() {
    this.getDataFromApi();
  },
  methods: {
    changeChartType(type) {
      this.chartOptions.chart.type = type;
      this.getDataFromApi();
    },
    getDataFromApi() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get(`count`, { params: options }).then(async ({ data }) => {
        this.loading = false;
        this.data = data = [
          {
            title: "Online",
            value: Math.floor(Math.random() * (20 - 1 + 1)) + 1,
          },
          {
            title: "Offline",
            value: Math.floor(Math.random() * (20 - 1 + 1)) + 1,
          },
        ];
        this.chartOptions.labels = await data.map((e) => e.title);
        this.chartOptions.series = await data.map((e) => e.value);
        this.loading = false;
        new ApexCharts(
          document.querySelector("#DevicePieId"),
          this.chartOptions
        ).render();
      });
    },
  },
};
</script>
<style scoped>
.center-both {
  height: 31vh; /* Adjust the height as needed */
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
