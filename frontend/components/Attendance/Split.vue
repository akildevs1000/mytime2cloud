<template>
  <v-container fluid>
    <v-data-table
      dense
      :headers="headers"
      :items="data"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [10, 30, 50, 100, 500, 1000],
        page: true,
      }"
      class="elevation-0"
      model-value="data.id"
      :server-items-length="totalRowsCount"
      fixed-header
      :height="tableHeight"
      no-data-text="No Data available. Click 'Generate' button to see the results"
    >
      <template
        v-slot:item.employee_name="{ item, index }"
        style="width: 300px"
      >
        <v-row no-gutters>
          <v-col
            style="
              padding: 5px;
              padding-left: 0px;
              width: 50px;
              max-width: 50px;
            "
          >
            <v-img
              style="
                border-radius: 50%;
                height: auto;
                width: 45px;
                max-width: 45px;
              "
              :src="
                item.employee.profile_picture
                  ? item.employee.profile_picture
                  : '/no-profile-image.jpg'
              "
            >
            </v-img>
          </v-col>
          <v-col style="padding: 10px">
            <div style="font-size: 13px">
              {{ item.employee.first_name ? item.employee.first_name : "---" }}
              {{ item.employee.last_name ? item.employee.last_name : "---" }}
            </div>
            <small style="font-size: 12px; color: #6c7184">
              {{
                item.employee.employee_id
                  ? item.employee.employee_id
                  : item?.employee_id
              }}
            </small>
          </v-col>
        </v-row>
      </template>

      <template v-slot:item.status="{ item }">
        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            {{ setStatusLabel(item.status) }}
            <div class="secondary-value" v-if="item.status == 'P'">
              {{ getShortShiftDetails(item) }}
            </div>

            <v-btn
              v-if="item.is_manual_entry"
              color="primary"
              text
              v-bind="attrs"
              v-on="on"
            >
              (ME)
            </v-btn>
          </template>
          <div>Reason: {{ item.last_reason?.reason }}</div>
          <div>Added By: {{ item.last_reason?.user?.email }}</div>
          <div>Created At: {{ item.last_reason?.created_at }}</div>
        </v-tooltip>
      </template>

      <template v-slot:item.shift="{ item }">
        <div>
          {{ item.shift && item.shift.on_duty_time }} -
          {{ item.shift && item.shift.off_duty_time }}
        </div>
        <div class="secondary-value">
          {{ (item.shift && item.shift.name) || "---" }}
          <span v-if="checkHalfday(item || `---`)">
            {{ `(Half Day ${item.shift.halfday_working_hours} hrs)` }}
          </span>
        </div>
        <!-- <v-tooltip v-if="item && item.shift" top color="primary">
            <template v-slot:activator="{ on, attrs }">
              <div class="primary--text" v-bind="attrs" v-on="on">
                <div>
                  {{ item.shift.on_duty_time }} - {{ item.shift.off_duty_time }}
                </div>
                {{ (item.shift && item.shift.name) || "---" }}
              </div>
            </template>
            <div v-for="(iterable, index) in item.shift" :key="index">
              <span v-if="index !== 'id'">
                {{ caps(index) }}: {{ iterable || "---" }}</span
              >
            </div>
          </v-tooltip>
          <span v-else>---</span> -->
      </template>
      <!-- <template v-slot:item.name="{ item }">
          {{ item.employee.first_name }} {{ item.employee.last_name }}
        </template> -->
      <template v-slot:item.date="{ item }">
        <div>{{ item.date }}</div>
        <div class="secondary-value">
          {{ item.day }}
        </div>
      </template>
      <template v-slot:item.in="{ item }">
        <div :class="`${item?.device_in?.name == 'Manual' ? 'red' : ''}--text`">
          <div>{{ item.in }}</div>
          <div class="secondary-value">
            <div
              v-if="
                item.device_in &&
                item.device_in.name &&
                item.device_in.name != '---'
              "
            >
              {{ item.device_in.name }}
            </div>
            <div v-else-if="item.device_id_in != '---'">
              {{ item.device_id_in }}
            </div>
            <div v-else>---</div>
          </div>
        </div>
      </template>
      <template v-slot:item.out="{ item }">
        <div
          :class="`${item?.device_out?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out }}</div>
          <div class="secondary-value">
            <div
              v-if="
                item.device_out &&
                item.device_out.name &&
                item.device_out.name != '---'
              "
            >
              {{ item.device_out.name }}
            </div>
            <div v-else-if="item.device_id_out != '---'">
              {{ item.device_id_out }}
            </div>
            <div v-else>---</div>

            <!-- {{ item.device_id_out == "Manual" ? "Manual" : "---" }} -->
          </div>
        </div>
      </template>
      <template v-slot:item.in1="{ item }">
        <div
          :class="`${item?.device_in1?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in1 }}</div>
          <div class="secondary-value">
            {{ (item.device_in1 && item.device_in1) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out1="{ item }">
        <div
          :class="`${item?.device_out1?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out1 }}</div>
          <div class="secondary-value">
            {{ (item.device_out1 && item.device_out1) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.in2="{ item }">
        <div
          :class="`${item?.device_in2?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in2 }}</div>
          <div class="secondary-value">
            {{ (item.device_in2 && item.device_in2) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out2="{ item }">
        <div
          :class="`${item?.device_in2?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out2 }}</div>
          <div class="secondary-value">
            {{ (item.device_out2 && item.device_out2) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.in3="{ item }">
        <div
          :class="`${item?.device_in3?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in3 }}</div>
          <div class="secondary-value">
            {{ (item.device_in3 && item.device_in3) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out3="{ item }">
        <div
          :class="`${item?.device_out3?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out3 }}</div>
          <div class="secondary-value">
            {{ (item.device_out3 && item.device_out3) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.in4="{ item }">
        <div
          :class="`${item?.device_in4?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in4 }}</div>
          <div class="secondary-value">
            {{ (item.device_in4 && item.device_in4) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out4="{ item }">
        <div
          :class="`${item?.device_out4?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out4 }}</div>
          <div class="secondary-value">
            {{ (item.device_out4 && item.device_out4) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.in5="{ item }">
        <div
          :class="`${item?.device_in5?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in5 }}</div>
          <div class="secondary-value">
            {{ (item.device_in5 && item.device_in5) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out5="{ item }">
        <div
          :class="`${item?.device_out5?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out5 }}</div>
          <div class="secondary-value">
            {{ (item.device_out5 && item.device_out5) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.in6="{ item }">
        <div
          :class="`${item?.device_in6?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in6 }}</div>
          <div class="secondary-value">
            {{ (item.device_in6 && item.device_in6) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out6="{ item }">
        <div
          :class="`${item?.device_out6?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out6 }}</div>
          <div class="secondary-value">
            {{ (item.device_out6 && item.device_out6) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.in7="{ item }">
        <div
          :class="`${item?.device_in7?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.in7 }}</div>
          <div class="secondary-value">
            {{ (item.device_in7 && item.device_in7) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.out7="{ item }">
        <div
          :class="`${item?.device_out7?.name == 'Manual' ? 'red' : ''}--text`"
        >
          <div>{{ item.out7 }}</div>
          <div class="secondary-value">
            {{ (item.device_out7 && item.device_out7) || "---" }}
          </div>
        </div>
      </template>
      <template v-slot:item.device_in="{ item }">
        <v-tooltip v-if="item && item.device_in" top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <div class="primary--text" v-bind="attrs" v-on="on">
              {{ (item.device_in && item.device_in.short_name) || "---" }}
            </div>
          </template>
          <div v-for="(iterable, index) in item.device_in" :key="index">
            <span v-if="index !== 'id'">
              {{ caps(index) }}: {{ iterable || "---" }}</span
            >
          </div>
        </v-tooltip>
        <span v-else>---</span>
      </template>

      <template v-slot:item.device_out="{ item }">
        <v-tooltip v-if="item && item.device_out" top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <div class="primary--text" v-bind="attrs" v-on="on">
              {{ (item.device_out && item.device_out.short_name) || "---" }}
            </div>
          </template>
          <div v-for="(iterable, index) in item.device_out" :key="index">
            <span v-if="index !== 'id'">
              {{ caps(index) }}: {{ iterable || "---" }}</span
            >
          </div>
        </v-tooltip>
        <span v-else>---</span>
      </template>
    </v-data-table>
  </v-container>
</template>
<script>
import headers from "../../headers/double.json";
export default {
  props: ["statuses", "branch_id", "from_date", "to_date"],

  data: () => ({
    headers: headers.filter((e) => e.value !== "actions"),
    donwload_pdf_file: "",
    view_pdf_file: "",
    key: 1,
    tableHeight: 750,
    totalRowsCount: 0,
    date: null,
    options: { page: 1 },
    loading: false,
    Model: "Attendance Reports",
    dialog: false,
    total: 0,
    response: "",
    data: [],
    clearPagenumber: false,
  }),

  watch: {
    branch_id(value) {
      this.getDataFromApi();
    },

    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  mounted() {
    this.tableHeight = window.innerHeight - 370;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 370;
    });
  },

  methods: {
    checkHalfday(item) {
      let currentDay = new Date().toLocaleString("en-US", {
        weekday: "long",
      });

      return item.shift && currentDay === item.shift.halfday;
    },

    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDataFromApi() {
      let { page, itemsPerPage } = this.options;

      this.loading = true;

      let payload = {
        page: page,
        per_page: itemsPerPage,
        company_id: this.$auth.user.company_id,
        shift_type_id: 5,
        report_type: "Monthly",
        filterType: "Monthly",
        statuses: this.statuses,
        branch_id: this.branch_id,
        from_date: this.from_date,
        to_date: this.to_date,
      };

      let endpoint = `attendance-report-new`;

      this.$axios.post(endpoint, payload).then(({ data }) => {
        if (data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }

        this.data = data.data;
        this.total = data.total;
        this.loading = false;
        this.totalRowsCount = data.total;

        if (this.clearPagenumber) {
          this.options.page = 1;
          this.clearPagenumber = false;
        }
      });
    },
    getShortShiftDetails(item) {
      if (item.shift) {
        let shiftWorkingHours = item.shift.working_hours;
        let employeeHours = item.total_hrs;

        if (
          shiftWorkingHours != "" &&
          employeeHours != "" &&
          shiftWorkingHours != "---" &&
          employeeHours != "---"
        ) {
          let [hours, minutes] = shiftWorkingHours.split(":").map(Number);
          shiftWorkingHours = hours * 60 + minutes;

          [hours, minutes] = employeeHours.split(":").map(Number);
          employeeHours = hours * 60 + minutes;

          if (
            employeeHours < shiftWorkingHours &&
            !this.checkHalfday(item || `---`)
          ) {
            return "Short Shift";
          }
        }
      }
    },
    setStatusLabel(status) {
      const statuses = {
        A: "Absent",
        P: "Present",
        M: "Missing",
        LC: "Late In",
        EG: "Early Out",
        O: "Week Off",
        L: "Leave",
        H: "Holiday",
        V: "Vaccation",
      };
      return statuses[status];
    },
  },
};
</script>
