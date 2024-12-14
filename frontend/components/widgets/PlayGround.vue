<template>
  <v-card
    class="pa-4 paste-area"
    ref="pasteArea"
    @paste="handlePaste"
    contenteditable="true"
  >
    <style scoped>
      .paste-area {
        border: 2px dashed #ccc;
        padding: 10px;
        text-align: center;
        height: 200px;
        overflow: auto;
      }
      .resize-canvas {
        border: 1px solid #000;
        max-width: 100%;
      }
    </style>
    <h2>Image Capture, Paste, and Resize</h2>
    <v-row>
      <v-col cols="12" md="6">
        <div>Paste image here</div>
      </v-col>
      <v-col cols="12" md="6">
        <canvas ref="canvas" class="resize-canvas"></canvas>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-slider
          v-model="resizeRatio"
          min="0.1"
          max="2"
          step="0.1"
          label="Resize Ratio"
          @change="resizeImage"
        ></v-slider>
      </v-col>
    </v-row>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      resizeRatio: 1, // Default resize ratio
      image: null, // Stores the pasted image
      canvas: null, // Reference to the canvas
      context: null, // Canvas context
    };
  },
  mounted() {
    this.canvas = this.$refs.canvas;
    this.context = this.canvas.getContext("2d");
  },
  methods: {
    handlePaste(event) {
      const clipboardItems = event.clipboardData.items;
      for (let item of clipboardItems) {
        if (item.type.includes("image")) {
          const file = item.getAsFile();
          const img = new Image();
          img.onload = () => {
            this.image = img;
            this.resizeImage();
          };
          img.src = URL.createObjectURL(file);
          break;
        }
      }
    },
    resizeImage() {
      if (!this.image) return;

      const width = this.image.width * this.resizeRatio;
      const height = this.image.height * this.resizeRatio;

      // Adjust canvas size
      this.canvas.width = width;
      this.canvas.height = height;

      // Draw resized image on canvas
      this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
      this.context.drawImage(this.image, 0, 0, width, height);
    },
  },
};
</script>
