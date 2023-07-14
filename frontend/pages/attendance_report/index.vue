<template>
  <div>
    <v-row>
      <v-radio-group row v-model="currentComponent">
        <v-radio
          @click="currentComponent == 'MimoAttendanceReport'"
          label="Multi In/Out Report"
          value="MimoAttendanceReport"
        ></v-radio>
        <v-radio
          @click="currentComponent == 'GeneralAttendanceReport'"
          label="General Report"
          value="GeneralAttendanceReport"
        ></v-radio>
      </v-radio-group>
    </v-row>
    <component :is="currentComponent" />
  </div>
</template>
<script>
import GeneralAttendanceReport from "../../components/attendance_report/GeneralAttendanceReport.vue";
import MimoAttendanceReport from "../../components/attendance_report/MimoAttendanceReport.vue";
export default {
  data() {
    return {
      currentComponent: "GeneralAttendanceReport",
    };
  },

  components: { GeneralAttendanceReport, MimoAttendanceReport },
};
</script>
