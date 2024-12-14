<template>
  <v-card dense>
    <div class="pa-3">Calendar</div>
    <v-card-text>
      <style scoped>
        .day-container {
          border: 1px solid #eaeaea;
          padding: 5px 20px;
          gap:2px; margin:2px;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
        }
      </style>
      <div class="d-flex flex-wrap justify-start">
        <div
          v-for="day in daysInMonth"
          :key="day"
          class="day-container text-center"
        >
          <div>{{ formatDate(day) }}</div>
          <v-icon :color="getDotColor(day)" x-small>mdi-circle</v-icon>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      // Number of days in the month (e.g., 30 for September)
      daysInMonth: Array.from({ length: 30 }, (_, i) => i + 1),
      // Attendance data for each day (e.g., P = Present, A = Absent, O = Other)
      attendance: {
        1: "P",
        2: "A",
        3: "O",
        4: "P",
        5: "P",
        30: "O",
      },
    };
  },
  methods: {
    formatDate(day) {
      return day.toString().padStart(2, "0"); // Format day as 01, 02, etc.
    },
    getDotColor(day) {
      // Determine color based on attendance data
      const status = this.attendance[day];
      if (status === "P") return "green"; // Present
      if (status === "A") return "red"; // Absent
      return "grey"; // Other
    },
  },
};
</script>
