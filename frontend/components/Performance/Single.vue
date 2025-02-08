<template>
  <client-only>
    <v-dialog v-model="dialog" width="1200px">
      <WidgetsClose left="1190" @click="dialog = false" />
      <template v-slot:activator="{ on, attrs }">
        <span v-bind="attrs" v-on="on"
          ><v-icon color="secondary" small>mdi-trophy</v-icon> View</span
        >
      </template>

      <v-card style="overflow: hidden">
        <v-container fluid>
          <v-row class="pa-2" v-if="item && item.employee">
            <v-col cols="8">
              <v-row>
                <v-col cols="12">
                  <v-card outlined>
                    <v-card-text>
                      <v-row>
                        <v-col>
                          <div class="d-flex align-start">
                            <v-avatar size="60">
                              <v-img
                                src="https://randomuser.me/api/portraits/women/45.jpg"
                                alt="Profile"
                              />
                            </v-avatar>
                            <div class="ml-5">
                              <div
                                class="primary--text body-1 py-0"
                                style="
                                  max-width: 150px;
                                  white-space: nowrap;
                                  overflow: hidden;
                                  text-overflow: ellipsis;
                                "
                              >
                                {{ item?.employee?.first_name }}
                              </div>

                              <div style="margin-top: 3px">
                                ID: {{ item.employee_id }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ item?.employee?.designation?.name || "---" }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ item?.employee?.branch?.branch_name || "---" }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ $auth?.user?.company?.name }}
                              </div>
                            </div>
                          </div>
                        </v-col>
                        <v-col>
                          <div>
                            <div class="white--text">sdf</div>
                            <div style="margin-top: 3px">
                              <strong>Email:</strong>
                              {{ item?.employee?.local_email || "---" }}
                            </div>
                            <div style="margin-top: 3px">
                              <strong>Ph:</strong>
                              {{ item?.employee?.whatsapp_number }}
                            </div>
                            <div style="margin-top: 3px">
                              <strong>Nationality:</strong>
                              {{ item?.employee?.home_country || "---" }}
                            </div>
                            <div style="margin-top: 3px">
                              <strong>Manager:</strong>
                              {{
                                item?.employee?.reporting_manager?.first_name
                              }}
                            </div>
                          </div>
                        </v-col>
                        <v-col class="text-center">
                          <div class="body-2">
                            <v-rating
                              v-model="rating"
                              color="orange"
                              dense
                              size="20"
                              half-increments
                              readonly
                            />
                          </div>
                          <div class="white--text body-2">hideme</div>
                          <div>
                            <strong>Since</strong>
                            <h4 class="text-center text-primary">
                              {{ item?.employee?.show_joining_date }}
                            </h4>
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12">
                  <v-card outlined class="py-0">
                    <v-card-text>
                      <div v-if="options" class="body-2 text-left">
                        <b
                          >{{ options?.from_date }}
                          {{
                            options?.from_date && options?.to_date ? "to" : ""
                          }}
                          {{ options?.to_date }}</b
                        >
                      </div>
                      <div
                        style="display: flex; align-items: center; height: 25vh"
                      >
                        <!-- Left Table (Smaller) -->
                        <div style="flex: 0.7; min-width: 10%">
                          <table style="width: 100%; table-layout: fixed">
                            <tr>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="success"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">
                                P ({{ item?.p_count_value }})
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div
                                  class="error"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td>A ({{ item?.a_count_value }})</td>
                            </tr>
                            <tr>
                              <td>
                                <div
                                  class="orange"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td>L ({{ item?.l_count_value }})</td>
                            </tr>
                          </table>
                        </div>

                        <!-- Pie Chart -->
                        <div style="flex: 0.7; min-width: 30%">
                          <apexchart
                            with="300px"
                            v-if="isMounted"
                            type="pie"
                            :options="pieOptions"
                            :series="pieSeries"
                          ></apexchart>
                        </div>

                        <!-- Bar Chart (More Space) -->
                        <div style="flex: 0.7; min-width: 50%">
                          <div class="body-2 text-left">
                            <b>Last 6 Month</b>
                          </div>
                          <apexchart
                            v-if="
                              isMounted && barOptions?.xaxis?.categories?.length
                            "
                            type="bar"
                            height="200px"
                            :options="barOptions"
                            :series="barSeries"
                          ></apexchart>
                        </div>

                        <!-- Right Table -->
                        <div style="flex: 0.7; min-width: 10%">
                          <table style="width: 100%; table-layout: fixed">
                            <tr>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="success"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">P (50)</td>
                            </tr>
                            <tr>
                              <td>
                                <div
                                  class="error"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td>A (10)</td>
                            </tr>
                          </table>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12" class="py-1">
                  <v-card outlined>
                    <v-card-text>
                      <v-row>
                        <!-- Rating and Joining Date -->
                        <v-col class="text-center">
                          <div><strong>Total Hrs</strong></div>
                          <div>4528 Hrs. / 95 days</div>
                        </v-col>
                        <v-col class="text-center">
                          <div><strong>Late In</strong></div>
                          <div>12.8 Hrs. / 12 days</div>
                        </v-col>
                        <v-col class="text-center">
                          <div><strong>Early Out</strong></div>
                          <div>45.6 Hrs. / 17 days</div>
                        </v-col>
                        <v-col class="text-center">
                          <div><strong>OverTime</strong></div>
                          <div>651 Hrs. / 45 days</div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="6">
                  <v-card outlined>
                    <v-card-text>
                      <div class="body-2"><b>Salary</b></div>
                      <table dense flat style="width: 100%" class="pt-1">
                        <tbody>
                          <tr>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Month
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Salary
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Ot
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Deduction
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Total
                            </td>
                          </tr>
                          <tr v-for="(n, i) in 6" :key="i">
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              Jan 2025
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              8500.00
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              1500.00
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              750.00
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              9250.00
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="6">
                  <v-card outlined>
                    <v-card-text>
                      <div class="body-2"><b>Payroll Details</b></div>
                      <div style="display: flex; align-items: center">
                        <!-- Left Table (Smaller) -->
                        <div style="flex: 0.7; min-width: 10%">
                          <table style="width: 100%; table-layout: fixed">
                            <tr>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="success"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">Total Salary</td>
                            </tr>
                            <tr>
                              <td>
                                <div
                                  class="error"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td>Overtime</td>
                            </tr>
                            <tr>
                              <td>
                                <div
                                  class="orange"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td>Deduction</td>
                            </tr>
                          </table>
                        </div>

                        <div style="flex: 0.7; min-width: 72%">
                          <apexchart
                            v-if="isMounted"
                            type="donut"
                            :options="chartOptionsDonut"
                            :series="donutSeries"
                          ></apexchart>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="4">
              <v-row>
                <v-col cols="12">
                  <v-card outlined>
                    <v-card-text class="pa-0">
                      <v-date-picker
                        full-width
                        no-title
                        dense
                        v-model="selectedDate"
                        :events="getEvents"
                        :event-color="getEventColor"
                      ></v-date-picker>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12">
                  <v-card outlined>
                    <v-card-text>
                      <div class="body-2"><b>Leave Quota</b></div>
                      <div
                        class="d-flex justify-space-between text-center mt-3"
                      >
                        <div>
                          <div class="">Total</div>
                          <div class="">40</div>
                        </div>
                        <div>
                          <div class="">Balance</div>
                          <div class="">12</div>
                        </div>
                        <div>
                          <div class="">Approved</div>
                          <div class="">5</div>
                        </div>
                        <div>
                          <div class="">Rejected</div>
                          <div class="">5</div>
                        </div>
                      </div>
                      <div style="flex: 0.7; min-width: 30%">
                        <apexchart
                          height="250"
                          v-if="isMounted"
                          type="bar"
                          :options="leaveChartOptions"
                          :series="leaveChartSeries"
                        ></apexchart>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-container>
      </v-card>
    </v-dialog>
  </client-only>
