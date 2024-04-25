<!-- FileUpload.vue -->
<template>
  <v-dialog v-model="dialog" max-width="500px">
    <template v-slot:activator="{ on }">
      <v-icon color="black" dark v-on="on">mdi-upload-outline</v-icon>
    </template>
    <v-card>
      <v-card-title>Upload File</v-card-title>
      <v-card-text>
        <v-file-input
          outlined
          dense
          append-icon="mdi-upload-outline"
          prepend-icon=""
          v-model="file"
          label="Choose a file"
          @change="handleFileUpload"
        ></v-file-input>
        <v-btn color="primary" @click="submit">Submit</v-btn>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { decryptData } from "../utils/encryption";

export default {
  data() {
    return {
      file: null,
      dialog: false,
      token: null,
      encryptedData: null,
    };
  },
  methods: {
    handleFileUpload() {
      // Handle file upload logic here
      const fileData = this.file; // Access the uploaded file
      this.readFileContents(fileData);
    },
    readFileContents(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const fileContent = e.target.result; // This is the content of the uploaded file
        // Call a method to process the file content
        this.processFileContent(fileContent);
      };
      reader.readAsText(file);
    },
    processFileContent(content) {
      const pattern = /echo (.*) > output.txt/;
      const matches = content.match(pattern);
      if (matches) {
        const echoCommand = matches[0]; // Full echo command
        this.token = matches[1]; // Extracted message
        console.log("Message:", this.token);
      } else {
        console.log("No match found.");
      }
    },
    submit() {
      this.loading = true;
      let decryptedData = decryptData(this.token);
      this.$axios
        .post(`decrypt-devices`, {
          company_id: this.$auth.user.company_id,
          devices: decryptedData,
          device_ids: decryptedData.map((e) => e.device_id),
        })
        .then(({ data }) => {
          if (!data.status) {
            alert(data.message);
            return;
          }
          this.$emit("success");

          this.loading = false;
          this.dialog = false;
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });
    },
    handleErrorResponse(response) {
      this.loading = false;
      if (!response) {
        this.$emit("error", "An unexpected error occurred.");
        return;
      }
      let { status, data, statusText } = response;

      if (status && status == 422) {
        this.errors = data.errors;
        return;
      }

      this.$emit("error", statusText);
    },
  },
};
</script>
