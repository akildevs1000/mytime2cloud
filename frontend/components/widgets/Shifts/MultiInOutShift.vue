<template>
  <v-row>
    <v-col cols="12" md="3">
      <TimePickerCommon
        label="On Duty Time"
        :default_value="payload.on_duty_time"
        @getTime="(value) => (payload.on_duty_time = value)"
      />
      <span v-if="errors && errors.off_duty_time" class="text-danger">{{
        errors.off_duty_time[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="3">
      <TimePickerCommon
        label="Off Duty Time"
        :default_value="payload.off_duty_time"
        @getTime="(value) => (payload.off_duty_time = value)"
      />
      <span v-if="errors && errors.off_duty_time" class="text-danger">{{
        errors.off_duty_time[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="3">
      <TimePickerCommon
        label="Min working hrs"
        :default_value="payload.working_hours"
        @getTime="(value) => (payload.working_hours = value)"
      />
      <span v-if="errors && errors.working_hours" class="text-danger">{{
        errors.working_hours[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="3">
      <TimePickerCommon
        label="OT start after duty hrs"
        :default_value="payload.overtime_interval"
        @getTime="(value) => (payload.overtime_interval = value)"
      />
      <span v-if="errors && errors.overtime_interval" class="text-danger">{{
        errors.overtime_interval[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="12">
      <DaysPickerCommon
        label="Working Days"
        :default_value="payload.days"
        @selectedDays="(value) => (payload.days = value)"
      />
      <span v-if="errors && errors.days" class="text-danger">{{
        errors.days[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="4">
      <WeekendPickerCommon
        label="Weekend 1"
        @selectedWeekend="(value) => (payload.weekend_1 = value)"
      />
      <span v-if="errors && errors.weekend_1" class="text-danger">{{
        errors.weekend_1[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="4">
      <WeekendPickerCommon
        label="Weekend 2"
        @selectedWeekend="(value) => (payload.weekend_2 = value)"
      />
      <span v-if="errors && errors.weekend_2" class="text-danger">{{
        errors.weekend_2[0]
      }}</span>
    </v-col>
    <v-col cols="12" md="4">
      <MonthlyFlexiHolidays
        label="Monthly Flexible Holidays"
        @selectedMonthlyHolidays="(value) => (payload.weekend_3 = value)"
      />
      <span v-if="errors && errors.weekend_3" class="text-danger">{{
        errors.weekend_3[0]
      }}</span>
    </v-col>
  </v-row>
</template>

<script>
import TimePickerCommon from "../../Snippets/TimePickerCommon.vue";
import DaysPickerCommon from "../../Snippets/DaysPickerCommon.vue";
import WeekendPickerCommon from "../../Snippets/WeekendPickerCommon.vue";
import MonthlyFlexiHolidays from "../../Snippets/MonthlyFlexiHolidays.vue";

export default {
  props: ["payload", "errors"],
  components: {
    TimePickerCommon,
    DaysPickerCommon,
    WeekendPickerCommon,
    MonthlyFlexiHolidays,
  },
};
</script>
