<template>
  <v-dialog v-model="loadingDialog" persistent max-width="400">
    <WidgetsClose
      left="390"
      @click="
        () => {
          loadingDialog = false;
          reset();
        }
      "
    />
    <style>
      /* --- Text and Percentage --- */
      .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: 600;
        color: #333333;
      }

      .percentage {
        font-weight: 500;
        font-size: 16px;
      }
    </style>

    <v-card rounded="lg">
      <v-container>
        <div class="header">
          <span>{{ processing.text1 }}</span>
          <span class="percentage">{{ progress }}%</span>
        </div>

        <v-progress-linear
          class="mt-5 mb-2"
          height="8"
          color="#007aff"
          rounded="lg"
          :value="progress"
        ></v-progress-linear>

        <div
          style="
            color: #007aff;
            font-size: 14px;
            font-weight: 400;
            text-align: left;
            margin-bottom: 40px;
          "
        >
          {{ processing.text2 }}
        </div>

        <v-btn block :loading="loader" color="#007aff" dark @click="viewReport"
          >View Report</v-btn
        >
      </v-container>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ["queryStringUrl"],
  data() {
    return {
      loadingDialog: false,
      total: 0,
      done: 0,
      failed: 0,
      progress: 0,
      intervalId: null,
      loader: false,
      processing: {
        text1: "Processing your request",
        text2: "This may take a few moments...",
      },
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

      this.processing = {
        text1: "Processing your request",
        text2: "This may take a few moments...",
      };
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

        this.processing = {
          text1: "Request Finished",
          text2: "Report request has been finish",
        };
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

        // Stop polling if finished
        if (done + failed >= total && total > 0) {
          this.stopProgressPolling();
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
