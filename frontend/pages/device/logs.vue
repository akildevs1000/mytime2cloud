<template>
  <div class="device-employee-log">
    <h3>Device Employee Upload Logs</h3>

    <div class="controls">
      <label>
        Select date:
        <input type="date" v-model="date" @change="fetchLogs" />
      </label>

      <button @click="fetchLogs">Refresh</button>
    </div>

    <div class="log-box">
      <pre v-if="lines.length">
{{ lines.join('\n') }}
      </pre>
      <p v-else>{{ message || 'No logs found for this date.' }}</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "DeviceEmployeeLog",
  data() {
    return {
      date: null,
      lines: [],
      message: "",
    };
  },
  created() {
    // default: today
    const today = new Date().toISOString().slice(0, 10);
    this.date = today;
    this.fetchLogs();
  },
  methods: {
    async fetchLogs() {
      this.message = "";
      this.lines = [];

      try {
        const res = await axios.get("/api/SDK/device-employee-logs", {
          params: { date: this.date },
        });
        this.lines = res.data.lines || [];
        if (!this.lines.length) {
          this.message = "No logs found for this date.";
        }
      } catch (err) {
        console.error(err);
        if (err.response && err.response.data && err.response.data.message) {
          this.message = err.response.data.message;
        } else {
          this.message = "Failed to load logs.";
        }
      }
    },
  },
};
</script>

<style scoped>
.device-employee-log {
  max-width: 900px;
  margin: 0 auto;
}

.controls {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.log-box {
  max-height: 500px;
  overflow-y: auto;
  background: #111827;
  color: #e5e7eb;
  padding: 12px;
  border-radius: 8px;
  font-family: monospace;
  font-size: 12px;
  white-space: pre-wrap;
}
</style>
