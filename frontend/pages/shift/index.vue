<template>
  <div v-if="can(`shift_access`)">
    <style scoped>
      .custom-input {
        padding: 6px 10px;
        height: 30px;
        position: relative;
        border-radius: 5px;
        border: 1px solid grey;
        font-size: 16px;
        transition: border-color 0.3s ease-in-out;
        outline: none;
        /* Remove default outline */
      }

      .custom-input:focus {
        border-color: purple;
      }
    </style>

    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <!-- <Back class="primary white--text" /> -->

    <v-dialog persistent v-model="showDialog" width="1200">
      <WidgetsClose @click="showDialog = false" left="1190" />
      <v-card style="background-color: #ddd !important">
        <style scoped>
          .card-wrapper {
            height: 700px; /* Or any fixed height like 500px */
            display: flex;
            flex-direction: column;
          }
          .fill {
            height: 100%;
          }
        </style>
        <div class="card-wrapper">
          <v-container fluid class="fill pb-0">
            <v-row class="fill" align="stretch">
              <v-col cols="8" class="fill">
                <v-card class="fill d-flex flex-column">
                  <v-alert dense flat dark class="primary">
                    {{ Model }}
                  </v-alert>
                  <v-card-text class="flex-grow-1 overflow-auto pt-1">
                    <v-row>
                      <v-col cols="12">
                        <v-checkbox
                          hide-details
                          small
                          color="primary"
                          v-model="payload.isAutoShift"
                          label="Auto Shift"
                          dense
                        >
                        </v-checkbox>
                      </v-col>
                      <v-col md="3" sm="12" cols="12">
                        <label
                          >Type of Schedule
                          <span class="error--text">*</span></label
                        >
                        <v-select
                          @change="getRelatedShiftComponent"
                          v-model="payload.shift_type_id"
                          :items="[
                            { id: 1, name: `Flexible` },
                            { id: 4, name: `Night` },
                            { id: 6, name: `Single` },
                            { id: 5, name: `Dual` },
                            { id: 2, name: `Multi` },
                          ]"
                          item-value="id"
                          item-text="name"
                          :hide-details="true"
                          dense
                          outlined
                        ></v-select>
                        <span
                          v-if="errors && errors.shift_type_id"
                          class="text-danger"
                          >{{ errors.shift_type_id[0] }}</span
                        >
                      </v-col>
                      <!-- <v-col v-if="isCompany" md="3" sm="12" cols="12">
              <label>Branch <span class="error--text">*</span></label>
              <v-select
                clearable
                :hide-details="true"
                outlined
                dense
                small
                v-model="payload.branch_id"
                item-text="name"
                item-value="id"
                :items="branchList"
                placeholder="Branch"
                solo
                flat
              ></v-select>
              <span v-if="errors && errors.branch_id" class="text-danger">{{
                errors.branch_id[0]
              }}</span>
            </v-col> -->

                      <v-col md="3" sm="12" cols="12">
                        <label
                          >Name of Schedule<span class="error--text"
                            >*</span
                          ></label
                        >
                        <v-text-field
                          v-model="payload.name"
                          :hide-details="true"
                          dense
                          outlined
                        ></v-text-field>
                        <span
                          v-if="errors && errors.name"
                          class="text-danger"
                          >{{ errors.name[0] }}</span
                        >
                      </v-col>

                      <v-col
                        v-if="
                          payload.shift_type_id == 4 ||
                          payload.shift_type_id == 6
                        "
                        md="3"
                        sm="12"
                        cols="12"
                      >
                        <label>Overtime Type</label>
                        <v-select
                          v-model="payload.overtime_type"
                          :items="[`Both`, `Before`, `After`]"
                          :hide-details="true"
                          dense
                          outlined
                        ></v-select>
                        <span
                          v-if="errors && errors.overtime_type"
                          class="text-danger"
                          >{{ errors.overtime_type[0] }}</span
                        >
                      </v-col>

                      <v-col cols="12">
                        <SplitShift
                          v-if="payload.shift_type_id == 5"
                          :key="renderComponent"
                          :shift_type_id="payload.shift_type_id"
                          :name="payload.name"
                          @success="getDataFromApi"
                          :payload="payload"
                          @close-popup="showDialog = false"
                        />
                        <component
                          v-if="payload.shift_type_id != 5"
                          :key="renderComponent"
                          :errors="errors"
                          :payload="payload"
                          :is="comp"
                        />
                      </v-col>
                    </v-row>
                    <v-row
                      v-if="can(`shift_create`) && payload.shift_type_id != 5"
                    >
                      <v-col cols="12" style="float: right; text-align: right">
                        <v-btn
                          v-if="payload && payload.id > 0"
                          small
                          color="primary"
                          @click="update"
                        >
                          Update
                        </v-btn>

                        <v-btn v-else small color="primary" @click="submit">
                          Submit
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>

              <v-col class="fill">
                <v-card class="fill d-flex flex-column">
                  <v-alert dense flat dark class="primary">
                    Weekly Duty Schedule
                  </v-alert>
                  <v-card-text class="flex-grow-1 overflow-auto">
                    <v-row no-gutters v-for="(day, index) in days" :key="index">
                      <v-col cols="2">
                        <div>
                          {{ day }}
                        </div>
                      </v-col>
                      <v-col class="text-right pt-1">
                        <div
                          style="
                            display: flex;
                            height: 13px;
                            overflow: hidden;
                            border-radius: 5px;
                          "
                          v-if="index > 0 && flexLayout.isNight"
                        >
                          <div
                            v-for="(item, index) in nightSegment(day)"
                            :key="index"
                            :style="item"
                          ></div>
                        </div>

                        <div
                          style="
                            display: flex;
                            height: 13px;
                            overflow: hidden;
                            border-radius: 5px;
                          "
                          v-else
                        >
                          <div
                            v-for="(item, index) in segment(day)"
                            :key="index"
                            :style="item"
                          ></div>
                        </div>
                      </v-col>
                    </v-row>

                    <v-row no-gutters class="mt-5" align="stretch">
                      <v-col cols="12">
                        <v-card elevation="0" flat>
                          <v-card-text>
                            <v-row no-gutters>
                              <v-col>
                                <div>Schedule Name:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>{{ payload.name || "---" }}</div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Type Schedule -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Type Schedule:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  {{
                                    [
                                      { id: 1, name: `Flexible` },
                                      { id: 4, name: `Night` },
                                      { id: 6, name: `Single` },
                                      { id: 5, name: `Dual` },
                                      { id: 2, name: `Multi` },
                                    ].find((e) => e.id == payload.shift_type_id)
                                      ?.name
                                  }}
                                </div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Duty Hours -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Duty Hours:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  {{ payload.on_duty_time }} to
                                  {{ payload.off_duty_time }}
                                </div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Week Days -->
                            <v-row no-gutters>
                              <v-col cols="3">
                                <div>Days:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  {{
                                    payload?.days?.length
                                      ? payload.days.join(",")
                                      : "---"
                                  }}
                                </div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Half Day -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Half Day:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>{{ payload.halfday }}</div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Half Day Duty Hours -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Half Day Duty Hours:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>{{ payload.halfday_working_hours }}</div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Week End 1 -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Week End 1:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>{{ payload.weekend1 }}</div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Week End 2 -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Week End 2:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>{{ payload.weekend2 }}</div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Monthly Flexible Holidays -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Monthly Flexible Holidays:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  {{
                                    payload.monthly_flexi_holidays == 0
                                      ? "Not Applicable"
                                      : payload.monthly_flexi_holidays + " Day"
                                  }}{{
                                    payload.monthly_flexi_holidays > 1
                                      ? "s"
                                      : ""
                                  }}
                                </div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Late Grace Time -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Late Grace Time:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  {{
                                    `${
                                      payload.shift_type_id == 4 ||
                                      payload.shift_type_id == 6
                                        ? payload.late_time + " Min"
                                        : "Not Applicable"
                                    }`
                                  }}
                                </div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Early Grace Time -->
                            <v-row no-gutters>
                              <v-col>
                                <div>Early Grace Time:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  {{
                                    `${
                                      payload.shift_type_id == 4 ||
                                      payload.shift_type_id == 6
                                        ? payload.early_time + " Min"
                                        : "Not Applicable"
                                    }`
                                  }}
                                </div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- OT -->
                            <v-row no-gutters>
                              <v-col>
                                <div>OT Type:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>{{ payload.overtime_type || "---" }}</div>
                              </v-col>
                            </v-row>
                            <v-divider></v-divider>
                            <!-- Condition -->
                            <v-row no-gutters>
                              <v-col cols="3">
                                <div>Condition:</div>
                              </v-col>
                              <v-col class="text-right">
                                <div>
                                  Over time starts {{ payload.overtime_type }}
                                  {{ payload.overtime_interval }} mins only
                                </div>
                              </v-col>
                            </v-row>
                          </v-card-text>
                        </v-card>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-container>
        </div>
      </v-card>
    </v-dialog>
    <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
      {{ snackText }}

      <template v-slot:action="{ attrs }">
        <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
      </template>
    </v-snackbar>

    <v-card elevation="0" class="mt-2" v-if="can(`shift_view`)">
      <v-row>
        <v-col>
          <b class="ml-5" style="font-size: 18px; font-weight: 600">{{
            Model
          }}</b>
          <span>
            <v-btn
              dense
              class="ma-0 px-0"
              x-small
              :ripple="false"
              text
              title="Filter"
            >
              <v-icon @click="getDataFromApi()" class="mx-1 ml-2"
                >mdi mdi-reload</v-icon
              >
            </v-btn>
          </span>
        </v-col>
        <v-col class="text-right">
          <div class="input-group" style="width: 100%">
            <input
              class="custom-input"
              type="text"
              placeholder="Search"
              @input="searchData"
              v-model="filters.search"
            />
            <v-icon style="position: absolute; top: 16px; right: 107px"
              >mdi-magnify</v-icon
            >
            <v-btn
              style="margin-top: -6px"
              class="primary"
              small
              @click="goToCreate"
              v-if="can(`shift_create`)"
              >+ New</v-btn
            >
            <v-menu offset-y :nudge-width="100">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  dark-2
                  icon
                  v-bind="attrs"
                  v-on="on"
                  style="margin-top: -9px"
                >
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list dense>
                <v-list-item @click="export_submit">
                  <v-list-item-title
                    style="cursor: pointer; display: flex; align-items: center"
                  >
                    <div style="height: 17px; width: 17px">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                        class="icon align-text-top"
                      >
                        <path
                          fill="#6946dd"
                          d="M447.6 270.8c-8.8 0-15.9 7.1-15.9 15.9v142.7H80.4V286.8c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v158.6c0 8.8 7.1 15.9 15.9 15.9h383.1c8.8 0 15.9-7.1 15.9-15.9V286.8c0-8.8-7.1-16-15.9-16z"
                        ></path>
                        <path
                          fill="#6946dd"
                          d="M244.7 328.4c.4.4.8.7 1.2 1.1.2.1.4.3.5.4.2.2.5.4.7.5.2.1.4.3.7.4.2.1.4.3.7.4.2.1.5.2.7.3.2.1.5.2.7.3.2.1.5.2.7.3.3.1.5.2.8.3.2.1.5.1.7.2.3.1.5.1.8.2.3.1.6.1.8.1.2 0 .5.1.7.1.5.1 1 .1 1.6.1s1 0 1.6-.1c.2 0 .5-.1.7-.1.3 0 .6-.1.8-.1.3-.1.5-.1.8-.2.2-.1.5-.1.7-.2.3-.1.5-.2.8-.3.2-.1.5-.2.7-.3.2-.1.5-.2.7-.3.2-.1.5-.2.7-.3.2-.1.5-.3.7-.4.2-.1.4-.3.7-.4.3-.2.5-.4.7-.5.2-.1.4-.3.5-.4.4-.3.8-.7 1.2-1.1l95-95c6.2-6.2 6.2-16.3 0-22.5-6.2-6.2-16.3-6.2-22.5 0L272 278.7v-212c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v212l-67.8-67.8c-6.2-6.2-16.3-6.2-22.5 0-6.2 6.2-6.2 16.3 0 22.5l94.8 95z"
                        ></path>
                      </svg>
                    </div>

                    <div style="margin: 4px 0 0 5px">
                      <span style="font-size: 12px">{{ Model }}</span>
                    </div>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </v-col>
        <v-col cols="12">
          <v-data-table
            dense
            :server-items-length="total"
            :headers="headers"
            :items="data"
            model-value="data.id"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [20, 50, 100, 500, 1000],
            }"
          >
            <template v-slot:item.sno="{ item, index }">
              {{ ++index }}
            </template>

            <template v-slot:item.scheduled_time="{ item, index }">
              {{ item.on_duty_time }} to {{ item.off_duty_time }}
              <span v-if="item.shift_type_id == 5">
                -
                {{ item.on_duty_time1 }} to {{ item.off_duty_time1 }}
              </span>
            </template>

            <template v-slot:item.isAutoShift="{ item, index }">
              <v-icon v-if="item.isAutoShift" color="green">mdi-check</v-icon>
              <v-icon v-else color="red">mdi-close</v-icon>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <div class="text-center">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </div>
                </template>
                <v-list width="120" dense>
                  <v-list-item v-if="can(`shift_edit`)" @click="editItem(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-pencil </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    v-if="can(`shift_delete`)"
                    @click="deleteItem(item)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="error" small> mdi-delete </v-icon>
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-card>

    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
