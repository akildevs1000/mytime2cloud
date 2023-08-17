<template>
    <div>
      <video ref="videoElement" autoplay></video>
      <button @click="toggleCamera">{{ cameraActive ? 'Turn Off Camera' : 'Turn On Camera' }}</button>
    </div>
  </template>

<script>
export default {
  data() {
    return {
      cameraActive: false,
    };
  },
  methods: {
    async toggleCamera() {
      const videoElement = this.$refs.videoElement;

      if (!this.cameraActive) {
        try {
          const stream = await navigator.mediaDevices.getUserMedia({ video: true });
          videoElement.srcObject = stream;
          this.cameraActive = true;
        } catch (error) {
          console.error('Error accessing camera:', error);
        }
      } else {
        const stream = videoElement.srcObject;
        if (stream) {
          const tracks = stream.getTracks();
          tracks.forEach(track => track.stop());
          videoElement.srcObject = null;
        }
        this.cameraActive = false;
      }
    },
  },
  beforeDestroy() {
    const videoElement = this.$refs.videoElement;
    const stream = videoElement.srcObject;
    if (stream) {
      const tracks = stream.getTracks();
      tracks.forEach(track => track.stop());
    }
  },
};
</script>
