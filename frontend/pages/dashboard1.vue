<template>
  <div>
    <v-row v-if="!loading">
      <v-col cols="12" xs="12" sm="12" md="4">
        <v-card
          dark
          dense
          style="border-radius: 15px !important"
          class="green-gradient-bg pa-8 text-center"
        >
          <div>
            <h2>Today Summary</h2>
            <h3>05</h3>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4">
        <v-card
          dark
          dense
          style="border-radius: 15px !important"
          class="green-gradient-bg pa-8 text-center"
        >
          <div>
            <h2>Today Present</h2>
            <h3>05</h3>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4">
        <v-card
          dark
          dense
          style="border-radius: 15px !important"
          class="green-gradient-bg pa-5 text-center"
        >
          <div>
            <h2>Today Missing</h2>
            <h3>05</h3>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4">
        <v-card
          dark
          dense
          style="border-radius: 15px !important"
          class="orange-gradient-bg pa-5 text-center"
        >
          <div>
            <h2>Employee on Leave</h2>
            <h3>05</h3>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4">
        <v-card
          dark
          dense
          style="border-radius: 15px !important"
          class="orange-gradient-bg pa-5 text-center"
        >
          <div>
            <h2>Employee on Holiday</h2>
            <h3>05</h3>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4">
        <v-card
          dark
          dense
          style="border-radius: 15px !important"
          class="orange-gradient-bg pa-5 text-center"
        >
          <div>
            <h2>Today Employees</h2>
            <h3>05</h3>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="6">
        <AttendanceLog />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="6">
        <Announcement />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="6">
        <Device />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="6">
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

export default {
  components: { Announcement, AttendanceLog, Device, ReportNotification },
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
  created() {
    // this.loading = true;
    // let options = {
    //   company_id: this.$auth.user.company.id,
    // };
    // this.$axios.get(`count`, { params: options }).then(({ data }) => {
    //   this.items = data;
    //   if (this.items.length > 0) {
    //     this.loading = false;
    //   }
    // });
  },
};
</script>

<style scoped>
/* Gradient background styles */
.green-gradient-bg {
  background: linear-gradient(to right, rgb(140, 82, 255), rgb(0, 191, 99));

  /* background: linear-gradient(90deg, rgb(140, 82, 255) 0%, rgb(255, 145, 77) 100%) 0px 0px / 112.389px 38.213px */
}
.orange-gradient-bg {
  background: linear-gradient(to right, rgb(140, 82, 255), rgb(255, 145, 77));
}
</style>
