<template>
  <v-tabs right background-color="popup_background">
    <v-tab>Attendance</v-tab>
    <v-tab>Summary</v-tab>
    <v-tab>Performance</v-tab>
    <v-tab-item class="popup_background">
      <div class="mt-5" v-if="can(`attendance_report_access`)">
        <ReportsAttendance />
      </div>
      <NoAccess v-else />
    </v-tab-item>
    <v-tab-item class="popup_background">
      <div class="mt-5" v-if="can(`summary_report_access`)">
        <ReportsSummary />
      </div>
      <NoAccess v-else />
    </v-tab-item>
    <v-tab-item class="popup_background">
      <div class="mt-5" v-if="can(`performance_report_access`)">
        <ReportsPerformance />
      </div>
      <NoAccess v-else />
    </v-tab-item>
  </v-tabs>
</template>
<script>
export default {
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
