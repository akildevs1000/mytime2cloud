<template>
  <v-container>
    <style scoped>
      .custom-slider {
        position: relative;
        height: 24px;
        margin: 40px auto;
        width: 100%;
        max-width: 600px;
      }

      .segment {
        position: absolute;
        top: 50%;
        height: 6px;
        transform: translateY(-50%);
        border-radius: 3px;
      }

      .segment.before {
        background-color: #e0e0e0;
        left: 0;
      }

      .segment.range {
        background-color: #8e44ff;
        z-index: 1;
      }

      .segment.after {
        background-color: #3498db;
        right: 0;
      }

      .handle {
        position: absolute;
        top: 50%;
        width: 24px;
        height: 24px;
        border: 5px solid #8e44ff;
        background-color: white;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        z-index: 2;
      }
    </style>

    <div class="time-labels text-center mb-2">
      <strong>{{ formatHour(startHour) }} - {{ formatHour(endHour) }}</strong>
    </div>

    <div class="custom-slider" ref="slider">
      <!-- Segments -->
      <div
        class="segment before"
        :style="{ width: (startHour / 24) * 100 + '%' }"
      ></div>
      <div
        class="segment range"
        :style="{
          left: (startHour / 24) * 100 + '%',
          width: ((endHour - startHour) / 24) * 100 + '%',
        }"
      ></div>
      <div
        class="segment after"
        :style="{
          left: (endHour / 24) * 100 + '%',
          width: ((24 - endHour) / 24) * 100 + '%',
        }"
      ></div>

      <!-- Handles -->
      <div
        class="handle"
        :style="{ left: (startHour / 24) * 100 + '%' }"
        @mousedown="startDrag('start')"
      ></div>
      <div
        class="handle"
        :style="{ left: (endHour / 24) * 100 + '%' }"
        @mousedown="startDrag('end')"
      ></div>
    </div>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      startHour: 9, // 09:00
      endHour: 18, // 18:00
      dragging: null,
    };
  },
  methods: {
    startDrag(handle) {
      this.dragging = handle;
      window.addEventListener("mousemove", this.onDrag);
      window.addEventListener("mouseup", this.stopDrag);
    },
    stopDrag() {
      this.dragging = null;
      window.removeEventListener("mousemove", this.onDrag);
      window.removeEventListener("mouseup", this.stopDrag);
    },
    onDrag(e) {
      const rect = this.$refs.slider.getBoundingClientRect();
      let percent = ((e.clientX - rect.left) / rect.width) * 100;
      let hour = (percent / 100) * 24;
      hour = Math.round(hour * 2) / 2; // Snap to 0.5 hour

      hour = Math.max(0, Math.min(24, hour));

      if (this.dragging === "start") {
        this.startHour = Math.min(hour, this.endHour);
      } else if (this.dragging === "end") {
        this.endHour = Math.max(hour, this.startHour);
      }
    },
    formatHour(hour) {
      const h = Math.floor(hour);
      const m = hour % 1 === 0.5 ? "30" : "00";
      return `${String(h).padStart(2, "0")}:${m}`;
    },
  },
};
</script>
