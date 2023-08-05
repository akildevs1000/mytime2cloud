<template>
  <v-card class="mb-5 rounded-md" elevation="1">
    <v-toolbar class="rounded-md" color="background" dense flat dark>
      <v-toolbar-title
        ><span> {{ Model }} List </span></v-toolbar-title
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
      <v-tooltip top color="primary">
        <template v-slot:activator="{ on, attrs }">
          <v-btn
            to="/devicelogs"
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
        <span>Raw Log List</span>
      </v-tooltip>
    </v-toolbar>
    <div class="center-both" style="min-height: 300px">
      <FacePreloader v-if="loading" />
      <div v-else-if="!data.length">No record found</div>
      <div v-else style="height: 160px; width: 300px">
        <v-carousel
          hide-delimiter-background
          hide-delimiters
          :height="carouselHeight"
        >
          <v-carousel-item v-for="(item, index) in items" :key="index">
            <div
              style="
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
              "
            >
              <v-img
                :src="item.image"
                :alt="item.alt"
                style="width: 150px; object-fit: contain; border-radius: 50%"
              />
              <div style="margin-top: 10px; text-align: center">
                <span>Francis Gill</span>
              </div>
            </div>
          </v-carousel-item>
        </v-carousel>
      </div>
    </div>
  </v-card>
</template>
<script>
export default {
  data: () => ({
    carouselWidth: 250,
    carouselHeight: 200, // You can adjust this height as needed
    Model: "RealTime Log",
    data: [],
    items: [
      {
        image:
          "https://th.bing.com/th/id/R.b37449e1b72e11ff5dd8107308207fd3?rik=vb9G3NWALO1Hdw&pid=ImgRaw&r=0",
        alt: "Image 1",
      },
      {
        image:
          "https://th.bing.com/th/id/R.b37449e1b72e11ff5dd8107308207fd3?rik=vb9G3NWALO1Hdw&pid=ImgRaw&r=0",
        alt: "Image 2",
      },
      {
        image:
          "https://th.bing.com/th/id/R.b37449e1b72e11ff5dd8107308207fd3?rik=vb9G3NWALO1Hdw&pid=ImgRaw&r=0",
        alt: "Image 3",
      },
    ],
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
    getDataFromApi() {
      this.loading = true;
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get(`count`, { params: options }).then(async ({ data }) => {
        this.loading = false;
        this.data = data = [
          {
            title: "Today Summary",
            value: Math.floor(Math.random() * (20 - 1 + 1)) + 1,
          },
          {
            title: "Today Present",
            value: Math.floor(Math.random() * (20 - 1 + 1)) + 1,
          },
          {
            title: "Today Missing",
            value: Math.floor(Math.random() * (20 - 1 + 1)) + 1,
          },
        ];

        this.loading = false;
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
