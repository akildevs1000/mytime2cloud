<template>
  <client-only>
    <v-dialog v-model="dialog" width="1300px">
      <WidgetsClose left="1290" @click="dialog = false" />
      <template v-slot:activator="{ on, attrs }">
        <span v-bind="attrs" v-on="on"
          ><v-icon color="secondary" small>mdi-eye</v-icon></span
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
            <v-btn @click="download" class="primary"
              >Download <v-icon right>mdi-download</v-icon></v-btn
            >
          </div>
          <v-row no-gutters class="pa-2" id="screenshot-element">
            <v-col style="margin-right: 10px">
              <v-row no-gutters>
                <v-col cols="12" style="margin-top: 10px">
                  <v-card outlined>
                    <v-card-text style="color: black">
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
                                <b> {{ employee?.name }}</b>
                              </div>

                              <div style="margin-top: 3px">
                                ID: {{ employee?.employee_id }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ employee?.designation || "---" }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ employee?.branch || "---" }}
                              </div>
                              <div style="margin-top: 3px">
                                {{ employee?.company }}
                              </div>
                            </div>
                          </div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-left">
                            <div class="white--text">sdf</div>
                            <div style="margin-top: 3px">
                              Email:{{ employee?.email || "---" }}
                            </div>
                            <div style="margin-top: 3px">
                              Ph:
                              {{ employee?.whatsapp_number }}
                            </div>
                            <div style="margin-top: 3px">
                              Nationality:
                              {{ employee?.home_country || "---" }}
                            </div>
                            <div style="margin-top: 3px">
                              Manager:
                              {{ employee?.reporting_manager }}
                            </div>
                          </div>
                        </v-col>
                        <v-col cols="3" class="text-center">
                          <div class="body-2">
                            <v-rating
                              dense
                              hide-details
                              :value="item.rating"
                              background-color="green lighten-3"
                              color="green"
                              half-increments
                            ></v-rating>
                          </div>
                          <div class="white--text body-2">hideme</div>
                          <div>
                            <strong>Since</strong>
                            <h4 class="text-center text-primary">
                              {{ employee?.joining_date }}
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
                        <div style="min-width: 340px" class="body-2 text-left">
                          <b>Current Month</b>
                        </div>
                        <div class="body-2 text-left">
                          <b>Last 6 Month</b>
                        </div>
                      </div>
                      <div
                        class="mt-1"
                        style="display: flex; align-items: center; height: 30vh"
                      >
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
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    item?.p_count
                                  }}</strong>
                                </div>
                                <div>Present</div>
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
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    item?.a_count
                                  }}</strong>
                                </div>
                                <div>Absent</div>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div
                                  class="primary"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td>
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    item?.o_count
                                  }}</strong>
                                </div>
                                <div>WeekOff</div>
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
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    item?.other_count
                                  }}</strong>
                                </div>
                                <div>Other</div>
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
                  <v-card
                    outlined
                    style="
                      max-height: 270px;
                      min-height: 270px;
                    "
                  >
                    <v-card-text class="py-5">
                      <div class="body-2"><b>(Last 6 Months)</b></div>
                      <table dense flat style="width: 100%" class="mt-5">
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
                              Total Hrs
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Late In
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              Early Out
                            </td>
                            <td
                              class="text-center"
                              style="border-bottom: 1px solid #eaeaeaea"
                            >
                              OverTime
                            </td>
                          </tr>
                          <tr
                            v-for="(item, monthLabel) in hoursReportData"
                            :key="monthLabel"
                          >
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ monthLabel }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ item?.total_performed?.hours || "---" }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ item?.late_coming?.hours || "---" }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ item?.early_going?.hours || "---" }}
                            </td>
                            <td
                              class="text-center"
                              style="
                                font-size: 11px;
                                border-bottom: 1px solid #eaeaeaea;
                              "
                            >
                              {{ item?.overtime?.hours || "---" }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </v-card-text>
                  </v-card>
                </v-col>
                <!-- <v-col cols="6" style="margin-top: 10px">
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
                </v-col> -->
                <!-- <v-col
                  style="margin-top: 10px; max-height: 235px; min-height: 235px"
                >
                  <v-card outlined style="max-height: 235px; min-height: 235px">
                    <v-card-text>
                      <div class="body-2">
                        <b>Payroll Details (Last Month)</b>
                      </div>
                      <div style="display: flex; align-items: center">
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
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    donutSeries[0] || 0
                                  }}</strong>
                                </div>
                                <div>Salary</div>
                              </td>
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
                              <td>
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    donutSeries[1] || 0
                                  }}</strong>
                                </div>
                                <div>Overtime</div>
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
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    donutSeries[2] || 0
                                  }}</strong>
                                </div>
                                <div>Deduction</div>
                              </td>
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
                </v-col> -->
              </v-row>
            </v-col>
            <v-col cols="4" style="margin-top: 10px">
              <v-row no-gutters>
                <v-col cols="12">
                  <v-card outlined>
                    <v-card-text>
                      <v-date-picker
                        hide-details
                        v-if="selectedDatesForCurrentMonth"
                        full-width
                        no-title
                        dense
                        :events="Object.keys(eventsForCurrentMonth)"
                        :event-color="getEventColorsForCurrentMonth"
                        v-model="selectedDatesForCurrentMonth"
                        :max="maxDateForCurrentMonth"
                      >
                        <template v-slot:default>
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
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStatsForCurrentMonth["P"] || 0
                                  }}</strong>
                                </div>
                                <div>Present</div>
                              </td>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="red"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStatsForCurrentMonth["A"] || 0
                                  }}</strong>
                                </div>
                                <div>Absent</div>
                              </td>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="primary"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStatsForCurrentMonth["O"] || 0
                                  }}</strong>
                                </div>
                                <div>WeekOff</div>
                              </td>
                              <td style="width: 20px; min-width: 10px">
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
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStatsForCurrentMonth["OTHERS_COUNT"] ||
                                    0
                                  }}</strong>
                                </div>
                                <div>Other</div>
                              </td>
                            </tr>
                          </table>
                        </template>
                      </v-date-picker>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col
                  v-if="!leaveCardDisplay"
                  cols="12"
                  style="margin-top: 10px"
                >
                  <v-card outlined>
                    <v-card-text>
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
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStats["P"] || 0
                                  }}</strong>
                                </div>
                                <div>Present</div>
                              </td>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="red"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStats["A"] || 0
                                  }}</strong>
                                </div>
                                <div>Absent</div>
                              </td>
                              <td style="width: 20px; min-width: 10px">
                                <div
                                  class="primary"
                                  style="
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    display: inline-block;
                                  "
                                ></div>
                              </td>
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStats["O"] || 0
                                  }}</strong>
                                </div>
                                <div>WeekOff</div>
                              </td>
                              <td style="width: 20px; min-width: 10px">
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
                              <td style="white-space: nowrap">
                                <div class="pt-3">
                                  <strong style="font-size: 16px">{{
                                    eventStats["OTHERS_COUNT"] || 0
                                  }}</strong>
                                </div>
                                <div>Other</div>
                              </td>
                            </tr>
                          </table>
                        </template>
                      </v-date-picker>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col
                  v-if="leaveCardDisplay"
                  cols="12"
                  style="margin-top: 10px"
                >
                  <v-card outlined style="min-height: 370px">
                    <v-card-text>
                      <div class="body-2">
                        <b>Leave Quota</b>
                      </div>

                      <div style="flex: 0.7; min-width: 30%">
                        <apexchart
                          v-if="isMounted && leaveChartSeries[0].data.length"
                          type="bar"
                          :options="leaveChartOptions"
                          :series="leaveChartSeries"
                        ></apexchart>
                      </div>

                      <div
                        v-if="leaveQuota"
                        class="d-flex justify-space-between text-center"
                      >
                        <div>
                          <div class="">Total</div>
                          <div class="">
                            <strong style="font-size: 16px; color: black">{{
                              leaveQuota?.total_leave_days || 0
                            }}</strong>
                          </div>
                        </div>
                        <div>
                          <div class="">Balance</div>
                          <div class="">
                            <strong style="font-size: 16px; color: black">{{
                              leaveQuota?.balance || 0
                            }}</strong>
                          </div>
                        </div>
                        <div>
                          <div class="">Approved</div>
                          <div class="">
                            <strong style="font-size: 16px; color: black">{{
                              leaveQuota?.approved || 0
                            }}</strong>
                          </div>
                        </div>
                        <div>
                          <div class="">Rejected</div>
                          <div class="">
                            <strong style="font-size: 16px; color: black">{{
                              leaveQuota?.rejected || 0
                            }}</strong>
                          </div>
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
import jsPDF from "jspdf";
export default {
  props: ["item", "employee"],
  data() {
    return {
      rating: 4.5,
      selectedDate: null, // Default to today
      maxDate: null,
      events: null,
      eventStats: null,

      selectedDatesForCurrentMonth: null, // Default to today
      maxDateForCurrentMonth: null,
      eventsForCurrentMonth: null,
      eventStatsForCurrentMonth: null,

      dialog: false,
      isMounted: false,
      pieSeries: [86, 5, 6],
      pieOptions: {
        labels: ["Present", "Absent", "WeekOff", "Others"],
        colors: ["#00e676", "#dd2c00", "#6946dd", "#ff9800"],
        legend: { show: false }, // Hide the legends
        dataLabels: { enabled: false },
      },
      barSeries: [
        { name: "Present", data: [] },
        { name: "Absent", data: [] },
        { name: "WeekOff", data: [] },
        { name: "Others", data: [] },
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
        colors: ["#00e676", "#dd2c00", "#6946dd", "#ff9800"],
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

      donutSeries: [0, 0, 0], // Total Salary, Overtime, Deductions
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
          categories: [],
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
          data: [], // Current year's data
        },
      ],
      leaveCardDisplay: null,

      payslipsData: [],
      hoursReportData: null,
      base64Image: null,
      leaveQuota: null,
    };
  },
  async mounted() {
    console.log({ item: this.item, employee: this.employee });

    const { company_id } = this.item;
    const { employee_id, employee_id_for_payroll, profile_picture } =
      this.employee;

    await this.getLastSixMonthReport({
      company_id,
      employee_id: employee_id,
    });

    await this.getPreviousMonthPerformanceReport({
      company_id,
      employee_id: employee_id,
    });

    await this.geCurrentMonthPerformanceReport({
      company_id,
      employee_id: employee_id,
    });

    await this.getCurrentMonthHoursReport({
      company_id,
      employee_id: employee_id,
    });

    await this.getLastSixMonthSalaryReport({
      company_id,
      employee_id: employee_id_for_payroll,
    });

    await this.getPreviousMonthSalaryReport({
      company_id,
      employee_id: employee_id_for_payroll,
    });

    await this.getEncodedImage(profile_picture);

    await this.getLeaveQuota();

    await this.getYearlyLeaveQuota();

    this.pieSeries = [
      parseInt(this.item?.p_count),
      parseInt(this.item?.a_count),
      parseInt(this.item?.o_count),
      parseInt(this.item?.other_count),
    ];

    this.isMounted = true;

    this.setDataForDatePicker();
  },

  methods: {
    async getLastSixMonthReport(payload) {
      let endpoint = `last-six-month-performance-report`;

      let { data } = await this.$axios.post(endpoint, payload);

      const categories = data.map((e) => e.month_year);
      const presentData = data.map((e) => e.present_count);
      const absentData = data.map((e) => e.absent_count);
      const WeekOffData = data.map((e) => e.week_off_count);
      const otherData = data.map((e) => e.other_count);

      this.barOptions.xaxis.categories = categories;
      this.barSeries = [
        { name: "Present", data: presentData },
        { name: "Absent", data: absentData },
        { name: "WeekOff", data: WeekOffData },
        { name: "Others", data: otherData },
      ];
    },
    async getPreviousMonthPerformanceReport(payload) {
      let { data } = await this.$axios.post(
        `previous-month-performance-report`,
        payload
      );
      this.events = data.events;
      this.eventStats = data.stats;
    },

    async geCurrentMonthPerformanceReport(payload) {
      let { data } = await this.$axios.post(
        `current-month-performance-report`,
        payload
      );
      this.eventsForCurrentMonth = data.events;
      this.eventStatsForCurrentMonth = data.stats;
    },

    async getEncodedImage(url) {
      try {
        let { data } = await this.$axios.get(
          `/get-encoded-profile-picture/?url=${url}`
        );
        this.base64Image = data;
      } catch (error) {
        this.base64Image = null;
      }
    },
    async getCurrentMonthHoursReport(payload) {
      let endpoint = "last-six-month-hours-report";

      let { data } = await this.$axios.post(endpoint, payload);

      this.hoursReportData = data;
    },
    async getLastSixMonthSalaryReport(payload) {
      let endpoint = "last-six-month-salary-report";

      let { data } = await this.$axios.post(endpoint, payload);

      this.payslipsData = data;
    },
    async getPreviousMonthSalaryReport(payload) {
      let endpoint = "previous-month-salary-report";

      try {
        let { data } = await this.$axios.post(endpoint, payload);

        console.log("🚀 ~ getPreviousMonthSalaryReport ~ data:", data);
        if (!data) {
          this.donutSeries = [0, 0, 0];
          return;
        }
        this.donutSeries = [
          data.salary_and_earnings_value || 1,
          data.ot_value || 0,
          data.total_deductions_value || 0,
        ];
        this.chartOptionsDonut.colors = ["#4CAF50", "#00e676", "#FFA500"];
      } catch (error) {
        this.donutSeries = [1, 0, 0];
        this.chartOptionsDonut.colors = ["grey"];
      }
    },
    async getLeaveQuota() {
      if (!this.item.leave_group_id) {
        return false;
      }

      let options = {
        params: {
          company_id: this.item.company_id,
          employee_id: this.employee.employee_id_for_leave,
        },
      };

      this.$axios
        .get("leave_total_quota/" + this.item.leave_group_id, options)
        .then(({ data }) => {
          this.leaveQuota = data;
        });
    },
    async getYearlyLeaveQuota() {
      if (!this.item.leave_group_id) {
        return false;
      }

      let options = {
        params: {
          company_id: this.item.company_id,
          employee_id: this.employee.employee_id_for_leave,
        },
      };

      this.$axios
        .get("yearly_leave_quota/" + this.item.leave_group_id, options)
        .then(({ data }) => {
          console.log("🚀 ~ .then ~ data:", data);
          this.leaveChartOptions.xaxis.categories = data.month_names;
          this.leaveChartSeries[0].data = data.month_values;

          this.leaveCardDisplay = data.month_values.some((e) => e > 0);
        });
    },

    setDataForDatePicker() {
      const date = new Date(); // This is a Date object, not a string
      let currentMonth = new Date(date.getFullYear(), date.getMonth() + 1)
        .toISOString()
        .substr(0, 7);

      let previousMonth = new Date(date.getFullYear(), date.getMonth())
        .toISOString()
        .substr(0, 7);

      this.selectedDatesForCurrentMonth = `${currentMonth}-01`;
      this.maxDateForCurrentMonth = `${currentMonth}-31`;

      this.selectedDate = `${previousMonth}-01`;
      this.maxDate = `${previousMonth}-31`;
    },
    getEventColors(e) {
      return this.events[e] || "";
    },
    getEventColorsForCurrentMonth(e) {
      return this.eventsForCurrentMonth[e] || "";
    },
    download() {
      const queryParams = new URLSearchParams({
        item: JSON.stringify(this.item),
        employee: JSON.stringify(this.employee),
        company_id: this.$auth.user.company_id,
        baseUrl: this.$backendUrl,
      });

      // Open the target page in a new window with the query parameters
      const url = `${
        this.$appUrl
      }/performance_report/index.html?${queryParams.toString()}`;

      // Open the URL in a new window
      window.open(url, "_blank");
    },
    async downloadInternally() {
      // Select the element you want to screenshot
      const element = document.getElementById("screenshot-element");

      // Capture the screenshot
      html2canvas(element, { useCORS: true }).then((canvas) => {
        const imgData = canvas.toDataURL("image/png");
        const pdf = new jsPDF("l", "mm", "a4");
        const imgWidth = 297; // Width of A4 paper in mm (landscape)
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio
        // Add the captured image to the PDF
        pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
        pdf.save("performance-report.pdf"); // Save the PDF
      });
      return;
    },
  },
};
</script>
