<template>
  <v-card elevation="5">
    <div class="pa-3">Statistics</div>
    <v-divider></v-divider>
    <v-card-text
      class="custom-scrollbar"
      style="max-height: 355px; min-height: 355px; overflow-y: hidden"
    >
      <v-row v-for="(stat, index) in stats" :key="index">
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center">
            <span>{{ stat.label }}</span>
            <span>{{ stat.value }}</span>
          </div>
          <v-progress-linear
            :value="stat.percentage"
            :color="stat.color"
            height="15"
            rounded
          ></v-progress-linear>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      stats: [
        {
          label: "This Month (Total Hrs)",
          value: "3.45 / 8 hrs",
          percentage: (3.45 / 8) * 100,
          color: "orange",
        },
        {
          label: "This Month (Present)",
          value: "10 / 30 Days",
          percentage: (90 / 160) * 100,
          color: "green",
        },
        {
          label: "This Month (Absent)",
          value: "5 / 30 Days",
          percentage: (90 / 160) * 100,
          color: "red",
        },
        {
          label: "This Month (Week Off)",
          value: "3 / 30 Days",
          percentage: (90 / 160) * 100,
          color: "grey",
        },
        {
          label: "Overtime",
          value: "4 hrs",
          percentage: 50, // Arbitrary percentage for visual
          color: "blue",
        },
      ],
    };
  },
};
</script>
