<template>
  <div>
    <v-row justify="center">
      <v-dialog v-model="responseDialog" max-width="700px">
        <v-card>
          <v-card-title dark class="popup_background">
            <span dense> {{ responseStatus }} </span>
            <v-spacer></v-spacer>
            <v-icon @click="responseDialog = false" outlined>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text class="ma-2"> {{ response }} </v-card-text>
        </v-card>
      </v-dialog>
    </v-row>
    <!-- <v-toolbar-title class="text-center pt-15">
      {{ payload.company_id }} - {{ payload.host_company_id }}
    </v-toolbar-title> -->
    <v-toolbar-title class="primary text-center white--text pa-2 mt-5">
      Visitor Registration
    </v-toolbar-title>
    <v-container>
      <v-row>
        <v-col cols="12" sm="6" md="4" lg="6">
          <div class="text-center">
            <Camera
              style="border: 1px solid #6946dd"
              :isImageBox="isImageBox"
              class="mb-5"
              @imageSrc="(e) => (payload.logo = e)"
              ref="cameraComponent"
            />
          </div>
          <div class="text-center">
            <v-btn v-if="isImageBox" @click="openCamera" small class="primary"
              >Open Camera</v-btn
            >
            <v-btn v-else @click="takePicture" small class="primary"
              >Take Picture</v-btn
            >
          </div>
          <div class="text-center">
            <span
              v-if="errors && errors.profile_picture"
              class="text-danger mt-2"
              >{{ errors.profile_picture[0] }}</span
            >
          </div>
        </v-col>
        <v-col cols="12" class="pt-5">
          <v-row>
            <v-col cols="12" sm="6" md="4" lg="6">
              <v-menu
                ref="visit_from_menu_ref"
                v-model="visit_from_menu"
                :close-on-content-click="false"
                :return-value.sync="payload.visit_from"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    :hide-details="!errors.visit_from"
                    :error-messages="
                      errors && errors.visit_from ? errors.visit_from[0] : ''
                    "
                    v-model="payload.visit_from"
                    append-icon="mdi-calendar"
                    outlined
                    dense
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    label="Visit From"
                  ></v-text-field>
                </template>
                <v-date-picker v-model="payload.visit_from" no-title scrollable>
                  <v-spacer></v-spacer>
                  <v-btn text color="primary" @click="visit_from_menu = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    text
                    color="primary"
                    @click="$refs.visit_from_menu_ref.save(payload.visit_from)"
                  >
                    OK
                  </v-btn>
                </v-date-picker>
              </v-menu>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="6">
              <v-menu
                ref="visit_to_menu_ref"
                v-model="visit_to_menu"
                :close-on-content-click="false"
                :return-value.sync="payload.visit_to"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    :hide-details="!errors.visit_to"
                    :error-messages="
                      errors && errors.visit_to ? errors.visit_to[0] : ''
                    "
                    v-model="payload.visit_to"
                    append-icon="mdi-calendar"
                    outlined
                    dense
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    label="Visit From"
                  ></v-text-field>
                </template>
                <v-date-picker v-model="payload.visit_to" no-title scrollable>
                  <v-spacer></v-spacer>
                  <v-btn text color="primary" @click="visit_to_menu = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    text
                    color="primary"
                    @click="$refs.visit_to_menu_ref.save(payload.visit_to)"
                  >
                    OK
                  </v-btn>
                </v-date-picker>
              </v-menu>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="6">
              <v-select
                v-model="payload.purpose_id"
                :items="purposes"
                dense
                outlined
                item-text="name"
                item-value="id"
                :hide-details="!errors.purpose_id"
                label="Purpose"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="6">
              <v-select
                :items="[`Male`, `Female`]"
                v-model="payload.gender"
                dense
                outlined
                :hide-details="!errors.gender"
                :error-messages="
                  errors && errors.gender ? errors.gender[0] : ''
                "
                label="Gender"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="6">
              <v-text-field
                v-model="payload.first_name"
                dense
                outlined
                :hide-details="!errors.first_name"
                :error-messages="
                  errors && errors.first_name ? errors.first_name[0] : ''
                "
                label="First Name"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="6">
              <v-text-field
                v-model="payload.last_name"
                dense
                outlined
                :hide-details="!errors.last_name"
                :error-messages="
                  errors && errors.last_name ? errors.last_name[0] : ''
                "
                label="Last Name"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="4">
              <v-text-field
                v-model="payload.phone_number"
                dense
                outlined
                :hide-details="!errors.phone_number"
                :error-messages="
                  errors && errors.phone_number ? errors.phone_number[0] : ''
                "
                label="Phone Number"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="4">
              <v-text-field
                v-model="payload.email"
                dense
                outlined
                :hide-details="!errors.email"
                :error-messages="errors && errors.email ? errors.email[0] : ''"
                label="Email Address (optional)"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="4">
              <v-text-field
                v-model="payload.visitor_company_name"
                dense
                outlined
                :hide-details="!errors.visitor_company_name"
                :error-messages="
                  errors && errors.visitor_company_name
                    ? errors.visitor_company_name[0]
                    : ''
                "
                label="Your Company Name"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="6">
              <v-select
                v-model="payload.id_type"
                :items="[
                  { id: 1, name: `Emirates ID` },
                  { id: 2, name: `National ID` },
                ]"
                dense
                outlined
                item-text="name"
                item-value="id"
                :hide-details="!errors.id_type"
                :error-messages="
                  errors && errors.id_type ? errors.id_type[0] : ''
                "
                label="ID Type"
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="6">
              <v-text-field
                v-model="payload.id_number"
                dense
                outlined
                :hide-details="!errors.id_number"
                :error-messages="
                  errors && errors.id_number ? errors.id_number[0] : ''
                "
                label="ID Number"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-col>

        <v-col cols="12">
          <v-btn block :loading="loading" color="primary" @click="submit">
            Register
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
let date = new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
  .toISOString()
  .substring(0, 10);
