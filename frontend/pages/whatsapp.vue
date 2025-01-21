<template>
  <v-container>
    <v-card>
      <v-card-title>WebSocket QR Code Generator</v-card-title>
      <v-card-text>
        <v-text-field
          v-model="clientId"
          label="Enter Client ID"
          outlined
          dense
        ></v-text-field>
        <v-btn
          :disabled="!clientId"
          color="primary"
          @click="connectWebSocket"
        >
          Connect
        </v-btn>
        <div v-if="statusMessage" class="mt-4">
          <p>Status: {{ statusMessage }}</p>
        </div>
        <div v-if="qrCodePath" class="mt-4">
          <p>QR Code:</p>
          <v-img
            :src="qrCodePath"
            max-width="200"
            max-height="200"
          ></v-img>
        </div>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import QRCode from "qrcode";

export default {
  data() {
    return {
      clientId: "", // Input field model for client ID
      ws: null, // WebSocket instance
      statusMessage: "", // Message for status updates
      qrCodePath: "", // QR code image path
    };
  },
  methods: {
    async connectWebSocket() {
      if (!this.clientId) {
        this.statusMessage = "Client ID is required!";
        return;
      }

      const wsUrl = `wss://wa.mytime2cloud.com/ws/?clientId=${this.clientId}`;
      this.ws = new WebSocket(wsUrl);

      this.ws.onopen = () => {
        this.statusMessage = `Connected to the WebSocket server with clientId: ${this.clientId}`;
      };

      this.ws.onmessage = async (event) => {
        const data = JSON.parse(event.data);

        if (data.event === "status") {
          this.statusMessage = `Status: ${data.data}`;
        } else if (data.event === "ready") {
          this.statusMessage = `Ready: ${data.data}`;
        } else if (data.event === "qr") {
          const qrCodeData = data.data;

          try {
            // Generate a QR code as a data URL
            const qrCodePath = await QRCode.toDataURL(qrCodeData, {
              color: {
                dark: "#000000", // Black QR code
                light: "#ffffff", // White background
              },
            });

            // Update the path to display the QR code
            this.qrCodePath = qrCodePath;
          } catch (error) {
            this.statusMessage = `Error generating QR code: ${error.message}`;
          }
        }
      };

      this.ws.onerror = (error) => {
        this.statusMessage = `WebSocket error: ${error.message}`;
      };

      this.ws.onclose = () => {
        this.statusMessage = "WebSocket connection closed.";
      };
    },
  },
};
</script>