<template>
  <div>
    <v-row>
      <v-col cols="12" xs="12" sm="12" md="4" lg="4" xl="4">
        <AttendancePIE />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4" lg="4" xl="4">
        <Announcement />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4" lg="4" xl="4">
        <DevicePIE />
      </v-col>

      <v-col cols="12">
        <AttendanceLog />
        <Device />
        <ReportNotification />
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Announcement from "../components/widgets/Announcement.vue";
import AttendanceLog from "../components/widgets/AttendanceLog.vue";
import Device from "../components/widgets/Device.vue";
import ReportNotification from "../components/widgets/ReportNotification.vue";
import AttendancePIE from "../components/widgets/AttendancePIE.vue";
import DevicePIE from "../components/widgets/DevicePIE.vue";
import DailyLog from "../components/widgets/DailyLog.vue";

export default {
  components: {
    DailyLog,
    Announcement,
    AttendanceLog,
    Device,
    ReportNotification,
    AttendancePIE,
    DevicePIE,
  },
  date: () => ({
    loading: true,
    items: [
      {
        title: "Today Summary",
        value: 0,
        icon: "fas fa-clock",
        color: "l-bg-purple-dark",
        link: "http://localhost:8000/api/daily?company_id=8&status=SA&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        multi_in_out:
          "http://localhost:8000/api/multi_in_out_daily?company_id=8&status=SA&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        total_employees_count: 7,
      },
      {
        title: "Today Presents",
        value: 0,
        icon: "fas fa-calendar-check",
        color: "l-bg-green-dark ",
        link: "http://localhost:8000/api/daily?page=1&per_page=1000&company_id=8&status=P&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        multi_in_out:
          "http://localhost:8000/api/multi_in_out_daily?page=1&per_page=1000&company_id=8&status=P&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        total_employees_count: 7,
      },
      {
        title: "Today Absent",
        value: 0,
        icon: "fas fa-calendar-times",
        color: "l-bg-orange-dark",
        link: "http://localhost:8000/api/daily?page=1&per_page=1000&company_id=8&status=A&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        multi_in_out:
          "http://localhost:8000/api/multi_in_out_daily?page=1&per_page=1000&company_id=8&status=A&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        total_employees_count: 7,
      },
      {
        title: "Today Missing",
        value: 0,
        icon: "\tfas fa-clock",
        color: "l-bg-cyan-dark",
        link: "http://localhost:8000/api/daily?page=1&per_page=1000&company_id=8&status=M&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        multi_in_out:
          "http://localhost:8000/api/multi_in_out_daily?page=1&per_page=1000&company_id=8&status=M&daily_date=2023-08-04&department_id=-1&report_type=Daily",
        total_employees_count: 7,
      },
    ],
  }),
};
</script>