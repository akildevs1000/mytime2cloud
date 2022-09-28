<template>
  <div>
    <v-row>
      <v-col md="10">
        <div id="donut"></div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  head() {
    return {
      script: [{ src: "https://cdn.jsdelivr.net/npm/apexcharts", body: true }]
    };
  },

  data() {
    return {
      options: {
        series: [44, 55, 41, 17, 15],
        chart: {
          type: "donut"
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
  mounted() {
    var chart = new ApexCharts(document.querySelector("#donut"), this.options);
    chart.render();
  }
};

var options = {
  series: [44, 55, 41, 17, 15],
  chart: {
    type: "donut"
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
};
</script>
