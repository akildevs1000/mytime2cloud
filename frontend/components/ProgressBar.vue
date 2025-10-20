<template>
  <v-dialog
    v-model="loadingDialog"
    persistent
    max-width="400"
    style="box-shadow: none !important"
  >
    <div style="display: flex; justify-items: center; align-items: center">
      <div class="spinner-container">
        <!-- New Outer Spinning Bar Layer -->
        <div class="outer-spin-bars">
          <div class="bar bar-1"></div>
          <div class="bar bar-2"></div>
          <div class="bar bar-3"></div>
        </div>

        <!-- Progress Indicator Layer -->
        <div class="spinner-progress" :style="{ '--percent': progress }"></div>

        <!-- Spinning Outer Wheel Layer (Fast Spin) -->
        <div class="spinner-circle"></div>

        <!-- Inner Percentage Display -->
        <div class="spinner-inner-circle">{{ progress }}%</div>

        <div class="progress-loader">{{ analysisText }}</div>
      </div>
    </div>
  </v-dialog>
</template>

<script>
export default {
  props: ["queryStringUrl"],
  data() {
    return {
      analysisText: "Loading...",
      loadingDialog: false,
      total: 0,
      done: 0,
      failed: 0,
      progress: 0,
      intervalId: null,
      loader: false,
    };
  },
  watch: {
    loadingDialog(newVal) {
      if (newVal) {
        // Dialog opened → start polling
        this.startProgressPolling();
      } else {
        // Dialog closed → stop polling
        this.stopProgressPolling();
      }
    },
  },

  methods: {
    viewReport() {
      let report = document.createElement("a");
      report.setAttribute("href", this.queryStringUrl);
      report.setAttribute("target", "_blank");
      report.click();
    },
    reset() {
      this.total = 0;
      this.done = 0;
      this.failed = 0;
      this.progress = 0;
    },

    startProgressPolling() {
      this.reset();
      // Clear any existing interval just in case
      this.stopProgressPolling();

      // Immediately fetch once
      this.fetchProgress();

      // Then poll every 3 seconds
      this.intervalId = setInterval(this.fetchProgress, 3000);
    },

    stopProgressPolling() {
      if (this.intervalId) {
        clearInterval(this.intervalId);
        this.intervalId = null;
        this.loader = false;
      }
    },

    async fetchProgress() {
      this.loader = true;

      try {
        const res = await this.$axios.get("progress"); // your endpoint
        const { total, done, failed } = res.data;

        this.total = total;
        this.done = done;
        this.failed = failed;
        this.progress =
          total > 0 ? Math.round(((done + failed) / total) * 100) : 0;

        this.analysisText = this.progress < 100 ? "Preparing……" : "Ready!";

        // Stop polling if finished
        if (done + failed >= total && total > 0) {
          this.stopProgressPolling();

          setTimeout(() => {
            this.viewReport();
          }, 2000);

          setTimeout(() => {
            this.loadingDialog = false;
          }, 10000);
        }
      } catch (err) {
        console.error("Error fetching progress:", err.message);
      }
    },
  },
  mounted() {
    // this.intervalId = setInterval(this.fetchProgress, 3000); // poll every 1 second
  },
  beforeDestroy() {
    if (this.intervalId) clearInterval(this.intervalId);
  },
};
</script>
<style>
.progress-loader {
  position: absolute;
  bottom: 85px;
  color: rgba(0, 224, 255, 0.8);
  letter-spacing: 1px;
  animation: pulse 2s infinite alternate;
  width: fit-content;
  font-size: 1.2rem;
}

/* Define the primary colors for the electric glow effect */
:root {
  --color-accent: #00e0ff; /* Bright Electric Cyan/Blue */
  --color-light: #00e0ff;
  --color-dark-bg: #1a1a2e; /* Deep dark blue background */
}

.spinner-container {
  position: relative;
  width: 220px; /* Adjusted to fit the new outer bars */
  height: 500px; /* Adjusted to fit the new outer bars */
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background: none;
}

/* 0. NEW OUTER SPINNING BAR LAYER */
.outer-spin-bars {
  position: absolute;
  width: 220px; /* Slightly larger than main spinner */
  height: 220px;
  /* Slower, continuous spin */
  animation: spin-outer 10s linear infinite reverse;
}

/* Style for the individual bars */
.bar {
  position: absolute;
  top: 0; /* Positions the bar at the top edge of the 220px container */
  left: 50%;
  width: 25px;
  height: 5px;
  margin-left: -12.5px;
  border-radius: 2px;
  background-color: var(--color-accent);
  box-shadow: 0 0 10px var(--color-accent);
  /* Transform origin is half the container size (220/2 = 110) to rotate around the center */
  transform-origin: 50% 110px;
  opacity: 0.8;
}

/* Rotations for 120 degree separation */
.bar-1 {
  transform: rotate(0deg);
}
.bar-2 {
  transform: rotate(120deg);
}
.bar-3 {
  transform: rotate(240deg);
}

/* 1. PROGRESS LAYER (Bottom Layer, Static Fill) */
.spinner-progress {
  /* This property is controlled by JavaScript */
  --percent: 0;
  position: absolute;
  width: 180px;
  height: 180px;
  border-radius: 50%;

  /* Background conic gradient creates the progress fill */
  background: conic-gradient(
    var(--color-accent) calc(var(--percent) * 3.6deg),
    /* 3.6deg per 1% */ rgba(0, 224, 255, 0.15) 0%
  );

  /* Apply glow effect to the progress bar */
  box-shadow: 0 0 15px rgba(0, 224, 255, 0.5);
}

/* This pseudo-element creates the ring cutout by using the background color */
.spinner-progress::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 154px; /* 180px - (2 * 13px ring thickness) */
  height: 154px;
  border-radius: 50%;
  background-color: var(--color-dark-bg);
}

/* 2. SPINNING WHEEL LAYER (Top Layer, Dynamic Spin) */
.spinner-circle {
  position: absolute;
  width: 198px; /* Slightly larger than the progress layer */
  height: 198px;
  border-radius: 50%;
  /* Thin, high-contrast border for the spinning element */
  border: 2px solid transparent;
  border-top-color: var(--color-light);
  border-right-color: var(--color-light);
  animation: spin 0.7s linear infinite; /* Fast, continuous spin */
  opacity: 0.7;
}

/* 3. INNER PERCENTAGE CIRCLE */
.spinner-inner-circle {
  position: absolute;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(0, 224, 255, 0.3) 0%,
    rgba(0, 100, 150, 0.6) 100%
  );
  border: 2px solid rgba(255, 255, 255, 0.2);
  display: flex;
  justify-content: center;
  align-items: center;
  color: var(--color-light);
  font-size: 2.5em;
  font-weight: 800;
  box-shadow: 0 0 30px var(--color-accent),
    inset 0 0 10px rgba(0, 150, 200, 0.5);
  opacity: 0.95;
  z-index: 10;
}

/* Keyframes for the continuous spin on the top layer */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Keyframes for the slow outer spin */
@keyframes spin-outer {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Keyframes for text pulse */
@keyframes pulse {
  0% {
    opacity: 0.6;
  }
  100% {
    opacity: 1;
  }
}
.v-dialog.v-dialog--active.v-dialog--persistent {
  box-shadow: none !important;
}
</style>
