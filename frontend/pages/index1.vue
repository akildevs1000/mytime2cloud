<template>
  <div>
    <div class="row">
      <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
        <GChart type="ColumnChart" :data="chartData" />
      </div>
      <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
        <GChart type="ColumnChart" :data="chartData" />
      </div>
    </div>
    <v-row>
      <v-col cols="9">
        <v-row>
          <v-col cols="12"> </v-col>
          <v-col>
            <v-card>
              <div class="heading display-3 overline text-center">
                <b>Attendance Report</b>
              </div>
              <GChart type="BarChart" :data="chartData" :options="BublechartOptions"
                style="width: 800px; height: 350px" />
            </v-card>
          </v-col>
          <v-col>
            <v-card>
              <div class="heading display-3 overline text-center">
                <b>Attendance Report</b>
              </div>
              <GChart type="LineChart" :data="chartData" :options="BublechartOptions"
                style="width: 800px; height: 350px" />
            </v-card>
          </v-col>
          <v-col>
            <v-card>
              <div class="heading display-3 overline text-center">
                <b>Attendance Report</b>
              </div>
              <GChart type="BubbleChart" :data="chartData" :options="BublechartOptions"
                style="width: 800px; height: 350px" />
            </v-card>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="3">
        <v-row>
          <v-col cols="12">
            <v-card>
              <div class="heading display-3 overline text-center">
                <b>Timing Report</b>
              </div>
              <GChart type="PieChart" :data="chartData" />
            </v-card>
          </v-col>
          <v-col>
            <v-card class="no_print">
              <v-col cols="12">
                <v-list-item four-line>
                  <v-list-item-content>
                    <div class="heading display-3 overline mb-4 text-center">
                      <b>Employee Arrival Timing</b>
                    </div>

                    <v-col v-for="(i, idx) in items_by_cities" :key="idx" cols="12">
                      {{ i.title }}

                      <div style="margin-top: -10%" class="text-right">
                        <v-chip small color="error">{{ i.value }}</v-chip>
                      </div>
                    </v-col>
                  </v-list-item-content>
                </v-list-item>
              </v-col>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  data() {
    return {
      total_items: [],
      items_by_cities: [],
      test_headers: [
        {
          text: "Customer",
          align: "left",
          sortable: false,
          value: "company_name",
        },
        {
          text: "Order Total",
          align: "left",
          sortable: false,
          value: "order_total",
        },
      ],

      orders: "",
      products: "",
      customers: "",
      daily_orders: "",
      weekly_orders: "",
      monthly_orders: "",

      chartData: [
        ["Month", "On Time", "Absence", "Lates"],
        ["Apr", 33, 4, 7],
        ["Mar", 17, 6, 3],
        ["Feb", 41, 9, 1],
      ],
      chartOptions: {
        chart: {
          title: "Company Performance",
          subtitle: "Sales, Expenses, and Profit: 2014-2017",
        },
      },
      BublechartOptions: {
        colorAxis: { colors: ["yellow", "red"] },
      },
    };
  },
  created() {
    this.initialize();
  },
  filters: {
    get_decimal_value: function (value) {
      if (!value) return "";
      return (Math.round(value * 100) / 100).toFixed(2);
    },
    get_comma_seperator: function (x) {
      if (!x) return "";
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    async initialize() {
      (this.total_items = [
        {
          title: "TOTAL Employees",
          value: "723",
        },
        {
          title: "Payroll For Current Month",
          value: "5.2k",
        },
        {
          title: "Today Abense",
          value: "4",
        },
      ]),
        (this.items_by_cities = [
          {
            title: "Gamosaj",
            value: "3pm",
          },
          {
            title: "Vekorot",
            value: "9am",
          },
          {
            title: "Pinih",
            value: "12pm",
          },
          {
            title: "Cixobele",
            value: "10:30am",
          },
        ]),
        (this.payment_modes = [
          {
            title: "CASH ON DELIVER",
            value: 9.5,
            color: "success",
          },
          {
            title: "EASY PAISA",
            value: 3.2,
            color: "warning",
          },
          {
            title: "JAZZ CASH",
            value: 7.2,
            color: "info",
          },
          {
            title: "DEBIT CARD",
            value: 1.2,
            color: "error",
          },
        ]);
      this.order_by_devices = [
        {
          title: "DESKTOP",
          value: 3.2,
          color: "success",
        },
        {
          title: "MOBILE",
          value: 7.2,
          color: "warning",
        },
        {
          title: "POS",
          value: 1.2,
          color: "error",
        },
      ];
    },
  },
};
</script>
