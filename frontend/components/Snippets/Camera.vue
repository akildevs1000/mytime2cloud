<template>
  <div>
    <v-btn @click="open_camera = !open_camera" small style="width: 100%;" class="background my-1" dark>
      Take Photo <v-icon>mdi-camera-outline</v-icon>
    </v-btn>
    <video v-show="open_camera" style="border: 1px solid; width: 100%;" id="camera" autoplay playsinline
      ref="camera"></video>
    <v-btn v-show="open_camera" small style="width: 100%;" class="background" dark @click="capturePhoto">Capture</v-btn>

  </div>
</template>

<script>
export default {
  data() {
    return {
      open_camera: false,
      errorMessage: null,
      capturedImage: null,
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
      const canvasElement = document.createElement("canvas");
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

      this.capturedImage = canvasElement.toDataURL("image/png");
      this.$emit("captured-image", this.capturedImage);

    },
  },
  mounted() {
    this.startCamera();
  },
};
</script>
