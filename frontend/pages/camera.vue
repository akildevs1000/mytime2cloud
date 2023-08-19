<template>
  <div>
    <h1>Camera Example</h1>
    <v-container>
      <v-btn @click="capturePhoto">Capture</v-btn>

      <v-row>
        <v-col cols="6">

          <video style="width: 300px;height: 300px;" id="camera" autoplay playsinline ref="camera"></video>
        </v-col>
        <v-col cols="6">
          <img style="width: 300px;height: 300px;" v-if="capturedImage" :src="capturedImage" />
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
    };
  },
  methods: {
    async startCamera() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'user' }, // Example: using rear camera
        });
        this.$refs.camera.srcObject = stream;
        this.errorMessage = null;
      } catch (error) {
        console.error('Error accessing camera:', error);
        this.errorMessage = 'Camera not found or access denied.';
      }
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
};
</script>
