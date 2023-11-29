<template>
  <div>
    <div class="text-center">
      <v-avatar size="200" style="border: 1px solid #6946dd">
        <v-img v-show="isImageBox" :src="imageSrc" />
        <video
          height="100%"
          v-show="!isImageBox"
          ref="video"
          autoplay
          playsinline
        ></video>
      </v-avatar>
    </div>
    <div class="text-center mt-1">
      <v-btn
        width="200"
        v-if="isImageBox"
        @click="openCamera"
        small
        class="primary"
        >Open Camera</v-btn
      >
      <v-btn width="200" v-else @click="takePicture" small class="primary"
        >Take Picture</v-btn
      >
    </div>
  </div>
</template>

<script>
export default {
  auth: false,
  layout: "login",
  data() {
    return {
      debug: false,
      isImageBox: true,
      videoStream: null,
      imageSrc: "https://mytime2cloud.com/no-profile-image.jpg",
    };
  },
  methods: {
    async openCamera() {
      this.isImageBox = false;

      const video = this.$refs.video;

      if (this.debug) {
        video.src = "/your_video.mp4";
        return;
      }

      const mediaStream = await navigator.mediaDevices.getUserMedia({
        video: true,
      });
      video.srcObject = mediaStream;
      this.videoStream = mediaStream;
    },
    async takePicture() {
      this.isImageBox = true;

      // Create a canvas to capture the video frame
      const canvas = document.createElement("canvas");

      const video = this.$refs.video;

      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas
        .getContext("2d")
        .drawImage(video, 0, 0, canvas.width, canvas.height);
      this.imageSrc = canvas.toDataURL("image/jpeg");
      this.$emit("imageSrc", this.imageSrc);
    },
  },
  beforeDestroy() {
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop());
    }
  },
};
</script>
