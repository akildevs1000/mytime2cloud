<template>
  <div>
    <h1>Take Photo</h1>
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-alert
            id="errorMessage"
            :value="error"
            type="error"
            dismissible
            transition="scale-transition"
          >
            Camera not found or access denied.
          </v-alert>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-text>
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <v-video id="camera" autoplay></v-video>
                  </v-col>
                  <v-col cols="12" class="text-center">
                    <v-btn @click="capturePhoto" color="primary">Capture</v-btn>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
export default {
  data() {
    return {
      error: false,
      cameraStream: null,
    };
  },
  mounted() {
    this.startCamera();
  },
  methods: {
    async startCamera() {
      try {
        this.cameraStream = await navigator.mediaDevices.getUserMedia({
          video: true,
        });
        const videoElement = this.$refs.camera;
        videoElement.srcObject = this.cameraStream;
        this.error = false;
      } catch (error) {
        console.error("Error accessing camera:", error);
        this.error = true;
      }
    },
    capturePhoto() {
      const videoElement = this.$refs.camera;
      const canvasElement = this.$refs.canvas;
      canvasElement.width = videoElement.videoWidth;
      canvasElement.height = videoElement.videoHeight;
      canvasElement
        .getContext("2d")
        .drawImage(
          videoElement,
          0,
          0,
          canvasElement.width,
          canvasElement.height
        );
      const imageURL = canvasElement.toDataURL("image/png");
      const preview = new Image();
      preview.src = imageURL;
      document.body.appendChild(preview);
    },
  },
};
</script>
