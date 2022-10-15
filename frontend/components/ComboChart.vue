<template>
  <div>
    <v-row>
      <div id="comboChart" class="p-2"></div>
    </v-row>
  </div>
</template>

<script>
export default {
  // head() {
  //   return {
  //     script: [{ src: "https://cdn.jsdelivr.net/npm/apexcharts", body: true }]
  //     // script: [{ src: "~/plugins/apex.js", body: true }]
  //   };
  // },

  data() {
    return {
      days: [],
      options: {
        series: [
          {
            name: "Present",
            type: "column",
            data: [1.4, 2, 2.5, 1.5, 2.5, 2.8, 3.8, 4.6]
          },
          {
            name: "Absent",
            type: "column",
            data: [1.1, 3, 3.1, 4, 4.1, 4.9, 6.5, 8.5]
          },
          {
            name: "Early",
            type: "column",
            data: [20, 29, 37, 36, 44, 45, 50, 58]
          },
          {
            name: "Late",
            type: "line",
            data: [20, 29, 37, 36, 44, 45, 50, 58]
          }
        ],
        chart: {
          height: 350,
          type: "line",
          stacked: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: [1, 1, 1]
        },
        title: {
          text: "DAILY ATTENDANCE REPORT",
          align: "left",
          offsetX: 110
        },
        xaxis: {
          categories: []
        },
        yaxis: [
          {
            axisTicks: {
              show: true
            },
            axisBorder: {
              show: true,
              color: "#008FFB"
            },
            labels: {
              style: {
                colors: "#008FFB"
              }
            },
            // title: {
            //   text: "Income (thousand crores)",
            //   style: {
            //     color: "#008FFB"
            //   }
            // },
            tooltip: {
              enabled: true
            }
          },
          // {
          //   seriesName: "Income",
          //   opposite: true,
          //   axisTicks: {
          //     show: true
          //   },
          //   axisBorder: {
          //     show: true,
          //     color: "#00E396"
          //   },
          //   labels: {
          //     style: {
          //       colors: "#00E396"
          //     }
          //   },
          //   title: {
          //     text: "Operating Cashflow (thousand crores)",
          //     style: {
          //       color: "#00E396"
          //     }
          //   }
          // },
          {
            seriesName: "Revenue",
            opposite: true,
            axisTicks: {
              show: true
            },
            axisBorder: {
              show: true,
              color: "#FEB019"
            },
            labels: {
              style: {
                colors: "#FEB019"
              }
            },
            title: {
              text: "Revenue (thousand crores)",
              style: {
                color: "#FEB019"
              }
            }
          }
        ],
        tooltip: {
          fixed: {
            enabled: true,
            position: "topLeft", // topRight, topLeft, bottomRight, bottomLeft
            offsetY: 30,
            offsetX: 60
          }
        },
        legend: {
          horizontalAlign: "left",
          offsetX: 40
        }
      }
    };
  },
  mounted() {
    this.getDaysInCurrentMonth;

    var chart = new ApexCharts(
      document.querySelector("#comboChart"),
      this.options
    );
    chart.render();
  },
  computed: {
    getDaysInCurrentMonth() {
      const date = new Date();
      let lastDay = new Date(
        date.getFullYear(),
        date.getMonth() + 1,
        0
      ).getDate();
      for (let i = 1; i <= lastDay; i++) {
        this.options.xaxis.categories.push(i);
      }
    }
  },
  methods: {}
};
</script>
