<template>
  <v-card elevation="0">
    <v-card-text class="mt-4">
      <div v-if="statusMessage">
        <v-badge dense :color="statusColor" offset-y="5" class="mr-5"></v-badge
        ><span>{{ statusMessage }}</span>
      </div>
      <div v-if="qrCodeDisplay == true">
        <v-img :src="qrCodePath" max-width="200" max-height="200"></v-img>
      </div>
   
      <div>
        <v-btn class="pa-2" x-small color="primary" @click="connect">
          <v-icon small class="mr-1">mdi-whatsapp</v-icon>
          {{ statusMessage !== "Online" ? "Connect" : "Check Online" }}
        </v-btn>
      </div>

      <v-container class="px-0">
                <h3 class="white">API Usage Example</h3>
                <p class="black white--text pa-2 my-3">
                  <strong>Endpoint:</strong>
                  <code> {{ endpoint }}</code>
                </p>
                <div class="black white--text pa-2">Request (POST)</div>
                <pre class="black white--text pa-2 mb-3">
  {
    "clientId": "{{ clientId }}",
    "recipient": "971xxxxxxxxx",
    "text": "test message"
  }
  </pre
                >
                <div class="black white--text pa-2">Response</div>
                <pre class="black white--text pa-2 mb-3">
  {
    "success": true,
    "data": "Message to 971554501483 is being processed."
  }
  </pre
                >
              </v-container>
    </v-card-text>
  </v-card>
</template>

<script>
import QRCode from "qrcode";

export default {
  data() {
    return {
      clientId:"default_client_secret",
      ws: null, // WebSocket instance
      statusMessage: null, // Message for status updates
      qrCodePath: null, // QR code image path
      qrCodeDisplay: false,
      connectButton: false,
      statusColor: null,
      endpoint: "wa.mytime2cloud.com/api/send-message",
    };
  },
  async mounted() {
    // await this.connect();
  },
  methods: {
    async connect() {
      await this.connectWebSocket(this.clientId);
    },

    async connectWebSocket(clientId) {
      const wsUrl = `wss://wa.mytime2cloud.com/ws/?clientId=${clientId}`;
      this.ws = new WebSocket(wsUrl);

      this.ws.onopen = () => {
        this.statusMessage = `Connected to the WebSocket server with clientId: ${clientId}`;
      };

      this.ws.onmessage = async (event) => {
        this.qrCodePath = null;
        this.qrCodeDisplay = false;

        this.statusMessage = null;
        this.connectButton = false;
        this.statusColor = "";

        const data = JSON.parse(event.data);
        console.log("🚀 ~ this.ws.onmessage= ~ data:", data);

        if (data.event === "status") {
          this.statusMessage = data.data;
        } else if (data.event === "ready") {
          this.statusMessage = data.data;
          this.statusColor = "success";
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
            this.qrCodeDisplay = true;
          } catch (error) {
            this.statusMessage = `Error generating QR code: ${error.message}`;
          }
        } else if (data.event === "destroy") {
          this.statusMessage = data.data;
          this.connectButton = true;
          this.statusColor = "error";
        }
      };
    },
  },
};
</script>
