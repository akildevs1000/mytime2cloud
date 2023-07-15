<template>
  <div v-if="data.length" id="element"></div>
  <NoRecordFound v-else :data="data" />
</template>

<script>
export default {
  data() {
    return {
      data: [],
      chartOptions: {
        title: {
          align: "center",
          margin: 0,
        },
        colors: ["#23bdb8", "#f48665", "#289cf5", "#8e4cf1"],

        series: [],
        chart: {
          width: 350, //200 //275
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
    this.loading = false;
    let options = {
      company_id: this.$auth.user.company.id,
      employee_id: this.$auth.user.employee.system_user_id,
    };
    this.$axios
      .get(`employee-statistics`, { params: options })
      .then(async ({ data }) => {
        this.data = data;
        this.chartOptions.labels = await data.map((e) => e.title);
        this.chartOptions.series = await data.map((e) => e.value);
        new ApexCharts(
          document.querySelector("#element"),
          this.chartOptions
        ).render();
      });

    this.loading = false;
  },
  methods: {},
};
</script>
