<template>
  <v-dialog v-model="dialog" width="560">
    <template v-slot:activator="{ on, attrs }">
      <div v-bind="attrs" v-on="on">
        <v-icon color="blue" small>mdi-key</v-icon>
        License Key
      </div>
    </template>

    <v-card>
      <v-toolbar flat class="primary white--text" dense>
        Generate License Key
        <v-spacer></v-spacer>
        <v-icon @click="close" color="white">mdi-close</v-icon>
      </v-toolbar>

      <v-card-text>
        <v-row class="mt-4">
          <v-col cols="12">
            <v-text-field
              v-model="payload.name"
              dense
              outlined
              hide-details
              label="Company Name *"
            />
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.contact_person_name"
              dense
              outlined
              hide-details
              label="Contact Person Name *"
            />
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.number"
              dense
              outlined
              hide-details
              label="Phone Number *"
            />
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.email"
              dense
              outlined
              hide-details
              label="Email *"
            />
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.location"
              dense
              outlined
              hide-details
              label="Location *"
            />
          </v-col>

          <!-- Expiry date -->
          <v-col cols="12">
            <v-menu
              ref="expiryMenu"
              v-model="expiryMenu"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="payload.expiry_date"
                  label="Expiry Date *"
                  dense
                  outlined
                  hide-details
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  append-icon="mdi-calendar"
                />
              </template>

              <v-date-picker
                no-title
                v-model="payload.expiry_date"
                @input="expiryMenu = false"
              />
            </v-menu>
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.machine_id"
              dense
              outlined
              hide-details
              label="Machine Code *"
            >
            </v-text-field>
          </v-col>

          <!-- License key -->
          <v-col cols="12">
            <v-text-field
              v-model="payload.license_key"
              dense
              outlined
              hide-details
              label="License Key *"
              readonly
            >
              <template v-slot:append>
                <v-btn small color="primary" @click="Encrypt"> Generate </v-btn>
              </template>
            </v-text-field>

            <div class="mt-1 text-caption grey--text">
              Tip: Generate after selecting expiry date.
            </div>
          </v-col>

          <v-col cols="12" v-if="errorResponse">
            <span class="red--text">{{ errorResponse }}</span>
          </v-col>

          <v-col cols="12" class="text-right">
            <v-btn small color="grey" class="white--text" dark @click="close">
              Close
            </v-btn>
            <v-btn :loading="loading" small color="primary" @click="submit">
              Submit
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { encryptData } from "../../../utils/license-crypto";

export default {
  props: ["item", "endpoint"],
  data() {
    return {
      payload: {
        id: null,
        name: "",
        contact_person_name: "",
        number: "",
        email: "",
        location: "",
        expiry_date: "", // YYYY-MM-DD (from v-date-picker)
        license_key: "", // generated
        machine_id: "", // generated
      },
      dialog: false,
      loading: false,
      successResponse: null,
      errorResponse: null,

      expiryMenu: false,
    };
  },
  created() {
    // clone to avoid mutating parent object directly
    this.payload = {
      ...this.payload,
      ...(this.item || {}),
    };

    // if editing existing record and fields are missing
    if (!this.payload.expiry_date) this.payload.expiry_date = "";
    if (!this.payload.license_key) this.payload.license_key = "";
    if (!this.payload.machine_id) this.payload.machine_id = "";
  },
  methods: {
    close() {
      this.dialog = false;
      this.loading = false;
      this.errorResponse = null;
    },

    async Encrypt() {
      let {
        id,
        license_key,
        created_at,
        devices,
        devices_count,
        is_used,
        updated_at,
        ...restOfPayload
      } = this.payload;

      let encryptedData = encryptData(restOfPayload, this.payload.machine_id);
      this.payload.license_key = encryptedData;
      // Prepare filename based on the first word of the name
      const name = restOfPayload.name;
      const firstWord = name ? name.split(" ")[0] : null;
      const filename = firstWord
        ? `${firstWord}_license_key.txt`
        : "license_key.txt";

      const blob = new Blob([encryptedData], { type: "text/plain" });
      const downloadLink = document.createElement("a");
      downloadLink.href = URL.createObjectURL(blob);
      downloadLink.download = filename;
      downloadLink.style.display = "none"; // Hide the download link
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
    },
    async submit() {
      this.loading = true;
      try {
        await this.$axios.put(
          `${this.endpoint}/${this.payload.id}`,
          this.payload
        );
        this.close();
        this.$emit("response");
      } catch (error) {
        this.errorResponse = error?.response?.data?.message || "Unknown error";
        this.loading = false;
      }
    },
  },
};
</script>
