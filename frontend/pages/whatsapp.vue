<template>
  <v-container>
    <style scoped>
      @keyframes rotate {
        0% {
          transform: rotate(360deg);
        }
        100% {
          transform: rotate(0deg);
        }
      }

      .rotate {
        animation: rotate 2s linear infinite;
      }
    </style>
    <v-row class="mb-3">
      <!----- <v-col cols="12" v-if="qrImage">
          <v-img :src="qrImage"></v-img>
        </v-col>
        ----->
      <v-col cols="12">
        <div class="headline">Whatsapp Proxy</div>
      </v-col>
      <!-- <v-col cols="4">
          <v-text-field
            label="Server URL"
            v-model="serverUrl"
            outlined
            dense
            hide-details
          />
        </v-col>
        <v-col>
          <v-btn v-if="isConnected" color="red" dark @click="disconnect"
            >Disonnect</v-btn
          >
          <v-btn
            v-else
            :loading="loading"
            color="primary"
            @click="connectToWebSocket"
            >Connect</v-btn
          >
        </v-col> -->

      <v-col cols="3">
        <v-row>
          <v-col cols="12" v-if="qrCodeUrl">
            <v-avatar size="200" tile>
              <v-img :src="qrCodeUrl" />
            </v-avatar>
          </v-col>
          <v-col cols="12" v-if="isRegenerate">
            <v-btn
              @click="regenerateClientId"
              :loading="regenerateLoading"
              small
              color="primary"
              >Regenerate</v-btn
            >
          </v-col>

          <v-col v-if="!qrCodeUrl" cols="12">
            <div class="white ma-2" dense>
              <v-badge
                x-small
                dense
                hide-details
                :color="statusColor"
                class="mr-5"
              ></v-badge>
              <span>{{ statusMessage }}</span>

              <!-- i want to rorate this v-con  -->
              <v-icon v-if="loading" class="rotate" color="primary"
                >mdi-sync</v-icon
              >
            </div>
          </v-col>
        </v-row>
      </v-col>
      <v-col>
        <v-card>
          <v-container class="px-5">
            <h3 class="white">API Usage Example</h3>
            <p class="black white--text pa-2 my-3">
              <strong>Endpoint:</strong>
              <code> {{ endpoint }}</code>
            </p>
            <div class="black white--text pa-2">Request (POST)</div>
            <pre class="black white--text pa-2 mb-3">
  {
    "clientId": "{{ clientId }}",
    "phone": "971xxxxxxxxx",
    "message": "test message"
  }
  </pre
            >
            <div class="black white--text pa-2">Response</div>
            <pre class="black white--text pa-2 mb-3">
  {
    "success": true,
    "message": "Message sent successfully!"
  }
  </pre
            >
          </v-container>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  auth: false,
  layout: "master",
  data() {
    return {
      regenerateLoading: false,
      loading: true,
      ws: null, // WebSocket instance
      clientId: "",
      serverUrl: "wss://wa.mytime2cloud.com/ws/",
      endpoint: "wa.mytime2cloud.com/api/send-message",

      //   serverUrl: "ws://localhost:5175",
      //   endpoint: "http://localhost:5176/api/send-message",
      qrCodeUrl: null,
      statusMessage: "",
      statusColor: "",
      loading: false,
      isConnected: false,

      qrImage: null,
      isRegenerate: false,
    };
  },
  async mounted() {
    // // Initialize clientId to an empty string
    this.clientId = "";
    // Check if a clientId exists in localStorage
    let clientId = localStorage.getItem("clientId");
    clientId = `client_${Math.random().toString(36).substr(2, 9)}`;
    localStorage.setItem("clientId", clientId);
    this.connectToWebSocket(clientId);
  },
  methods: {
    regenerateClientId() {
      this.clientId = "";
      this.regenerateLoading = true;
      let clientId = `client_${Math.random().toString(36).substr(2, 9)}`;
      localStorage.setItem("clientId", clientId);
      setTimeout(() => {
        this.connectToWebSocket(clientId);
        this.regenerateLoading = false;
      }, 3000);
    },
    disconnect() {
      if (this.ws && this.ws.readyState === WebSocket.OPEN) {
        // Notify the server to disconnect the WhatsApp client
        this.ws.send(
          JSON.stringify({
            type: "disconnect",
            clientId: localStorage.getItem("clientId"), // Pass the unique clientId
          })
        );

        // Update the UI to reflect the disconnection
        this.statusMessage = "Disconnected from WhatsApp.";
        this.statusColor = "warning";
        this.qrCodeUrl = null;

        localStorage.removeItem("clientId");

        console.log("WhatsApp client disconnected.");
      } else {
        console.warn("WebSocket is not open or already disconnected.");
        this.statusMessage =
          "No active connection to disconnect from WhatsApp.";
        this.statusColor = "error";
      }
    },

    connectToWebSocket(clientId) {
      if (!this.serverUrl.trim()) {
        alert("Please enter a valid WebSocket server URL.");
        return;
      }

      this.ws = new WebSocket(`${this.serverUrl}?clientId=${clientId}`);
      this.loading = true;

      this.ws.onopen = () => {
        console.log("WebSocket connection established.");
        this.ws.send(JSON.stringify({ type: "clientId", clientId }));
      };

      this.ws.onmessage = (event) => {
        const data = JSON.parse(event.data);

        if (data.qr == undefined) {
          this.qrCodeUrl = null;
          this.statusMessage = ``;
          this.loading = false;
          this.statusColor = "";
          this.isRegenerate = true;
        } else if (data.type === "qr" || data.type === "clientId") {
          this.qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(
            data.qr
          )}`;
          this.statusMessage = "";
          this.loading = false;
          this.statusColor = "";
          this.isRegenerate = false;
        } else if (data.type === "status" && data.ready) {
          this.qrCodeUrl = null;
          this.statusMessage = data.message;
          this.loading = true;
          this.statusColor = "success";
          this.isConnected = false;
          this.clientId = clientId;
        } else if (data.type === "error") {
          this.qrCodeUrl = null;
          this.statusMessage = `Error: ${data.message}`;
          this.loading = false;
          this.statusColor = "error";
        }
      };

      // this.ws.onclose = () => {
      //   console.log("WebSocket connection closed.");
      //   this.statusMessage = "Connection lost. Please refresh the page.";
      //   this.statusColor = "error";
      //   this.loading = false;
      // };
    },
    reloadPage() {
      location.reload();
    },
  },
};
</script>
