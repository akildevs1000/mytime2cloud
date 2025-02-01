<template>
  <v-card flat>
    <v-card-text>
      <v-row
      >
        <v-col cols="12">
          <v-tabs v-model="tab">
        <v-tab>Visa</v-tab>
        <v-tab>Emirate</v-tab>
        <v-tab>Passport</v-tab>
      </v-tabs>
      <v-tabs-items v-model="tab">
        <v-tab-item>
          <v-card
            flat
            v-if="visaObject"
            class="d-flex flex-column"
          >
            <div class="text-right">
              <v-icon small color="primary" @click="editForm = !editForm"
                >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
              >
            </div>
            <v-simple-table dense flat class="my-simple-table">
              <tbody>
                <tr>
                  <td style="width: 200px">Visa</td>
                  <td>
                    <span v-if="!editForm">{{ visaObject.visa_no }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="visaObject.visa_no"
                      color="primary"
                      autofocus
                      :hide-details="!errors.visa_no"
                      :error-messages="
                        errors && errors.visa_no ? errors.visa_no[0] : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Place of Issue</td>
                  <td>
                    <span v-if="!editForm">{{
                      visaObject.place_of_issues
                    }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="visaObject.place_of_issues"
                      color="primary"
                      :hide-details="!errors.place_of_issues"
                      :error-messages="
                        errors && errors.place_of_issues
                          ? errors.place_of_issues[0]
                          : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Country</td>
                  <td>
                    <span v-if="!editForm">{{ visaObject.country }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="visaObject.country"
                      color="primary"
                      :hide-details="!errors.country"
                      :error-messages="
                        errors && errors.country ? errors.country[0] : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Issue Date</td>
                  <td>
                    <span v-if="!editForm">{{ visaObject.issue_date }}</span>

                    <v-menu
                      v-else
                      v-model="visa_issue_date"
                      :close-on-content-click="false"
                      :return-value.sync="visa_issue_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="visaObject.issue_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.issue_date"
                          :error-messages="
                            errors && errors.issue_date
                              ? errors.issue_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="visaObject.issue_date"
                        no-title
                        scrollable
                        @input="visa_issue_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Expiry Date</td>
                  <td>
                    <span v-if="!editForm">{{ visaObject.expiry_date }}</span>

                    <v-menu
                      v-else
                      v-model="visa_expiry_date"
                      :close-on-content-click="false"
                      :return-value.sync="visa_expiry_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="visaObject.expiry_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.expiry_date"
                          :error-messages="
                            errors && errors.expiry_date
                              ? errors.expiry_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="visaObject.expiry_date"
                        no-title
                        scrollable
                        @input="visa_expiry_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Labour No</td>
                  <td>
                    <span v-if="!editForm">{{ visaObject.labour_no }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="visaObject.labour_no"
                      color="primary"
                      :hide-details="!errors.labour_no"
                      :error-messages="
                        errors && errors.labour_no ? errors.labour_no[0] : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Labour Issue Date</td>
                  <td>
                    <span v-if="!editForm">{{
                      visaObject.labour_issue_date
                    }}</span>

                    <v-menu
                      v-else
                      v-model="labour_issue_date"
                      :close-on-content-click="false"
                      :return-value.sync="labour_issue_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="visaObject.labour_issue_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.labour_issue_date"
                          :error-messages="
                            errors && errors.labour_issue_date
                              ? errors.labour_issue_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="visaObject.labour_issue_date"
                        no-title
                        scrollable
                        @input="labour_issue_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Labour Expiry Date</td>
                  <td>
                    <span v-if="!editForm">{{
                      visaObject.labour_expiry_date
                    }}</span>
                    <v-menu
                      v-else
                      v-model="labour_expiry_date"
                      :close-on-content-click="false"
                      :return-value.sync="labour_expiry_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="visaObject.labour_expiry_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.labour_expiry_date"
                          :error-messages="
                            errors && errors.labour_expiry_date
                              ? errors.labour_expiry_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="visaObject.labour_expiry_date"
                        no-title
                        scrollable
                        @input="labour_expiry_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>
            <v-card-actions class="mt-auto">
              <v-spacer></v-spacer>
              <v-btn
                :disabled="!editForm"
                x-small
                class="grey white--text"
                @click="close"
                >Cancel</v-btn
              >
              <v-btn
                :disabled="!editForm"
                x-small
                class="primary"
                :loading="loading"
                @click="submit('Visa', 'visa', visaObject)"

                >Save</v-btn
              >
            </v-card-actions>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card
            flat
            v-if="emirateObject"
            class="d-flex flex-column"
          >
            <div class="text-right">
              <v-icon small color="primary" @click="editForm = !editForm"
                >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
              >
            </div>
            <v-simple-table dense flat class="my-simple-table">
              <tbody>
                <tr>
                  <td style="width: 200px">Emirates Id</td>
                  <td>
                    <span v-if="!editForm">{{ emirateObject.emirate_id }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="emirateObject.emirate_id"
                      color="primary"
                      autofocus
                      :hide-details="!errors.emirate_id"
                      :error-messages="
                        errors && errors.emirate_id ? errors.emirate_id[0] : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Nationality</td>
                  <td>
                    <span v-if="!editForm">{{
                      emirateObject.nationality
                    }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="emirateObject.nationality"
                      color="primary"
                      :hide-details="!errors.nationality"
                      :error-messages="
                        errors && errors.nationality
                          ? errors.nationality[0]
                          : ''
                      "
                    />
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px">Issue Date</td>
                  <td>
                    <span v-if="!editForm">{{ emirateObject.issue }}</span>

                    <v-menu
                      v-else
                      v-model="emirate_issue_date"
                      :close-on-content-click="false"
                      :return-value.sync="emirate_issue_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="emirateObject.issue"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.issue"
                          :error-messages="
                            errors && errors.issue ? errors.issue[0] : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="emirateObject.issue"
                        no-title
                        scrollable
                        @input="emirate_issue_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Expiry Date</td>
                  <td>
                    <span v-if="!editForm">{{ emirateObject.expiry }}</span>

                    <v-menu
                      v-else
                      v-model="emirate_expiry_date"
                      :close-on-content-click="false"
                      :return-value.sync="emirate_expiry_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="emirateObject.expiry"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.expiry"
                          :error-messages="
                            errors && errors.expiry ? errors.expiry[0] : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="emirateObject.expiry"
                        no-title
                        scrollable
                        @input="emirate_expiry_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Expiry Date</td>
                  <td>
                    <span v-if="!editForm">{{
                      emirateObject.date_of_birth
                    }}</span>

                    <v-menu
                      v-else
                      v-model="emirate_date_of_date"
                      :close-on-content-click="false"
                      :return-value.sync="emirate_date_of_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="emirateObject.date_of_birth"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.date_of_birth"
                          :error-messages="
                            errors && errors.date_of_birth
                              ? errors.date_of_birth[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="emirateObject.date_of_birth"
                        no-title
                        scrollable
                        @input="emirate_date_of_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>

            <v-card-actions class="mt-auto">
              <v-spacer></v-spacer>
              <v-btn
                :disabled="!editForm"
                x-small
                class="grey white--text"
                @click="close"
                >Cancel</v-btn
              >
              <v-btn
                :disabled="!editForm"
                x-small
                class="primary"
                :loading="loading"
                @click="submit('Emirates', 'emirate', emirateObject)"
                >Save</v-btn
              >
            </v-card-actions>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card
            flat
            v-if="passportObject"
            class="d-flex flex-column"
          >
            <div class="text-right">
              <v-icon small color="primary" @click="editForm = !editForm"
                >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
              >
            </div>
            <v-simple-table dense flat class="my-simple-table">
              <tbody>
                <tr>
                  <td style="width: 200px">Passport No</td>
                  <td>
                    <span v-if="!editForm">{{
                      passportObject.passport_no
                    }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="passportObject.passport_no"
                      color="primary"
                      autofocus
                      :hide-details="!errors.passport_no"
                      :error-messages="
                        errors && errors.passport_no
                          ? errors.passport_no[0]
                          : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Place Of Issue</td>
                  <td>
                    <span v-if="!editForm">{{
                      passportObject.place_of_issues
                    }}</span>
                    <v-text-field
                      v-else
                      :readonly="!editForm"
                      style="border-bottom: 1px solid #eaeaea"
                      class="small-input-font"
                      dense
                      v-model="passportObject.place_of_issues"
                      color="primary"
                      autofocus
                      :hide-details="!errors.place_of_issues"
                      :error-messages="
                        errors && errors.place_of_issues
                          ? errors.place_of_issues[0]
                          : ''
                      "
                    />
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Issue Date</td>
                  <td>
                    <span v-if="!editForm">{{
                      passportObject.issue_date
                    }}</span>

                    <v-menu
                      v-else
                      v-model="passport_issue_date"
                      :close-on-content-click="false"
                      :return-value.sync="passport_issue_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="passportObject.issue_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.issue_date"
                          :error-messages="
                            errors && errors.issue_date
                              ? errors.issue_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="passportObject.issue_date"
                        no-title
                        scrollable
                        @input="passport_issue_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>

                <tr>
                  <td style="width: 200px">Expiry Date</td>
                  <td>
                    <span v-if="!editForm">{{
                      passportObject.expiry_date
                    }}</span>

                    <v-menu
                      v-else
                      v-model="passport_expiry_date"
                      :close-on-content-click="false"
                      :return-value.sync="passport_expiry_date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          append-icon="mdi-calendar"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="passportObject.expiry_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.expiry_date"
                          :error-messages="
                            errors && errors.expiry_date
                              ? errors.expiry_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="passportObject.expiry_date"
                        no-title
                        scrollable
                        @input="passport_expiry_date = false"
                      ></v-date-picker>
                    </v-menu>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>

            <v-card-actions class="mt-auto">
              <v-spacer></v-spacer>
              <v-btn
                :disabled="!editForm"
                x-small
                class="grey white--text"
                @click="close"
                >Cancel</v-btn
              >
              <v-btn
                :disabled="!editForm"
                x-small
                class="primary"
                :loading="loading"
                @click="submit('Passport', 'passport', passportObject)"
                >Save</v-btn
              >
            </v-card-actions>
          </v-card>
        </v-tab-item>
      </v-tabs-items>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      editForm: false,
      tab: null,
      emirate_info: false,
      errors: [],
      snackbar: false,
      response: "",

      visa_issue_date: false,
      visa_expiry_date: false,
      labour_issue_date: false,
      labour_expiry_date: false,

      emirate_issue_date: false,
      emirate_expiry_date: false,
      emirate_date_of_date: false,

      passport_issue_date: false,
      passport_expiry_date: false,

      visaObject: null,
      emirateObject: null,
      passportObject: null,

      loading: false,
    };
  },
  created() {
    this.getVisaInfo(this.employeeId);
    this.getEmirateInfo(this.employeeId);
    this.getPassportInfo(this.employeeId);
  },
  methods: {
    getVisaInfo(id) {
      this.$axios.get(`visa/${id}`).then(({ data }) => {
        this.visaObject = {
          ...data,
          employee_id: id,
        };
      });
    },
    getEmirateInfo(id) {
      this.$axios.get(`emirate/${id}`).then(({ data }) => {
        this.emirateObject = {
          ...data,
          employee_id: id,
        };
      });
    },
    getPassportInfo(id) {
      this.$axios.get(`passport/${id}`).then(({ data }) => {
        this.passportObject = {
          ...data,
          employee_id: id,
        };
      });
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    can(item) {
      return true;
    },

    close() {
      this.emirate_info = false;
      this.errors = [];
      setTimeout(() => {
        this.$emit("close-popup");
      }, 1000);
    },

    submit(model, endpoint, selectedPayload) {
      let payload = {
        ...selectedPayload,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.employeeId,
      };

      this.$axios
        .post(endpoint, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = model + " Info has been added";
            this.$emit("eventFromchild");
            this.close();
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
