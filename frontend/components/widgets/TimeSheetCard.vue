<template>
  <v-card elevation="5">
    <div class="pa-3">Timesheet</div>

    <v-divider></v-divider>
    <!-- Punch In Details -->
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
    <v-card-text
      class="custom-scrollbar"
      style="max-height: 355px; min-height: 355px; overflow-y: hidden"
    >
      <!-- Timer Circle -->
      <div class="text-center pa-2">
        <v-avatar
          size="100"
          class="mx-auto body-2 pa-2"
          style="border: 10px solid orange"
        >
          3.45 hrs
        </v-avatar>

        <div class="text-center mt-1">
          <span class="subtitle-2">Total Hours</span>
        </div>
      </div>

      <div class="white pt-2">
        <div class="body-2 pa-2 d-flex justify-space-between">
          <div>Present</div>
          <div>05</div>
        </div>
        <v-divider />
        <div class="body-2 pa-2 d-flex justify-space-between">
          <div>Absent</div>
          <div>05</div>
        </div>
        <v-divider />
        <div class="body-2 pa-2 d-flex justify-space-between">
          <div>Leaves</div>
          <div>05</div>
        </div>
        <v-divider />
        <div class="body-2 pa-2 d-flex justify-space-between">
          <div>Week Off</div>
          <div>05</div>
        </div>
        <v-divider />
        <div class="body-2 px-2 pt-2 pb-0 d-flex justify-space-between">
          <div>Last Punch At</div>
          <div>30 Aug 2024, 10:46</div>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      // You can manage these dynamically if needed
    };
  },
};
</script>
