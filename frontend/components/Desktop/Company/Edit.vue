<template>
  <v-dialog v-model="dialog" width="500">
    <template v-slot:activator="{ on, attrs }">
      <div v-bind="attrs" v-on="on">
        <v-icon color="blue" small> mdi-pencil </v-icon>
        Edit
      </div>
    </template>

    <v-card>
      <v-toolbar flat class="primary white--text" dense>
        Edit Company <v-spacer></v-spacer
        ><v-icon @click="close" color="white">mdi-close</v-icon></v-toolbar
      >

      <v-card-text>
        <v-row class="mt-4">
          <v-col cols="12">
            <v-text-field
              v-model="payload.name"
              dense
              outlined
              hide-details
              label="Company Name *"
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.contact_person_name"
              dense
              outlined
              hide-details
              label="Contact Person Name *"
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="payload.number"
              dense
              outlined
              hide-details
              label="Phone Number *"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="payload.email"
              dense
              outlined
              hide-details
              label="Email *"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="payload.location"
              dense
              outlined
              hide-details
              label="Location *"
            ></v-text-field>
          </v-col>
          <v-col cols="12" v-if="errorResponse">
            <span class="red--text">{{ errorResponse }}</span>
          </v-col>
          <v-col cols="12" class="text-right">
            <v-btn small color="grey" class="white--text" dark @click="close">
              Close
            </v-btn>
            <v-btn
              :loading="loading"
              small
              color="primary"
              @click="submit"
            >
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
        name: "",
        contact_person_name: "",
        number: "",
        email: "",
        location: "",
      },
      dialog: false,
      loading: false,
      successResponse: null,
      errorResponse: null,
    };
  },
  created() {
    this.payload = this.item;
  },
  methods: {
    close() {
      this.dialog = false;
      this.loading = false;
      this.errorResponse = null;
    },
    async submit() {
      this.loading = true;
      try {
        await this.$axios.put(
          `${this.endpoint}/${this.payload.id}`,
          this.payload
        );
        this.close();
        this.$emit("response", "Record has been inserted");
      } catch (error) {
        this.errorResponse = error?.response?.data?.message || "Unknown error";
        this.loading = false;
      }
    },
  },
};
</script>