</template>

<script>
export default {
  props: ["item", "options"],
  data() {
    return {
      rating: 4.5,
      selectedDate: new Date().toISOString().substr(0, 10), // Default to today
      attendanceData: {
        "2025-03-03": "late",
        "2025-03-05": "present",
        "2025-03-07": "absent",
        "2025-03-12": "leave",
        "2025-03-19": "late",
        "2025-03-20": "present",
        "2025-03-22": "present",
      },
      dialog: false,
      isMounted: false,
      pieSeries: [86, 5, 6],
      pieOptions: {
        labels: ["Present", "Absent", "Leave"],
        colors: ["#00e676", "#dd2c00", "#ff9800"],
        legend: { show: false }, // Hide the legends
      },
      barSeries: [
        { name: "Present", data: [25, 15, 23, 10, 17, 21] },
        { name: "Absent", data: [6, 16, 8, 21, 14, 10] },
      ],
      barOptions: {
        chart: {
          type: "bar",
          stacked: true,
          toolbar: {
            show: false,
          },
        },
        xaxis: { categories: [] },
        colors: ["#00e676", "#dd2c00"],
        legend: { show: false }, // Hide the legends
        plotOptions: {
          bar: {
            columnWidth: "35%", // Increase bar thickness
          },
        },
        dataLabels: {
          enabled: false,
        },
      },

      donutSeries: [12000, 3000, 2000], // Total Salary, Overtime, Deductions
      chartOptionsDonut: {
        chart: {
          type: "donut",
        },
        labels: ["Total Salary", "Overtime", "Deductions"], // Labels for the donut chart
        colors: ["#4CAF50", "#FFA500", "#FF5252"], // Green for Salary, Orange for Overtime, Red for Deductions
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: 400,
              },
              legend: {
                position: "bottom",
              },
            },
          },
        ],
        legend: {
          show: false, // Hides the legend
        },
        dataLabels: {
          enabled: false, // Hides data labels on the chart
        },
        plotOptions: {
          pie: {
            donut: {
              size: "50%", // Reduces the depth (inner radius) of the donut
              labels: {
                show: false, // Hides all labels inside the donut
                total: {
                  show: false, // Hides the total payroll label and value
                },
              },
            },
          },
        },
      },

      leaveChartOptions: {
        chart: {
          type: "bar", // Change the chart type to bar
          toolbar: {
            show: false,
          },
        },
        plotOptions: {
          bar: {
            horizontal: false, // Keep the bars vertical
            columnWidth: "50%", // Adjust the width of the bars if needed
          },
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
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ],
        },
        colors: ["#4CAF50"], // Green color for the bar
        legend: {
          show: false,
        },
        dataLabels: {
          enabled: false, // Hides data labels on the chart
        },
      },
      leaveChartSeries: [
        {
          name: "Current Year",
          type: "bar", // Set the type to bar for the current year
          data: [10, 12, 15, 20, 25, 10, 12, 30, 10, 20, 12, 22], // Current year's data
        },
      ],
    };
  },
  async mounted() {
    let payload = {
      company_id: this.$auth.user.company_id,
      employee_id: this.item.employee_id,
    };

    let endpoint = `last-six-month-performance-report`;

    let { data } = await this.$axios.post(endpoint, payload);

    console.log("🚀 ~ mounted ~ data:", data);

    // Extract month_year values for categories (now it's already formatted correctly)
    const categories = data.map((e) => e.month_year);

    // Extract present_count and absent_count for the bar chart series
    const presentData = data.map((e) => e.present_count);
    const absentData = data.map((e) => e.absent_count);

    // Update barOptions and barSeries with dynamic data
    this.barOptions.xaxis.categories = categories;
    this.barSeries = [
      { name: "Present", data: presentData },
      { name: "Absent", data: absentData },
    ];

    this.isMounted = true;
  },

  methods: {
    getEvents(date) {
      return this.attendanceData[date] ? [this.attendanceData[date]] : [];
    },
    getEventColor(date) {
      return this.eventColors[this.attendanceData[date]] || "";
    },
  },
};
</script>
