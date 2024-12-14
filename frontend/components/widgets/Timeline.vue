<template>
  <v-card elevation="5">
    <style scoped>
      .custom-scrollbar {
        scrollbar-width: thin; /* For Firefox */
        scrollbar-color: #7f7f7f #f5f5f5; /* Track and thumb colors for Firefox */
      }

      .custom-scrollbar::-webkit-scrollbar {
        width: 8px; /* Width of the scrollbar */
      }

      .custom-scrollbar::-webkit-scrollbar-track {
        background: #f5f5f5; /* Background of the scrollbar track */
        border-radius: 4px; /* Rounded corners */
      }

      .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #7f7f7f; /* Scrollbar thumb color */
        border-radius: 4px; /* Rounded corners for thumb */
        border: 2px solid #f5f5f5; /* Adds spacing between thumb and track */
      }
    </style>
    <div class="pa-3">Today Activity</div>
    <v-divider></v-divider>
    <v-card-text class="custom-scrollbar" style="max-height: 355px;    min-height: 355px; overflow-y: auto">
      <v-timeline dense class="pt-1">
        <v-timeline-item
          small
          v-for="(entry, index) in punchEntries"
          :key="index"
          :icon="`mdi-clock-outline`"
          fill-dot
        >
          <h4 class="ml-1">{{ entry.type }}</h4>
          <div>
            <v-icon small class="mr-1">mdi-clock-outline</v-icon
            ><small>{{ entry.time }}</small>
          </div>
        </v-timeline-item>
      </v-timeline>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      punchEntries: [
        { type: "Punch In", time: "09:00 AM" },
        { type: "Punch Out", time: "12:30 PM" },
        { type: "Punch In", time: "01:30 PM" },
        { type: "Punch Out", time: "05:00 PM" },
        { type: "Punch In", time: "06:00 PM" },
        { type: "Punch Out", time: "09:00 PM" },
      ],
    };
  },
};
</script>
