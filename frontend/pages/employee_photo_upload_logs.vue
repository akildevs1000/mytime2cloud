<template>
  <v-card>
    <v-toolbar class="rounded-md" dense flat>
      <v-toolbar-title>
        <span>Device Employee Upload Logs</span>
      </v-toolbar-title>

      <!-- Reload icon button with loading -->
      <v-btn dense class="ma-0 px-0" x-small :ripple="false" text title="Reload" :loading="loading" :disabled="loading"
        @click="fetchLogs">
        <v-icon class="ml-2" dark>mdi-reload</v-icon>
      </v-btn>

      <v-spacer></v-spacer>

      <!-- DATE PICKER (menu + text field + v-date-picker) -->
      <v-menu v-model="dateMenu" :close-on-content-click="false" transition="scale-transition" offset-y
        min-width="auto">
        <template v-slot:activator="{ on, attrs }">
          <v-text-field v-model="date" label="  Date" readonly outlined dense hide-details class="mr-2"
            style="max-width: 200px" v-bind="attrs" v-on="on"></v-text-field>
        </template>

        <v-date-picker v-model="date" @input="onDateSelected" scrollable></v-date-picker>
      </v-menu>

      <!-- Manual refresh button with loading -->
      <!-- <v-btn small :ripple="false" title="Submit" :loading="loading" color="primary" :disabled="loading"
        @click="fetchLogs">
        Submit
      </v-btn> -->
    </v-toolbar>

    <v-card-text>
      <!-- DATA TABLE -->
      <v-data-table :headers="headers" dense :items="items" :search="search" class="mt-5" style="min-height: 800px"
        :loading="loading" loading-text="Loading logs...">
        <!-- Row number -->
        <template v-slot:item.sno="{ index }">
          <span>{{ index + 1 }}</span>
        </template>

        <!-- Timestamp -->
        <template v-slot:item.timestamp="{ item }">
          <span>{{ item.timestamp }}</span>
        </template>

        <!-- Status as colored chip -->
        <template v-slot:item.gateway_result="{ item }">
          <v-chip small :color="item.gateway_result === 'Success' ? 'green' : 'red'" dark>
            {{ item.gateway_result }}
          </v-chip>
        </template>
      </v-data-table>

      <div v-if="!loading && items.length === 0" class="mt-5 red--text">
        {{ message }}
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: "DeviceEmployeeLogTable",

  data() {
    return {
      date: "",
      dateMenu: false,
      lines: [],
      items: [],
      search: "",
      message: "",
      loading: false,

      headers: [
        { text: "#", value: "sno" },
        { text: "Date", value: "timestamp", width: 180 },
        { text: "Name", value: "name" },
        { text: "User Code", value: "userCode" },
        { text: "Device SN", value: "device_id" },
        { text: "Device", value: "device_name" },


        { text: "Action", value: "action" },
        { text: "Status", value: "gateway_result" },
      ],
    };
  },

  created() {
    this.date = new Date().toISOString().slice(0, 10);
    this.fetchLogs();
  },

  methods: {
    onDateSelected() {
      this.dateMenu = false;
      this.fetchLogs();
    },

    async fetchLogs() {
      this.items = [];
      this.message = "";
      this.loading = true;

      try {
        const res = await this.$axios.get("/SDK/device-employee-logs", {
          params: {
            date: this.date, company_id: this.$auth.user.companyId, // optional if backend uses auth()
          },
        });

        const lines = res.data.lines || [];
        const allItems = [];

        lines.forEach((rawLine) => {
          const line = (rawLine || "").trim();
          if (!line) return;

          // Timestamp: [2025-11-26 12:26:15]
          const timestampMatch = line.match(/^\[(.*?)\]/);
          const timestamp =
            timestampMatch && timestampMatch.length > 1
              ? timestampMatch[1]
              : "";

          // Find JSON object
          const jsonStart = line.indexOf("{");
          if (jsonStart === -1) {
            console.warn("No JSON found in log line:", line);
            return;
          }

          const jsonStr = line.substring(jsonStart).trim();

          try {
            const obj = JSON.parse(jsonStr);

            obj.timestamp = timestamp;

            // Translate gateway_success -> readable word
            obj.gateway_result =
              obj.gateway_success && Number(obj.gateway_success) === 1
                ? "Success"
                : "Failed";

            allItems.push(obj);
          } catch (e) {
            console.error("JSON Parse Error:", e, jsonStr);
          }
        });

        this.items = allItems;

        if (!this.items.length) {
          this.message = "No logs found for this date.";
        }
      } catch (err) {
        console.error(err);
        this.message = "No logs found or error loading logs.";
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped></style>
