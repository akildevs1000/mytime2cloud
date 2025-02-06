<template>
  <div class="text-center">
    <v-dialog v-model="dialog" width="1000px">
      <WidgetsClose left="990" @click="dialog = false" />
      <template v-slot:activator="{ on, attrs }">
        <span>
          <v-icon color="secondary" small v-bind="attrs" v-on="on">
            mdi-trophy
          </v-icon>
          View
        </span>
      </template>

      <v-card>
        <v-container>
          <v-row>
            <v-col cols="8">
              <v-row class="py-1">
                <v-col cols="12" class="py-1">
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
                              <div class="primary--text body-2 py-0">
                                <small> Ms. Mariya Susan</small>
                              </div>
                              <div>
                                <small> ID: 421</small>
                              </div>
                              <div>
                                <small> Sales Executive</small>
                              </div>
                              <div>
                                <small> Burjuman Branch</small>
                              </div>
                              <div>
                                <small> Vperfume</small>
                              </div>
                            </div>
                          </div>
                        </v-col>

                        <!-- Contact Info and Manager Details -->
                        <v-col>
                          <div>
                            <div class="white--text">sdf</div>
                            <div>
                              <small
                                ><strong>Email:</strong>
                                mariyasusan@vperfume.ae</small
                              >
                            </div>
                            <div>
                              <small
                                ><strong>Ph:</strong> +971 55 231 2345</small
                              >
                            </div>
                            <div>
                              <small
                                ><strong>Nationality:</strong> Indian</small
                              >
                            </div>
                            <div>
                              <small
                                ><strong>Manager:</strong> Mr. Kumar
                                Subramani</small
                              >
                            </div>
                          </div>
                        </v-col>

                        <!-- Rating and Joining Date -->
                        <v-col class="text-center">
                          <div class="white--text body-2">hideme</div>
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
                          <div>
                            <strong>Since</strong>
                            <h4 class="text-center text-primary">
                              15 Jan 2022
                            </h4>
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12" class="py-1">
                  <v-card outlined>
                    <v-card-text>
                      <v-row>
                        <!-- Rating and Joining Date -->
                        <v-col cols="5" class="text-center py-0">
                          <div style="width: 100%; display: flex">
                            <div class="text-left">
                              <table>
                                <tr>
                                  <td>
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
                                  <td>Present</td>
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
                                  <td>Absent</td>
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
                                  <td>Leave</td>
                                </tr>
                              </table>
                            </div>
                            <div style="height: 160px; width: 175px">
                              <apexchart
                                type="pie"
                                :options="pieChartOptions"
                                :series="pieSeries"
                              ></apexchart>
                            </div>
                          </div>
                        </v-col>
                        <v-col class="text-center py-0">
                          <div style="height: 160px; width: 320px">
                            <apexchart
                              height="100%"
                              type="bar"
                              :options="chartOptions"
                              :series="series"
                            ></apexchart>
                          </div>
                        </v-col>
                      </v-row>
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
                <!-- <v-col cols="12">
                  <v-card outlined>
                    <v-card-text>
                     <v-row>
                      <v-col>
                        <div style="width: 100%; display: flex">
                        <div class="text-left">
                          <table>
                            <tr>
                              <td>
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
                              <td>Total Salary</td>
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
                              <td>OverTime</td>
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
                        <div style="height: 160px; width: 175px">
                          <apexchart
                            height="100%"
                            type="donut"
                            :options="chartOptionsDonut"
                            :series="donutSeries"
                          ></apexchart>
                        </div>
                      </div>
                      </v-col>
                      <v-col>
                        <div style="width: 100%; display: flex">
                        <div class="text-left">
                          <table>
                            <tr>
                              <td>
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
                              <td>Total Salary</td>
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
                              <td>OverTime</td>
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
                        <div style="height: 160px; width: 175px">
                          <apexchart
                            height="100%"
                            type="donut"
                            :options="chartOptionsDonut"
                            :series="donutSeries"
                          ></apexchart>
                        </div>
                      </div>
                      </v-col>
                     </v-row>
                    </v-card-text>
                  </v-card>
                </v-col> -->
              </v-row>
            </v-col>
            <v-col cols="4">
              <v-card outlined>
                <v-date-picker
                  no-title
                  dense
                  v-model="selectedDate"
                  :events="getEvents"
                  :event-color="getEventColor"
                ></v-date-picker>
              </v-card>
            </v-col>
          </v-row>
        </v-container>

        <!-- <pre>
          <span>{{ item }}</span>
        </pre> -->
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["item"],
  data() {
    return {
      dialog: false,
      rating: 4.5, // Star rating
      selectedDate: new Date().toISOString().substr(0, 10), // Default to today
      attendanceData: {
        // Example attendance records
        "2025-03-03": "late",
        "2025-03-05": "present",
        "2025-03-07": "absent",
        "2025-03-12": "leave",
        "2025-03-19": "late",
        "2025-03-20": "present",
        "2025-03-22": "present",
      },
      eventColors: {
        present: "green",
        absent: "red",
        leave: "blue",
        late: "orange",
      },
      loading: false,
      display_title: "Attendance Distribution",

      chartOptions: {
        chart: {
          id: "stacked-bar",
          stacked: true, // Enables stacked bars
        },
        plotOptions: {
          bar: {
            horizontal: false, // If you want vertical bars
          },
        },
        xaxis: {
          categories: [
            "Sep 25",
            "Oct 25",
            "Nov 25",
            "Dec 25",
            "Jan 25",
            "Feb 25",
          ], // Last 6 months
        },
        colors: ["#4CAF50", "#F44336"], // Green for Present, Red for Absent
      },
      series: [
        {
          name: "Present",
          data: [120, 150, 130, 140, 160, 145], // Data for Present
        },
        {
          name: "Absent",
          data: [10, 20, 15, 25, 30, 18], // Data for Absent
        },
      ],

      // pie chart options

      pieChartOptions: {
        chart: {
          id: "pie-chart",
        },
        labels: ["Present", "Absent", "Leave"],
        legend: {
          show: false, // Hides the legend
        },
        colors: ["#00e676", "#dd2c00", "#ff9800"], // Custom colors for each slice
      },
      pieSeries: [6, 2, 1], // Example data

      //donut chart

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
                width: 200,
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
    };
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
