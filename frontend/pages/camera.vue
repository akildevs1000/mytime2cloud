<template>
  <div>
    <h1>Camera Example</h1>
    <v-container>
      <v-btn @click="capturePhoto">Capture</v-btn>
      <v-btn @click="toggleCamera">Switch Camera</v-btn>

      <v-row>
        <v-col cols="6">
          <video style="width: 300px; height: 300px;" id="camera" autoplay playsinline ref="camera"></video>
        </v-col>
        <v-col cols="6">
          <img style="width: 300px; height: 300px;" v-if="capturedImage" :src="capturedImage" />
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
export default {
  data() {
    return {
      errorMessage: null,
      capturedImage: null,
      facingMode: 'environment',
      cameraStream: null, // Store the camera stream
    };
  },
  methods: {
    async startCamera() {
      try {
        this.cameraStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: this.facingMode },
        });
        this.$refs.camera.srcObject = this.cameraStream;
        this.errorMessage = null;
      } catch (error) {
        console.error('Error accessing camera:', error);
        this.errorMessage = 'Camera not found or access denied.';
      }
    },
    async toggleCamera() {
      if (this.cameraStream) {
        this.cameraStream.getTracks().forEach(track => track.stop()); // Stop the current camera stream
      }
      this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
      await this.startCamera(); // Start the camera with the new facing mode
    },
    capturePhoto() {
      const cameraElement = this.$refs.camera;
      const canvasElement = document.createElement('canvas');
      const canvasContext = canvasElement.getContext('2d');

      canvasElement.width = cameraElement.videoWidth;
      canvasElement.height = cameraElement.videoHeight;
      canvasContext.drawImage(
        cameraElement,
        0,
        0,
        canvasElement.width,
        canvasElement.height
      );

      this.capturedImage = canvasElement.toDataURL('image/png');
    },
  },
  mounted() {
    this.startCamera();
  },
  beforeDestroy() {
    if (this.cameraStream) {
      this.cameraStream.getTracks().forEach(track => track.stop()); // Make sure to stop the camera stream when the component is destroyed
    }
  },
};
</script>
