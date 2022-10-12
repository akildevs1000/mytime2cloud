<template>
  <div v-if="!loading">
    <v-row>
      <v-col md="12">
        <!-- <v-alert
          v-if="first_login && first_login_auth"
          outlined
          class="error lighten-1"
          dark
          dense
          prominent
        >
          <v-icon style="margin-top: -3px" size="22">
            mdi-alert-circle-outline</v-icon
          >
          <label class="text-white"
            >For security reasons you need to change your password after first
            login.</label
          >
          <nuxt-link class="white--text" to="/setting">
            <u>click here</u>
          </nuxt-link>
        </v-alert> -->
      </v-col>
      <div class="col-xl-3 col-lg-6 text-uppercase" v-for="(i, index) in items" :key="index">
        <div class="card p-2" :class="i.color">
          <div class="card-statistic-3">
            <div class="card-icon card-icon-large ">
              <i :class="i.icon"></i>
            </div>
            <div class="card-content">
              <h4 class="card-title text-capitalize">{{ i.title }} </h4>
              <span class="data-1"> {{ i.value }}</span>
              <p class="mb-0 text-sm">
                <span class="mr-2"><v-icon dark small>mdi-arrow-right</v-icon></span>
                <a class="text-nowrap text-white" target="_blank" :href="i.link">
                  <span class="text-nowrap">View Report</span>
                </a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </v-row>

    <v-row>
      <v-col md="8" cols="12" sm="12">
        <v-card elevation="0">
          <ComboChart />
        </v-card>
      </v-col>
      <v-col md="4" cols="12" sm="12">
        <v-card elevation="0">
          <Donut />
        </v-card>
      </v-col>
    </v-row>
    <v-row>
       <v-col cols="12">
        <DailySummary />
      </v-col>
    </v-row>
    <v-row class="mt-5">
     
      <v-col md="4" cols="12" sm="12">
        <v-card elevation="0">
          <v-list three-line>
            <template v-for="(item, index) in devices">
              <v-subheader v-if="item.header" :key="item.header">
                <h5>{{ item.header }}</h5>
              </v-subheader>

              <v-divider v-else-if="item.divider" :key="index" :inset="item.inset"></v-divider>

              <v-list-item v-else :key="item.title">
                <v-list-item-avatar>
                  <v-img :src="item.avatar"></v-img>
                </v-list-item-avatar>

                <v-list-item-content>
                  <v-list-item-title v-html="item.title"></v-list-item-title>
                  <v-list-item-subtitle v-html="item.subtitle"></v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </template>
          </v-list>
        </v-card>
      </v-col>

      <v-col md="4" cols="12" sm="12">
        <v-card elevation="0">
          <v-list three-line>
            <template v-for="(item, index) in polices">
              <v-subheader v-if="item.header" :key="item.header">
                <h5>{{ item.header }}</h5>
              </v-subheader>

              <v-divider v-else-if="item.divider" :key="index" :inset="item.inset"></v-divider>

              <v-list-item v-else :key="item.title">
                <v-list-item-content>
                  <v-list-item-title v-html="item.title"></v-list-item-title>
                  <v-list-item-subtitle v-html="item.subtitle"></v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </template>
          </v-list>
        </v-card>
      </v-col>
      <v-col md="4" cols="12" sm="12">
        <v-card elevation="0">
          <v-list three-line>
            <template v-for="(item, index) in anc">
              <v-subheader v-if="item.header" :key="item.header">
                <h5>{{ item.header }}</h5>
              </v-subheader>

              <v-divider v-else-if="item.divider" :key="index" :inset="item.inset"></v-divider>

              <v-list-item v-else :key="item.title">
                <v-list-item-content>
                  <v-list-item-title v-html="item.title"></v-list-item-title>
                  <v-list-item-subtitle v-html="item.subtitle"></v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </template>
          </v-list>
        </v-card>
      </v-col>



      <!-- <v-col md="12" cols="12" sm="12">
        <v-card elevation="0">
          <v-data-table :headers="headers" :items="desserts" class="elevation-1">
            <template v-slot:item.calories="{ item }">
              <v-avatar>
                <img src="https://cdn.vuetifyjs.com/images/lists/1.jpg" alt="John" />
              </v-avatar>
            </template>
          </v-data-table>
        </v-card>
      </v-col> -->
    </v-row>
  </div>
  <Preloader v-else />
