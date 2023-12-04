<template>
  <div
    style="
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    "
  >
    <video
      v-show="isCamera"
      height="250"
      ref="video"
      autoplay
      playsinline
    ></video>
    <canvas ref="canvas" style="display: none"></canvas>
    <img
      v-show="!isCamera"
      src="/no-profile-image.jpg"
      height="250"
      ref="img"
      alt=""
    />
    <v-btn class="primary mt-1" @click="takeSnapshot">Take Snapshot</v-btn>
  </div>
</template>

<script>
export default {
  auth: false,
  layout: "login",

  data: () => ({
    isCamera: false,
    videoStream: null,
  }),

  mounted() {
    this.setupCamera();
  },
  methods: {
    async setupCamera() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: true,
        });
        const video = this.$refs.video;
        video.srcObject = stream;
        this.videoStream = mediaStream;
        video.play();
      } catch (error) {
        console.error("Error accessing the camera: ", error);
      }
    },
    takeSnapshot() {
      this.isCamera = !this.isCamera;
      const video = this.$refs.video;
      const canvas = this.$refs.canvas;
      const img = this.$refs.img;
      const context = canvas.getContext("2d");

      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;

      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      const imageData = canvas.toDataURL("image/png");

      img.src = imageData;

      this.$emit("imageSrc", imageData);
    },
    beforeDestroy() {
      if (this.videoStream) {
        this.videoStream.getTracks().forEach((track) => track.stop());
      }
    },
  },
};
</script>
