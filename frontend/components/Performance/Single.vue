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
          <div class="text-right">
            <v-icon color="primary" left @click="takeScreenshot"
              >mdi-download</v-icon
            >
          </div>
          <v-row
            class="pa-2"
            v-if="item && item.employee"
            id="screenshot-target"
          >
            <v-col cols="8">
              <v-row>
                <v-col cols="12">
                  <v-card outlined>
                    <v-card-text>
                      <v-row>
                        <v-col>
                          <div class="d-flex align-start">
                            <v-avatar size="60">
                              <img v-if="base64Image"
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
                                {{ item?.employee?.first_name }}
                              </div>

                              <div style="margin-top: 3px">
                                ID: {{ item.employee_id }}
                                {{ item?.employee?.employee_id }}
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
                              dense
                              hide-details
                              :value="
                                $utils.getRating(
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
                        <div style="flex: 0.7; min-width: 60%">
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
                <v-col cols="6">
                  <v-card outlined>
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
                <v-col cols="6">
                  <v-card outlined>
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
                              <td style="white-space: nowrap">Total Salary</td>
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
import html2canvas from "html2canvas";

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

      donutSeries: [12000, 3000, 2000], // Total Salary, Overtime, Deductions
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

      payslipsData: [],
      hoursReportData: null,
      base64Image: null,
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

    this.base64Image = await this.getEncodedImage();

    this.isMounted = true;

    const scripts = [
      {
        src: "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js",
        type: "text/javascript",
        async: true,
      },
      // Add MDI CDN for Material Design Icons
      {
        src: "https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css",
        type: "text/css",
        async: true,
      },
    ];

    // Append scripts dynamically
    scripts.forEach((script) => {
      const scriptTag = document.createElement("link");
      scriptTag.href = script.src;
      scriptTag.type = script.type;
      scriptTag.rel = "stylesheet"; // For CSS files
      scriptTag.async = script.async;
      document.head.appendChild(scriptTag);
    });

    // Append scripts dynamically
    scripts.forEach((script) => {
      const scriptTag = document.createElement("script");
      scriptTag.src = script.src;
      scriptTag.type = script.type;
      scriptTag.async = script.async;
      document.head.appendChild(scriptTag);
    });
  },

  methods: {
    async getEncodedImage() {
      let image = this.item?.employee?.profile_picture;

      let { data } = await this.$axios.get(`get-encoded-profile-picture`);

      return data;
    },
    takeScreenshot() {
      html2canvas(document.getElementById("screenshot-target"), {
        useCORS: true, // Ensure cross-origin images are captured
        scale: 3, // Higher scale for better quality (increase for higher resolution)
      }).then((canvas) => {
        const imgData = canvas.toDataURL("image/png");

        const pdf = new jspdf.jsPDF("l", "mm", "a4"); // "l" for landscape orientation
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
        let response = await this.$axios.post(endpoint, payload);

        // Check if the response status is 404
        if (response.status === 404) {
          console.error("Resource not found (404)");
          this.donutSeries = [10000, 1000, 1000];
          return;
        }

        // If the response is successful, process the data
        let { data } = response;

        if (!data) {
          this.donutSeries = [10000, 1000, 1000];
          return;
        }

        this.donutSeries = [
          data.salary_and_earnings || 10000,
          data.ot_value || 1000,
          data.total_deductions_value || 1000,
        ];
      } catch (error) {
        // Handle other errors (e.g., network errors, server errors)
        if (error.response) {
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          if (error.response.status === 404) {
            console.error("Resource not found (404)");
          } else {
            console.error("Server error:", error.response.status);
          }
        } else if (error.request) {
          // The request was made but no response was received
          console.error("No response received:", error.request);
        } else {
          // Something happened in setting up the request
          console.error("Error:", error.message);
        }

        // Set default values in case of any error
        this.donutSeries = [10000, 1000, 1000];
      }
    },

    getEvents(date) {
      return this.attendanceData[date] ? [this.attendanceData[date]] : [];
    },
    getEventColor(date) {
      return this.eventColors[this.attendanceData[date]] || "";
    },
  },
};
</script>
