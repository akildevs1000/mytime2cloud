<template>
  <div>
    <!-- <GenerateLog /> -->
    <div v-if="this.$store.state.main_report_type == 'General Report'">
      <GeneralAttendanceReport
        :main_report_type="this.$store.state.main_report_type"
      />
    </div>
    <div v-else>
      <MimoAttendanceReport
        :main_report_type="this.$store.state.main_report_type"
      />
    </div>
  </div>
</template>
<script>
import GeneralAttendanceReport from "../../components/attendance_report/GeneralAttendanceReport.vue";
import MimoAttendanceReport from "../../components/attendance_report/MimoAttendanceReport.vue";
export default {
  data() {
    return {
      txt: ""
    };
  },
  created() {
    console.log(this.txt == "General Report");
  },

  computed: {
    getmeth() {
      this.txt = this.$store.state.main_report_type;
      console.log(this.txt);
    }
  },

  methods: {},
  components: { GeneralAttendanceReport, MimoAttendanceReport }
};
</script>
