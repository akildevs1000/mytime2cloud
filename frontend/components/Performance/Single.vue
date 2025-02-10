<template>
  <client-only>
    <v-dialog v-model="dialog" width="1300px">
      <WidgetsClose left="1290" @click="dialog = false" />
      <template v-slot:activator="{ on, attrs }">
        <span v-bind="attrs" v-on="on"
          ><v-icon color="secondary" small>mdi-trophy</v-icon> View</span
        >
      </template>
      <style scoped>
        /* Hide the selected date highlight */
        .v-date-picker-table .v-btn--active {
          background: white !important;
          color: black !important;
        }

        .v-date-picker-table .v-btn--active::before {
          opacity: 0 !important;
        }
      </style>
      <v-card style="overflow-y: scroll; max-height: 850px">
        <v-container fluid>
          <div class="text-right">
            <v-btn @click="takeScreenshot" class="primary"
              >Download
              <v-icon right>mdi-download</v-icon></v-btn
            >
          </div>
          <v-row
            no-gutters
            class="pa-2"
            v-if="item && item.employee"
            id="screenshot-target"
          >
            <v-col style="margin-right: 10px">
              <v-row no-gutters>
                <v-col cols="12" style="margin-top: 10px">
                  <v-card outlined>
                    <v-card-text>
                      <v-row>
                        <v-col cols="5">
                          <div class="d-flex align-start">
                            <v-avatar size="60">
                              <img
                                v-if="base64Image"
                                ref="profileImage"
                                :src="base64Image"
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
                                <b
                                  >{{ item?.employee?.title }}.
                                  {{ item?.employee?.full_name }}</b
                                >
                              </div>

                              <div style="margin-top: 3px">
                                ID: {{ item.employee_id }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ item?.employee?.designation?.name || "---" }}
                              </div>
                              <div style="margin-top: 3px">
                                {{
                                  item?.employee?.branch?.branch_name || "---"
                                }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ $auth?.user?.company?.name }}
                              </div>
                            </div>
                          </div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-left">
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
                        <v-col cols="3" class="text-center">
                          <div class="body-2">
                            <v-rating
                              dense
                              hide-details
                              :value="
                                getRating(
                                  item.p_count_value,
                                  options.from_date,
                                  options.to_date
                                )
                              "
                              background-color="green lighten-3"
                              color="green"
                              half-increments
                            ></v-rating>
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
                <v-col cols="12" style="margin-top: 10px">
                  <v-card outlined>
                    <v-card-text>
                      <div style="display: flex">
                        <div
                          style="min-width: 340px"
                          v-if="options"
                          class="body-2 text-left"
                        >
                          <b
                            >{{ formatDate(options?.from_date) }}
                            {{
                              options?.from_date && options?.to_date ? "to" : ""
                            }}
                            {{ formatDate(options?.to_date) }}</b
                          >
                        </div>
                        <div class="body-2 text-left">
                          <b>Last 6 Month</b>
                        </div>
                      </div>
                      <div
                        style="display: flex; align-items: center; height: 23vh"
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
                                <div class="pt-3">Present</div>
                                <div>({{ item?.p_count_value }})</div>
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
                              <td>
                                <div class="pt-3">Absent</div>
                                <div>({{ item?.a_count_value }})</div>
                              </td>
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
                              <td>
                                <div class="pt-3">Leave</div>
                                <div>({{ item?.l_count_value }})</div>
                              </td>
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
                        <div style="flex: 0.7; min-width: 60%">
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
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12" style="margin-top: 10px">
                  <v-card outlined>
                    <v-card-text>
                      <v-row>
                        <!-- Rating and Joining Date -->
                        <v-col class="text-center">
                          <div><strong>Total Hrs</strong></div>
                          <div>
                            {{
                              hoursReportData?.total_performed_hours || "---"
                            }}
                          </div>
                        </v-col>
                        <v-col class="text-center">
                          <div><strong>Late In</strong></div>
                          <div>
                            {{ hoursReportData?.late_coming_hours || "---" }}
                          </div>
                        </v-col>
                        <v-col class="text-center">
                          <div><strong>Early Out</strong></div>
                          <div>
                            {{ hoursReportData?.early_going_hours || "---" }}
                          </div>
                        </v-col>
                        <v-col class="text-center">
                          <div><strong>OverTime</strong></div>
                          <div>
                            {{ hoursReportData?.overtime_hours || "---" }}
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="6" style="margin-top: 10px">
                  <v-card
                    outlined
                    style="
                      margin-right: 5px;
                      max-height: 235px;
                      min-height: 235px;
                    "
                  >
                    <v-card-text>
                      <div class="body-2"><b>Salary (Last 6 Months)</b></div>
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
                          <tr v-for="(payslipData, i) in payslipsData" :key="i">
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ payslipData.month }} {{ payslipData.year }}

                              {{
                                !payslipData.month || !payslipData.month
                                  ? "---"
                                  : ""
                              }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ payslipData.salary_and_earnings }}
                              {{
                                !payslipData.salary_and_earnings ? "---" : ""
                              }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ payslipData.ot }}
                              {{ !payslipData.ot ? "---" : "" }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ payslipData.total_deductions }}
                              {{ !payslipData.total_deductions ? "---" : "" }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ payslipData.finalSalary }}
                              {{ !payslipData.finalSalary ? "---" : "" }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col
                  style="margin-top: 10px; max-height: 235px; min-height: 235px"
                >
                  <v-card outlined style="max-height: 235px; min-height: 235px">
                    <v-card-text>
                      <div class="body-2">
                        <b>Payroll Details (Current Month)</b>
                      </div>
                      <div style="display: flex; align-items: center">
                        <!-- Left Table (Smaller) -->
                        <div style="flex: 0.7; min-width: 10%">
                          <table style="width: 100%; table-layout: fixed">
                            <tr>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="green"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">Salary</td>
                            </tr>
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

                        <div style="flex: 0.7; min-width: 60%">
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
            <v-col cols="4" style="margin-top: 10px">
              <v-row no-gutters>
                <v-col cols="12">
                  <v-card outlined>
                    <v-date-picker
                      hide-details
                      v-if="selectedDate"
                      full-width
                      no-title
                      dense
                      :events="Object.keys(events)"
                      :event-color="getEventColors"
                      v-model="selectedDate"
                      :max="maxDate"
                    >
                      <template v-slot:default>
                        <v-row>
                          <!-- Present -->
                          <v-col cols="3" class="text-center">
                            <v-icon color="green" x-small>mdi-circle</v-icon>
                            <small> P ({{ eventStats["P"] || "0" }})</small>
                          </v-col>

                          <!-- Absent -->
                          <v-col cols="3" class="text-center">
                            <v-icon color="red" x-small>mdi-circle</v-icon>
                            <small> A ({{ eventStats["A"] || "0" }})</small>
                          </v-col>

                          <!-- Leave -->
                          <v-col cols="3" class="text-center">
                            <v-icon color="orange" x-small>mdi-circle</v-icon>
                            <small>L ({{ eventStats["L"] || "0" }})</small>
                          </v-col>

                          <!-- Week Off -->
                          <v-col cols="3" class="text-center">
                            <v-icon color="primary" x-small>mdi-circle</v-icon>
                            <small>WO ({{ eventStats["O"] || "0" }})</small>
                          </v-col>
                        </v-row>
                      </template>
                    </v-date-picker>
                  </v-card>
                </v-col>
                <v-col cols="12" style="margin-top: 10px">
                  <v-card outlined min-height="410">
                    <v-card-text>
                      <div class="body-2" style="margin-bottom: 38px">
                        <b>Leave Quota</b>
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

                      <div
                        class="d-flex justify-space-between text-center pt-8"
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
import html2canvas from "html2canvas";
import jsPDF from "jsPDF";

export default {
  props: ["item", "options"],
  data() {
    return {
      rating: 4.5,
      selectedDate: null, // Default to today
      maxDate: null,
      events: null,
      eventStats: null,
      dialog: false,
      isMounted: false,
      pieSeries: [86, 5, 6],
      pieOptions: {
        labels: ["Present", "Absent", "Leave"],
        colors: ["#00e676", "#dd2c00", "#ff9800"],
        legend: { show: false }, // Hide the legends
        dataLabels: { enabled: false },
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
        legend: { show: true }, // Hide the legends
        plotOptions: {
          bar: {
            columnWidth: "35%", // Increase bar thickness
          },
        },
        dataLabels: {
          enabled: false,
        },
      },

      donutSeries: [], // Total Salary, Overtime, Deductions
      chartOptionsDonut: {
        chart: {
          type: "donut",
        },
        labels: ["Salary", "Overtime", "Deductions"], // Labels for the donut chart
        colors: ["#4CAF50", "#00e676", "#FFA500"], // Green for Salary, Orange for Overtime, Red for Deductions
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

      payslipsData: [],
      hoursReportData: null,
      base64Image: null,
      leaveQuota: 0,
    };
  },
  head() {
    return {
      link: [
        {
          rel: "stylesheet",
          href: "https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css",
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

    await this.getLastSixMonthSalaryReport();

    await this.getCurrentMonthHoursReport();

    await this.getCurrentMonthSalaryReport();

    await this.getEncodedImage();

    await this.getCurrentMonthPerformanceReport(payload);

    await this.getLeaveQuota();

    this.isMounted = true;

    const date = new Date(); // This is a Date object, not a string
    let previousMonth = new Date(date.getFullYear(), date.getMonth())
      .toISOString()
      .substr(0, 7);

    this.selectedDate = `${previousMonth}-01`;
    this.maxDate = `${previousMonth}-31`;
  },

  methods: {
    getRating(count, from_date, to_date) {
      // Convert to Date objects
      let fromDate = new Date(from_date);
      let toDate = new Date(to_date);

      // Calculate difference in milliseconds
      let diffInMilliseconds = toDate - fromDate;

      // Convert milliseconds to days
      let totalDays = Math.ceil(diffInMilliseconds / (1000 * 60 * 60 * 24));

      let presentPercent = totalDays > 0 ? (count / totalDays) * 100 : 0;

      if (presentPercent > 90 && presentPercent <= 100) {
        return 5;
      } else if (presentPercent > 80 && presentPercent <= 90) {
        return 4.5;
      } else if (presentPercent > 70 && presentPercent <= 80) {
        return 4;
      } else if (presentPercent > 60 && presentPercent <= 70) {
        return 3.5;
      } else if (presentPercent > 50 && presentPercent <= 60) {
        return 3;
      } else if (presentPercent > 40 && presentPercent <= 50) {
        return 2.5;
      } else if (presentPercent > 30 && presentPercent <= 40) {
        return 2;
      } else if (presentPercent > 20 && presentPercent <= 30) {
        return 1.5;
      } else if (presentPercent > 10 && presentPercent <= 20) {
        return 1;
      } else {
        return 0;
      }
    },
    formatDate: (inputdate) => {
      const date = new Date(inputdate);
      const options = { day: "2-digit", month: "short", year: "numeric" };
      return date.toLocaleDateString("en-GB", options).replace(",", "");
    },
    async getCurrentMonthPerformanceReport(payload) {
      let { data } = await this.$axios.post(
        `current-month-performance-report`,
        payload
      );
      this.events = data.events;
      this.eventStats = data.stats;
    },
    getEventColors(e) {
      return this.events[e] || "";
    },
    async getEncodedImage() {
      let { data } = await this.$axios.get(`get-encoded-profile-picture`);

      this.base64Image = data;
    },
    takeScreenshot() {
      html2canvas(document.getElementById("screenshot-target"), {
        useCORS: true, // Ensure cross-origin images are captured
        scale: 3, // Higher scale for better quality (increase for higher resolution)
      }).then((canvas) => {
        const imgData = canvas.toDataURL("image/png");

        const pdf = new jsPDF("l", "mm", "a4"); // "l" for landscape orientation
        const imgWidth = 297; // Width of A4 paper in mm (landscape)
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio

        // Add the captured image to the PDF
        pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
        pdf.save("performance-report.pdf"); // Save the PDF
      });
    },
    async getCurrentMonthHoursReport() {
      let payload = {
        company_id: this.$auth.user.company_id,
        employee_id: this.item.employee_id,
      };

      let endpoint = "current-month-hours-report";

      let { data } = await this.$axios.post(endpoint, payload);

      this.hoursReportData = data;
    },
    async getLastSixMonthSalaryReport() {
      let employee_id = this.item?.employee?.employee_id;

      let payload = {
        company_id: this.$auth.user.company_id,
        employee_id: employee_id,
      };

      let endpoint = "last-six-month-salary-report";

      let { data } = await this.$axios.post(endpoint, payload);

      this.payslipsData = data;
    },

    async getCurrentMonthSalaryReport() {
      let employee_id = this.item?.employee?.employee_id;

      let payload = {
        company_id: this.$auth.user.company_id,
        employee_id: employee_id,
      };

      let endpoint = "current-month-salary-report";

      try {
        let { data } = await this.$axios.post(endpoint, payload);

        if (!data) {
          this.donutSeries = [];
          return;
        }
        this.donutSeries = [
          data.salary_and_earnings || 10000,
          data.ot_value || 1000,
          data.total_deductions_value || 1000,
        ];
        this.chartOptionsDonut.colors = ["#4CAF50", "#00e676", "#FFA500"];
      } catch (error) {
        this.donutSeries = [1];
        this.chartOptionsDonut.colors = ["grey"];
      }
    },
    async getLeaveQuota() {
      console.log("🚀 ~ getLeaveQuota ~ getLeaveQuota:");

      let employee = this.item.employee;
      console.log(
        "🚀 ~ getLeaveQuota ~ employee.leave_group_id:",
        employee.leave_group_id
      );

      if (!employee.leave_group_id) {
        return false;
      }
      this.dialogLeaveGroup = true;
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
          employee_id: employee.employee_id,
        },
      };
      this.$axios
        .get("leave_groups/" + employee.leave_group_id, options)
        .then(({ data }) => {
          this.leaveQuota =
            data
              .map((e) => e.leave_count)
              .map((e) => ({
                leave_type_count: e.leave_type_count,
                employee_used: e.employee_used,
              })) || 0;
          console.log("🚀 ~ .then ~ leaveQuota:", this.leaveQuota);
        });
    },
  },
};
</script>
