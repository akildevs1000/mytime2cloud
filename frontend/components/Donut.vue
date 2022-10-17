<template>
  <div>
    <v-row style="height: 347px;">
      <v-col md="10">
        <div id="donut"></div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["items"],
  head() {
    return {
      script: [{ src: "https://cdn.jsdelivr.net/npm/apexcharts", body: true }]
    };
  },

  data() {
    return {
      options: {
        title: {
          text: "DAILY ATTENDANCE REPORT",
          align: "left"
        },
        colors: ["#A24FDD", "#6DFCCA", "#E78956", "#3A95D9"],

        series: [],
        chart: {
          type: "pie"
        },
        labels: [],
        dataLabels: {
          dropShadow: {
            blur: 3,
            opacity: 0.8
          }
        },
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: 200
              },
              legend: {
                position: "bottom"
              }
            }
          }
        ]
      }
    };
  },
  created() {
    this.getTitle();
    this.getValue();
  },
  mounted() {
    var chart = new ApexCharts(document.querySelector("#donut"), this.options);
    chart.render();
  },
  methods: {
    getTitle() {
      this.options.labels = this.items.map(e => e.title);
    },
    getValue() {
      this.options.series = this.items.map(e => e.value);
      console.log(this.options.series);
    }
  }
};
</script>
