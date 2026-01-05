<template>
  <div class="text-center">
    <v-dialog v-model="dialog" width="500">
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          small
          class="primary"
          v-bind="attrs"
          v-on="on"
        >
          <v-icon color="white" small> mdi-plus </v-icon> Company
        </v-btn>
      </template>

      <v-card>
        <v-toolbar flat class="primary white--text" dense>
          Create Company <v-spacer></v-spacer
          ><v-icon @click="close" color="white">mdi-close</v-icon></v-toolbar
        >

        <v-card-text>
          <v-row class="mt-4">
            <v-col cols="12">
              <v-text-field
                v-model="item.name"
                dense
                outlined
                hide-details
                label="Company Name *"
              ></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="item.contact_person_name"
                dense
                outlined
                hide-details
                label="Contact Person Name *"
              ></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="item.number"
                dense
                outlined
                hide-details
                label="Phone Number *"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="item.email"
                dense
                outlined
                hide-details
                label="Email *"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="item.location"
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
                class="white--text"
                dark
                @click="submit"
              >
                Submit
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["endpoint"],
  data() {
    return {
      item: {
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
  methods: {
    close() {
      this.dialog = false;
      this.loading = false;
      this.errorResponse = null;
    },
    async submit() {
      this.loading = true;
      try {
        await this.$axios.post(this.endpoint, this.item);
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
