<template>
  <div style="padding: 0px; width: 100%">
    <div id="pie2"></div>
    <div
      v-if="totalCount == 0"
      style="
        padding: 0px;
        margin: auto;
        text-align: center;
        vertical-align: middle;
        height: 300px;
        padding-top: 36%;
      "
    >
      No Data is available
    </div>
  </div>
</template>

<script>
export default {
  props: ["items"],
  data() {
    return {
      //   items: [
      //     { title: "Title1", value: 20 },
      //     { title: "Title2", value: 30 },
      //     { title: "Title3", value: 40 },
      //     { title: "Title4", value: 50 },
      //   ],
      totalCount: 0,
      options: {
        noData: {
          text: "There's no data",
          align: "center",
          verticalAlign: "middle",
          offsetX: 0,
          offsetY: 0,
        },
        title: {
          text: "Visitors",
          align: "left",
          margin: 0,
        },
        colors: ["#009d00", "#ff0000"],

        series: [],
        chart: {
          width: "100%", //200 //275
          type: "donut",
          height: "auto",
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
              chart: {},
              legend: {
                position: "bottom",
              },
            },
          },
        ],
      },
    };
  },
  mounted() {
    try {
      this.items.forEach((element) => {
        totalCount += element.value;
      });

      this.options.labels = this.items.map((e) => e.title);
      this.options.series = this.items.map((e) => e.value);
      new ApexCharts(document.querySelector("#pie2"), this.options).render();
    } catch (error) {}
  },
  methods: {},
};
</script>

<style scoped>
/* .apexcharts-legend-series {
    margin: 0px 100px 2px 0px !important;
  } */
/* #pie .apexcharts-legend-series {
    margin: 0px 50px 2px 0px !important;
  } */

/* foreignObject {
    max-width: 280px !important;
  } */
#pie .apexcharts-legend-series {
  margin: 0px 50px 2px 0px !important;
}
</style>
