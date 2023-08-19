<template>
  <div>
    <h1>Camera Example</h1>
    <v-alert v-if="errorMessage" class="red" dark>{{ errorMessage }}</v-alert>
    <v-container v-if="!errorMessage && !loading">
      <video id="camera" autoplay playsinline ref="camera"></video>
      <v-button @click="capturePhoto">Capture</v-button>
      <canvas id="canvas" style="display: none"></canvas>
    </v-container>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: true,
      errorMessage: null,
    };
  },
  methods: {
    async startCamera() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: "environment" }, // Example: using rear camera
        });
        this.$refs.camera.srcObject = stream;
        this.errorMessage = null;
      } catch (error) {
        console.error("Error accessing camera:", error);
        this.errorMessage = "Camera not found or access denied.";
      }
    },
    capturePhoto() {
      const cameraElement = this.$refs.camera;
      const canvasElement = document.getElementById("canvas");
      const canvasContext = canvasElement.getContext("2d");

      canvasElement.width = cameraElement.videoWidth;
      canvasElement.height = cameraElement.videoHeight;
      canvasContext.drawImage(
        cameraElement,
        0,
        0,
        canvasElement.width,
        canvasElement.height
      );

      const imageURL = canvasElement.toDataURL("image/png");
      const preview = document.createElement("img");
      preview.src = imageURL;
      document.body.appendChild(preview);
    },
  },
  mounted() {
    this.startCamera();
  },
};
</script>

<style>
/* Add your custom styles here */
</style>
