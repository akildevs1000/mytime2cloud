<template>
  <div id="pie"></div>
</template>

<script>
export default {
  props: ["items"],
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
  mounted() {
    this.options.labels = this.items.map(e => e.title);
    this.options.series = this.items.map(e => e.value);
    new ApexCharts(document.querySelector("#pie"), this.options).render();
  },
  methods: {
  }
};
</script>
