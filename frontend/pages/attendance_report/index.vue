<template>
  <div>
    <!-- <GenerateLog /> -->
    <div v-if="this.$store.state.main_report_type == 'General Report'">
      <GeneralAttendanceReport
        :main_report_type_props="this.$store.state.main_report_type"
      />
    </div>
    <div v-else>
      <MimoAttendanceReport
        :main_report_type_props="this.$store.state.main_report_type"
      />
    </div>
  </div>
</template>
<script>
import GeneralAttendanceReport from "../../components/attendance_report/GeneralAttendanceReport.vue";
import MimoAttendanceReport from "../../components/attendance_report/MimoAttendanceReport.vue";
export default {
  data() {
    return {};
  },
  created() {},

  computed: {
    getmeth() {
      this.txt = this.$store.state.main_report_type;
    },
  },

  components: { GeneralAttendanceReport, MimoAttendanceReport },
};
</script>
