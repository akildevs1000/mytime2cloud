<template>
  <v-row>
    <v-col cols="12" sm="6" md="4">
      <v-menu
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        transition="scale-transition"
        offset-y
        min-width="auto"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="startDate"
            label="Picker in menu"
            prepend-icon="mdi-calendar"
            readonly
            v-bind="attrs"
            v-on="on"
          ></v-text-field>
        </template>
        <v-date-picker v-model="startDate" no-title range>
          <v-spacer></v-spacer>

          <v-btn small dark color="background" @click="menu = false">
            Cancel
          </v-btn>
          <v-btn
            small
            dark
            color="background"
            @click="$refs.menu.save(dateRangeText)"
          >
            OK
          </v-btn>
        </v-date-picker>

        <v-date-picker
          @input="handleDateChange"
          ref="datePicker"
          v-model="startDate"
          no-title
          range
        >
          <v-spacer></v-spacer>

          <v-btn small dark color="background" @click="menu = false">
            Cancel
          </v-btn>
          <v-btn
            small
            dark
            color="background"
            @click="$refs.menu.save(dateRangeText)"
          >
            OK
          </v-btn>
        </v-date-picker>
      </v-menu>
    </v-col>
  </v-row>
</template>
<script>
const currentDate = new Date();
const nextMonthDate = new Date(currentDate);
nextMonthDate.setMonth(nextMonthDate.getMonth() + 1);

export default {
  data: () => ({
    startTime: "",
    endTime: "",
    initialValue: "2023-09-30",
    showStartTimePicker: false,
    showEndTimePicker: false,

    startDate: [],
    endDate: [],
    menu: false,
  }),
};
</script>