</template>

<script>
import AreaChart from "../components/AreaChart.vue";
import Preloader from "../components/Preloader.vue";
import Donut from "../components/Donut.vue";
import ComboChart from "../components/ComboChart.vue";
export default {
  head() {
    return {
      script: [{ src: "https://cdn.jsdelivr.net/npm/apexcharts", body: true }]
    };
  },
  data() {
    return {
      first_login_auth: 1,
      loading: true,
      devices: [
        { header: "Devices" },
        {
          avatar:
            "https://www.shopkees.com/images/1000/9242668587_1599380694.png",
          title: "Main Door",
          subtitle: `<span class="text--primary">X-566</span> &mdash; this device use for main entrance door`
        },
        { divider: true, inset: true },
        {
          avatar:
            "https://4.imimg.com/data4/FV/IJ/MY-9999211/face-recognition-devices-500x500.jpg",
          title: "Hall Door",
          subtitle: `<span class="text--primary">X-765</span> &mdash; this device use for main entrance door`
        },
        { divider: true, inset: true },
        {
          avatar:
            "https://lenvica.b-cdn.net/wp-content/uploads/2020/05/SpeedFace-V5LTD-Face-Palm-and-Body-Temperature-Terminal-1.jpg",
          title: "Conference Area",
          subtitle: `<span class="text--primary">X-685</span> &mdash; this device use for main entrance door`
        },
        { divider: true, inset: true },
        {
          avatar:
            "https://www.scanmaxai.com/data/watermark/20191217/5df889f53c35e.jpg",
          title: "Out Door",
          subtitle: `<span class="text--primary">X-896</span> &mdash; this device use for main entrance door`
        },
        { divider: true, inset: true },
        {
          avatar:
            "https://www.shopkees.com/images/1000/9242668587_1599380694.png",
          title: "Main Door",
          subtitle: `<span class="text--primary">X-606</span> &mdash; this device use for main entrance door`
        }
      ],
      polices: [
        { header: "Polices" },
        {
          title: "Computer Usage?",
          subtitle: `<span class="text--primary">Make Your Own Printer Paper</span> &mdash;"" - Employees will be provided with a block of wood to chew, a large hunk of metal for a press, and a sunlamp.`
        },
        { divider: true, inset: true },
        {
          title:
            'Special Days <span class="grey--text text--lighten-1"></span>',
          subtitle: `<span class="text--primary">Friday is Pajama Day</span> &mdash; Bring Your Dog to Work Day.`
        },
        { divider: true, inset: true },
        {
          title: "Cutting Expenses and Work Hour",
          subtitle:
            '<span class="text--primary">This Company</span> &mdash; The company is saving money by installing dehydrated water coolers.'
        },
        { divider: true, inset: true },
        {
          title: "Security and Employee Testing",
          subtitle:
            '<span class="text--primary">Weekly body armor testing.</span> &mdash; Random breathalyzers at 2:00pm everyday.'
        },
        { divider: true, inset: true },
        {
          title: "New Employees",
          subtitle:
            '<span class="text--primary">Exam</span> &mdash; Prostate exams are required for all new hires.'
        }
      ],
      anc: [
        { header: "Announcement" },
        {
          title: "Annual Party",
          subtitle: `<span class="text--primary">Ali Connors</span> &mdash; I'll be in your neighborhood doing errands this weekend. Do you want to hang out?`
        },
        { divider: true, inset: true },
        {
          title:
            'Eid Holiday <span class="grey--text text--lighten-1">08-Jun-22</span>',
          subtitle: `<span class="text--primary">to Alex, Scott, Jennifer</span> &mdash; Wish I could come, but I'm out of town this weekend.`
        },
        { divider: true, inset: true },
        {
          title:
            'Summer Holiday <span class="grey--text text--lighten-1">08-Jun-22</span>',
          subtitle:
            '<span class="text--primary">Sandra Adams</span> &mdash; Do you have Paris recommendations? Have you ever been?'
        },
        { divider: true, inset: true },
        {
          title:
            'Independent Holiday <span class="grey--text text--lighten-1">08-Oct-22</span>',
          subtitle:
            '<span class="text--primary">Trevor Hansen</span> &mdash; Have any ideas about what we should get Heidi for her birthday?'
        },
        { divider: true, inset: true },
        {
          title:
            'Eid Holiday <span class="grey--text text--lighten-1">08-Jun-22</span>',
          subtitle:
            '<span class="text--primary">Britta Holt</span> &mdash; We should eat this: Grate, Squash, Corn, and tomatillo Tacos.'
        }
      ],
      headers: [
        {
          text: "ID",
          align: "start",
          sortable: false,
          value: "name"
        },
        { text: "Calories", value: "calories" },
        { text: "Fat (g)", value: "fat" },
        { text: "Carbs (g)", value: "carbs" },
        { text: "Protein (g)", value: "protein" },
        { text: "Iron (%)", value: "iron" }
      ],
      desserts: [
        {
          name: "ID7865",
          calories: 159,
          fat: 6,
          carbs: 24,
          protein: 4,
          iron: "1%"
        },
        {
          name: "ID7865",
          calories: 237,
          fat: 9,
          carbs: 37,
          protein: 4.3,
          iron: "1%"
        },
        {
          name: "ID7865",
          calories: 262,
          fat: 16,
          carbs: 23,
          protein: 6,
          iron: "7%"
        },
        {
          name: "ID7865",
          calories: 305,
          fat: 3.7,
          carbs: 67,
          protein: 4.3,
          iron: "8%"
        },
        {
          name: "ID7865",
          calories: 356,
          fat: 16,
          carbs: 49,
          protein: 3.9,
          iron: "16%"
        },
        {
          name: "ID7865",
          calories: 375,
          fat: 0,
          carbs: 94,
          protein: 0,
          iron: "0%"
        },
        {
          name: "ID7865",
          calories: 392,
          fat: 0.2,
          carbs: 98,
          protein: 0,
          iron: "2%"
        },
        {
          name: "ID7865",
          calories: 408,
          fat: 3.2,
          carbs: 87,
          protein: 6.5,
          iron: "45%"
        },
        {
          name: "ID7865",
          calories: 452,
          fat: 25,
          carbs: 51,
          protein: 4.9,
          iron: "22%"
        },
        {
          name: "ID7865",
          calories: 518,
          fat: 26,
          carbs: 65,
          protein: 7,
          iron: "6%"
        }
      ],
      total_items: [],
      items_by_cities: [],
      test_headers: [
        {
          text: "Customer",
          align: "left",
          sortable: false,
          value: "company_name"
        },
        {
          text: "Order Total",
          align: "left",
          sortable: false,
          value: "order_total"
        }
      ],
      orders: "",
      products: "",
      customers: "",
      daily_orders: "",
      weekly_orders: "",
      monthly_orders: "",
      items: [],
      chartData: [
        ["Month", "On Time", "Absence", "Lates"],
        ["Apr", 33, 4, 7],
        ["Mar", 17, 6, 3],
        ["Feb", 41, 9, 1]
      ],
      chartOptions: {
        chart: {
          title: "Company Performance",
          subtitle: "Sales, Expenses, and Profit: 2014-2017"
        }
      },
      BublechartOptions: {
        colorAxis: { colors: ["yellow", "red"] }
      }
    };
  },
  created() {
    this.initialize();
    this.first_login_auth = this.$auth.user.first_login;
  },
  computed: {
    first_login() {
      return this.$store.state.first_login;
    }
  },
  filters: {
    get_decimal_value: function (value) {
      if (!value) return "";
      return (Math.round(value * 100) / 100).toFixed(2);
    },
    get_comma_seperator: function (x) {
      if (!x) return "";
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
  },
  methods: {
    getColor(calories) {
      if (calories > 400) return "red";
      else if (calories > 200) return "orange";
      else return "green";
    },
    initialize() {
      let options = {
        company_id: this.$auth.user.company.id
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        this.items = data;

        if (this.items.length > 0) {
          this.loading = false;
        }
      });
    }
  },
  components: { AreaChart, Preloader, Donut, ComboChart }
};
</script>

<style scoped src="@/assets/dashtem.css">

</style>
