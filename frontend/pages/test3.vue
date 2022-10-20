<template>
  <div>
    <!-- <GChart type="ColumnChart" :data="chartData" :options="chartOptions" />chart -->

    <div id="chart"></div>
  </div>
</template>

<script>
export default {
 

  data() {
    return {
      options: {
        series: [
          {
            name: "Desktops",
            data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
          }
        ],
        chart: {
          height: 350,
          type: "area",
          zoom: {
            enabled: false
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: "straight"
        },
        title: {
          text: "Product Trends by Month",
          align: "left"
        },
        grid: {
          row: {
            colors: ["#f3f3f3", "transparent"], // takes an array which will be repeated on columns
            opacity: 0.5
          }
        },
        xaxis: {
          categories: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep"
          ]
        }
      }
    };
  },
  mounted() {
    var chart = new ApexCharts(document.querySelector("#chart"), this.options);
    chart.render();
  }
};
</script>
