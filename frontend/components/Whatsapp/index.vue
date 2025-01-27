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

      <!-- <div class="mt-5">
        <v-btn
          x-small
          v-if="disconnectButton"
          color="error"
          @click="disconnect"
        >
          <v-icon small class="mr-1">mdi-whatsapp</v-icon> Disconnect
        </v-btn>

        <v-btn x-small v-if="connectButton" color="primary" @click="connect">
          <v-icon small class="mr-1">mdi-whatsapp</v-icon> Connect
        </v-btn>
      </div> -->
    </v-card-text>
  </v-card>
</template>

<script>
import QRCode from "qrcode";

export default {
  data() {
    return {
      clientId: null, // Input field model for client ID
      ws: null, // WebSocket instance
      statusMessage: null, // Message for status updates
      qrCodePath: null, // QR code image path
      qrCodeDisplay: false,
      disconnectButton: false,
      connectButton: false,
      statusColor: null,
    };
  },
  async mounted() {
    await this.connect();
  },
  methods: {
    async connect() {
      await this.connectWebSocket(
        this.$auth?.user?.company?.company_code || "_1"
      );
    },
    async disconnect() {
      this.statusMessage = null;
      this.disconnectButton = false;
      this.connectButton = false;
      this.statusColor = "error";

      let payload = {
        clientId: this.$auth?.user?.company?.company_code || "_1",
      };

      try {
        let { data } = await this.$axios.post(
          `https://wa.mytime2cloud.com/whatsapp-destroy`,
          payload
        );
        this.statusMessage = data.data;
        this.connectButton = true;
      } catch (error) {
        this.statusMessage = error.message;
        this.disconnectButton = true;
      }
    },

    async connectWebSocket(clientId) {
      this.clientId = clientId;
      const wsUrl = `wss://wa.mytime2cloud.com/ws/?clientId=${this.clientId}`;
      this.ws = new WebSocket(wsUrl);

      this.ws.onopen = () => {
        this.statusMessage = `Connected to the WebSocket server with clientId: ${this.clientId}`;
      };

      this.ws.onmessage = async (event) => {
        this.qrCodePath = null;
        this.qrCodeDisplay = false;

        this.statusMessage = null;
        this.disconnectButton = false;
        this.connectButton = false;
        this.statusColor = "";

        const data = JSON.parse(event.data);
        console.log("🚀 ~ this.ws.onmessage= ~ data:", data);

        if (event.data == 1) {
          //   this.connect();
        }

        if (data.event === "status") {
          this.statusMessage = data.data;
        } else if (data.event === "ready") {
          this.statusMessage = data.data;
          this.disconnectButton = true;
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