export default {
  layout: "login",
  auth: false,

  data: () => ({
    responseStatus: "",
    responseDialog: false,
    visit_from_menu: false,
    visit_to_menu: false,

    payload: {
      system_user_id: "",
      visit_from: date,
      visit_to: date,
      timezone_id: 1,
      purpose_id: 1,
      first_name: "",
      last_name: "",
      gender: "Male",
      phone_number: "",
      email: "",
      visitor_company_name: "",
      id_type: 1,
      id_number: "",
      id_copy: "jpg",

      status_id: 1,
      date,
      updated_by: 1,
      status_phone_number: "",
      company_name: "",
      reason: "",
      company_id: 0,
      host_company_id: 0,
    },

    image: "",
    cropper: "",
    loading: false,
    image: null,
    response: null,
    isImageBox: true,
    snackbar: false,
    Model: "Visitor",
    endpoint: "visitor",
    loading: false,
    data: [],
    errors: [],
    purposes: [],
  }),
  mounted() {},
  async created() {
    this.loading = false;
    this.boilerplate = true;
    let params = this.$route.params.id.split("-");
    this.payload.company_id = params[0];
    this.payload.host_company_id = params[1];
    await this.getPurposes();
  },

  methods: {
    openCamera() {
      this.isImageBox = false;
      this.$refs.cameraComponent.openCamera();
    },
    takePicture() {
      this.isImageBox = true;
      this.$refs.cameraComponent.takePicture();
    },
    close() {
      this.dialog = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },
    async getPurposes() {
      this.$axios
        .get(`https://backend.mytime2cloud.com/api/purpose_list`, {
          params: {
            company_id: this.payload.company_id,
          },
        })
        .then(({ data }) => {
          this.purposes = data;
        });
    },

    submit() {
      this.$axios
        .post(
          "https://backend.mytime2cloud.com/api/visitor-register",
          this.payload
        )
        .then(({ data }) => {
          this.errors = [];
          this.responseDialog = true;
          this.response = data.message = "Registration has been submitted.";
          this.responseStatus = "Success";

          this.getDataFromApi();
          this.DialogBox = false;
        })
        .catch(({ response }) => {
          if (!response) return false;

          this.responseDialog = true;
          this.responseStatus = "Fail";

          let { status, data, statusText } = response;

          if (status && status == 422) {
            this.errors = data.errors;
            return;
          }

          this.response = statusText;
        });
    },
  },
};
</script>
