<template>
  <v-app>
    <v-container fluid>
      <div class="text-center">
        <v-dialog v-model="dialog" width="300">
          <v-card style="background: none">
            <v-toolbar style="background: none" flat dense>
              <v-spacer></v-spacer>
              <!-- <v-icon @click="dialog = false">mdi-close</v-icon> -->
            </v-toolbar>

            <v-card-text>
              <p class="text-center">
                <v-img
                  :src="response_image"
                  alt="Avatar"
                  height="125px"
                  width="125px"
                  style="display: inline-block"
                ></v-img>
              </p>
            </v-card-text>
          </v-card>
        </v-dialog>
      </div>
      <v-app-bar color="primary" fixed app>
        <v-row>
          <v-col cols="4">
            <v-img
              src="/logo-2.png"
              max-height="100"
              max-width="150"
              contain
              class="pa-2"
            ></v-img>
          </v-col>

          <v-col cols="4">
            <v-toolbar-title class="text-center white--text pa-2">
              <b>Device List Encrypter</b>
            </v-toolbar-title>
          </v-col>
        </v-row>
      </v-app-bar>

      <v-container class="mt-15">
        <v-row v-for="(item, index) in items" :key="index">
          <v-col cols="4">
            <v-text-field
              v-model="item.model_number"
              dense
              outlined
              :hide-details="!errors.model_number"
              :error-messages="
                errors && errors.model_number ? errors.model_number[0] : ''
              "
              label="Device Model *"
            ></v-text-field>
          </v-col>

          <v-col cols="4">
            <v-text-field
              v-model="item.device_id"
              dense
              outlined
              :hide-details="!errors.device_id"
              :error-messages="
                errors && errors.device_id ? errors.device_id[0] : ''
              "
              label="Device Serial *"
            ></v-text-field>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <v-icon @click="addItem"> mdi-plus-circle-outline </v-icon>
            <v-icon v-if="items && items.length > 1" @click="removeItem">
              mdi-delete
            </v-icon>
          </v-col>
          <v-col cols="12">
            <!-- <pre>
              {{ items }}
            </pre> -->
            <table>
              <tr>
                <th>Device Model</th>
                <th>Device Serial</th>
              </tr>
              <tr
                v-for="(displayItem, displayIndex) in items"
                :key="displayIndex"
              >
                <td>{{ displayItem.model_number || "---" }}</td>
                <td>{{ displayItem.device_id || "---" }}</td>
              </tr>
            </table>
          </v-col>
          <v-col cols="12">
            <v-btn large :loading="loading" color="primary" @click="submit">
              Generate
            </v-btn>
          </v-col>
          <!-- <v-col cols="12">
            <v-card outlined style="overflow: scroll">
              {{ response }}
            </v-card>
          </v-col> -->
        </v-row>
      </v-container>
    </v-container>
  </v-app>
</template>

<script>
export default {
  layout:"login",
  data: () => ({
    dialog: false,
    response_image: "/success.png",
    message: "Record has been inserted",

    items: [
      {
        model_number: "",
        device_id: "",
        name: "Default Name",
        status_id: 1,
        company_id: 1,
        ip: "0.0.0.0",
        port: "0000",
      },
    ],

    response: null,
    loading: false,
    errors: [],
  }),
  mounted() {},
  async created() {},

  methods: {
    addItem() {
      let json = {
        model_number: "",
        device_id: "",
        name: "Default Name",
        status_id: 1,
        company_id: 1,
        ip: "0.0.0.0",
        port: "0000",
      };
      this.items.push(json);
    },
    removeItem() {
      this.items.pop();
    },
    submit() {
      this.loading = true;
      let devices = this.items;
      this.$axios
        .post(`encrypt-devices`, devices)
        .then(({ data }) => {
          this.response = data.record;
          // Generate batch script content
          const scriptContent = `@echo off\n\necho ${this.response} > output.txt`;

          // Create a Blob object from script content
          const blob = new Blob([scriptContent], { type: "text/plain" });

          // Create a temporary anchor element
          const downloadLink = document.createElement("a");
          downloadLink.href = URL.createObjectURL(blob);
          downloadLink.download = "generate_output.bat";
          downloadLink.style.display = "none"; // Hide the download link

          // Append the anchor element to the document body
          document.body.appendChild(downloadLink);

          // Trigger a click event on the download link
          downloadLink.click();

          // Remove the anchor element after download
          document.body.removeChild(downloadLink);
          this.loading = false;
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });
    },
    handleErrorResponse(response) {
      if (!response) {
        alert("An unexpected error occurred");
        return;
      }
      let { status, data } = response;

      if (status && status == 422) {
        this.errors = data.errors;
        return;
      }

      alert("An unexpected error occurred");
    },
  },
};
</script>

<style scoped>
@import url("@/assets/tableStyles.css");
</style>