import DatePickerCommon from "../../components/Snippets/DatePickerCommon.vue";
import Back from "../../components/Snippets/Back.vue";
import headers from "../../menus/shift.json";
import defaults from "../../defaults/shift.json";

import SplitShift from "../../components/widgets/Shifts/SplitShift.vue";

const currentDate = new Date();
const nextYearDate = new Date(currentDate);
nextYearDate.setFullYear(currentDate.getFullYear() + 1);

export default {
  components: { Back, DatePickerCommon, SplitShift },

  data: () => ({
    days: ["MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN"],
    showDialog: false,
    branchList: [],
    isFilter: false,
    filters: {
      search: null,
    },
    shifts: [],
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    employee: {},
    defaults,
    currentDate,
    nextYearDate,

    payload: {
      shift_type_id: 1,
      overtime_type: `Both`,
    },
    isNew: true,
    options: {},
    Model: "Shift & Schedule",
    endpoint: "shift",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    headers,
    response: "",
    data: [],
    errors: [],
    renderComponent: 0,
    branch_id: 0,
    isCompany: true,
    comp: "",
    colors: {
      dutyHoursColor: "#00b050",
      emptyColor: "#ddd",
      otColor: "purple",
      leftBorderColor: "#000",
      rightBorderColor: "orange",
    },
    transition: "flex 0.5s ease",
  }),

  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
    "payload.days"(newVal, oldVal) {
      // Your logic when payload.days changes
      console.log("payload.days changed:", oldVal, "→", newVal);
    },
  },
  async created() {
    this.loading = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }

    this.getComponent();
  },
  computed: {
    flexLayout() {
      const onTime = this.payload?.on_duty_time ?? "00:00";
      const offTime = this.payload?.off_duty_time ?? "00:00";
      const otInterval = this.payload?.overtime_interval ?? "00:00";

      const [onHour, onMin] = onTime.split(":").map(Number);
      const [offHour, offMin] = offTime.split(":").map(Number);
      const [otHours, otMinutes] = otInterval.split(":").map(Number);

      const start = onHour + onMin / 60; // e.g. 09:30 => 9.5
      const end = offHour + offMin / 60; // e.g. 18:00 => 18
      const otExtra = otHours + otMinutes / 60; // e.g., 0.5 for 00:30

      const dutyHours = end - start;
      const beforeDuty = start;
      const afterDuty = 24 - end;

      let Previous =
        dutyHours < 0 ? Math.abs(afterDuty) - Math.abs(dutyHours) : dutyHours;

      return {
        otExtra,
        beforeDuty,
        dutyHours: Previous,
        afterDuty: dutyHours < 0 ? 0 : afterDuty,
        isNight: end < start,
        emptyColor: "#ddd",
        transition: "flex 0.5s ease",
        night: {
          otExtra,
          beforeDuty: 0,
          dutyHours: Math.abs(Math.abs(Previous) - Math.abs(dutyHours)),
          afterDuty: afterDuty - Previous,
          Previous,
        },
      };
    },
  },
  methods: {
    nightSegment(day) {
      let { isNight, night, otExtra } = this.flexLayout;
      let { overtime_type } = this.payload;

      let exceptAfterDuty = overtime_type !== "After";
      let exceptBeforeDuty = overtime_type !== "Before";
      let totalOtMultiplier =
        overtime_type == "Both" || overtime_type == undefined
          ? otExtra * 2
          : otExtra;

      let result = [
        {
          flex: night?.dutyHours,
          backgroundColor: this.colors.dutyHoursColor,
          transition: this.transition,
          borderRight: `3px solid ${this.colors.rightBorderColor}`,
        },
        {
          flex: exceptAfterDuty ? otExtra : 0,
          backgroundColor: this.colors.emptyColor,
          transition: this.transition,
        },
        {
          flex: exceptAfterDuty
            ? Math.abs(night?.dutyHours + night?.Previous - totalOtMultiplier)
            : 0,
          backgroundColor: this.colors.otColor,
          transition: this.transition,
        },
        {
          flex: exceptBeforeDuty
            ? Math.abs(night?.dutyHours + night?.Previous - totalOtMultiplier)
            : 0,
          backgroundColor: this.colors.otColor,
          transition: this.transition,
        },
        {
          flex: exceptBeforeDuty ? otExtra : 0,
          backgroundColor: this.colors.emptyColor,
          transition: this.transition,
        },
        {
          flex: night?.Previous,
          backgroundColor: this.colors.dutyHoursColor,
          transition: this.transition,
          borderLeft: `3px solid ${this.colors.leftBorderColor}`,
          borderRight: !isNight
            ? `5px solid ${this.colors.rightBorderColor}`
            : "0px",
        },
      ];
      return result;
    },
    segment(day) {
      let { isNight, otExtra, dutyHours } = this.flexLayout;
      let { overtime_type } = this.payload;

      let exceptAfterDuty = !isNight && overtime_type !== "After";
      let exceptBeforeDuty = !isNight && overtime_type !== "Before";

      let isBoth = overtime_type == "Both" || overtime_type == undefined;

      let multiplier = isBoth ? 2 : 1;

      let busyArea = dutyHours + otExtra * multiplier;

      let {
        otColor,
        emptyColor,
        dutyHoursColor,
        leftBorderColor,
        rightBorderColor,
      } = this.colors;

      let firstRow = (24 - busyArea) / (isNight ? 1 : multiplier);

      let result = [
        {
          flex: isNight ? firstRow + otExtra : firstRow,
          backgroundColor: isNight
            ? otColor
            : exceptAfterDuty
            ? otColor
            : emptyColor,
          transition: this.transition,
        },
        {
          flex: otExtra,
          backgroundColor: emptyColor,
          transition: this.transition,
        },
        {
          flex: dutyHours,
          backgroundColor: dutyHoursColor,
          transition: this.transition,
          borderLeft: `3px solid ${leftBorderColor}`,
          borderRight:
            (!isNight ? "3px" : "0px") + ` solid ${rightBorderColor}`,
        },
        {
          flex: isNight ? 0 : otExtra,
          backgroundColor: emptyColor,
          transition: this.transition,
        },
        {
          flex: isNight ? 0 : (24 - busyArea) / multiplier,
          backgroundColor: exceptBeforeDuty ? otColor : emptyColor,
          transition: this.transition,
        },
      ];

      return result;
    },
    searchData() {
      if (this.filters.search.length == 0 || this.filters.search.length > 3) {
        this.getDataFromApi();
      }
    },
    json_to_csv(json) {
      let data = json.map((e) => ({
        "Shift Name": e.name,
        "Shift Type": e.shift_type.name,
        Time: e.scheduled_time,
        "From Date": e.show_from_date,
        "To Date": e.show_to_date,
        "Auto Shift": e.isAutoShift ? "Yes" : "No",
        "Half Day": e.halfday,
        "Half Day Working Hours": e.halfday_working_hours,
      }));
      let header = Object.keys(data[0]).join(",") + "\n";
      let rows = "";
      data.forEach((e) => {
        rows += Object.values(e).join(",").trim() + "\n";
      });
      return header + rows;
    },
    export_submit() {
      if (this.data.length == 0) {
        this.snackbar = true;
        this.response = "No record to download";
        return;
      }

      let csvData = this.json_to_csv(this.data);
      let element = document.createElement("a");
      element.setAttribute(
        "href",
        "data:text/csv;charset=utf-8, " + encodeURIComponent(csvData)
      );
      element.setAttribute("download", "download.csv");
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    },
    async handleChangeEvent() {
      this.branchList = await this.$store.dispatch("fetchDropDowns", {
        key: "branchList",
        endpoint: "branch-list",
      });
    },
    getRelatedShiftComponent() {
      this.payload = {
        shift_type_id: this.payload.shift_type_id,
        ...this.defaults[this.payload.shift_type_id],
        // branch_id: this.branch_id,
      };
      this.renderComponent = Math.random() * (1000 - 1) + 1;
      this.getComponent();
    },
    getComponent() {
      switch (this.payload.shift_type_id) {
        case 6:
          this.comp = "widgetsShiftsSingleShift";
          break;
        case 4:
          this.comp = "widgetsShiftsOverNightShift";
          break;
        case 3:
          this.comp = "widgetsShiftsAutoShift";
          break;
        case 2:
          this.comp = "widgetsShiftsMultiInOutShift";
          break;
        default:
          this.comp = "widgetsShiftsFiloShift";
          break;
      }
    },
    async toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;

      if (this.isFilter) {
        this.refresh = true;
        this.handleChangeEvent();
      }
    },
    goToCreate() {
      this.isNew = true;
      this.payload = {
        shift_type_id: 1,
        branch_id: this.branch_id,
        ...this.defaults[this.payload.shift_type_id],
      };

      this.renderComponent = Math.random() * (1000 - 1) + 1;
      this.showDialog = true;
      this.getComponent();

      this.refresh = true;
      this.handleChangeEvent();
      // this.$router.push(`/shift/create`);
    },
    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    getRecords(filter_column = "", filter_value = "") {
      this.getDataFromApi(this.endpoint, filter_column, filter_value);
    },
    applyFilters() {
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          ...this.filters,
        },
      };
      if (filter_column != "") options.params[filter_column] = filter_value;

      this.$axios.get(url, options).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.data = data.data;
        this.total = data.total;
        this.loading = false;
      });
    },

    editItem(item) {
      this.isNew = false;
      this.renderComponent = Math.random() * (1000 - 1) + 1;
      this.payload = item;

      this.payload.from_date = new Date(item.from_date);
      this.payload.to_date = new Date(item.to_date);

      this.currentDate = item.from_date;
      this.nextYearDate = item.to_date;

      this.showDialog = true;
      this.getComponent();
      this.refresh = true;
      this.handleChangeEvent();
    },

    delteteSelectedRecords() {
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: this.ids.map((e) => e.id),
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
          })
          .catch((err) => console.log(err));
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },
    submit() {
      this.payload.company_id = this.$auth.user.company_id;

      if (!this.payload.from_date) {
        this.payload.from_date = this.currentDate;
      }
      if (!this.payload.to_date) {
        this.payload.to_date = this.nextYearDate;
      }

      this.loading = true;
      this.$axios
        .post(`/shift`, this.payload)
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Shift added successfully";
            this.showDialog = false;
            this.getDataFromApi();
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
          this.showDialog = false;
        });
    },
    update() {
      this.loading = true;
      this.$axios
        .put(`/shift/${this.payload.id}`, this.payload)
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Shift update successfully";
            this.showDialog = false;
            this.getDataFromApi();
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
  },
};
</script>
