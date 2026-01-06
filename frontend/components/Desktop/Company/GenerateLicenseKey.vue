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
                <v-btn small color="primary" @click="generateKey">
                  Generate
                </v-btn>
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

    async generateKey() {
      if (!this.payload.machine_id) {
        this.errorResponse = "Machine ID is required.";
        return;
      }

      if (!this.payload.expiry_date) {
        this.errorResponse = "Expiry date is required.";
        return;
      }

      this.errorResponse = null;

      const nonce = crypto.getRandomValues(new Uint32Array(1))[0].toString(36);

      const hash = await this.hashLicense(
        this.payload.machine_id,
        this.payload.expiry_date,
        nonce
      );

      this.payload.license_key = `LIC-${hash}`;
    },

    async hashLicense(machineId, expiryDate, nonce) {
      const secret = "XTREME";
      const raw = `${machineId}|${expiryDate}|${nonce}|${secret}`;

      const enc = new TextEncoder().encode(raw);
      const hash = await crypto.subtle.digest("SHA-256", enc);

      const bytes = Array.from(new Uint8Array(hash));
      const base32 = this.toBase32(bytes).slice(0, 16);

      return base32.match(/.{1,4}/g).join("-");
    },

    toBase32(bytes) {
      const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
      let bits = "";
      let output = "";

      for (const b of bytes) bits += b.toString(2).padStart(8, "0");

      for (let i = 0; i + 5 <= bits.length; i += 5) {
        output += alphabet[parseInt(bits.slice(i, i + 5), 2)];
      }

      return output;
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
