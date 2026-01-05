<template>
  <div @click="submit" style="cursor: pointer;">
    <v-icon color="blue" small> mdi-download </v-icon>
    Download
  </div>
</template>
<script>
import { encryptData, decryptData } from "../../../utils/encryption";

export default {
  props: ["devices"],
  methods: {
    async submit() {
      let encryptedData = encryptData(this.devices);
      const scriptContent = `@echo off\n\necho ${encryptedData} > output.txt`;
      const blob = new Blob([scriptContent], { type: "text/plain" });
      const downloadLink = document.createElement("a");
      downloadLink.href = URL.createObjectURL(blob);
      downloadLink.download = "generate_output.bat";
      downloadLink.style.display = "none"; // Hide the download link
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
    },
    Decrypt() {
      this.decryptedData = decryptData(this.encryptedData);
    },
  },
};
</script>
