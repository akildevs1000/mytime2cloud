<template>
  <v-card flat>
    <v-card-text >
      <v-row
        v-if="employeeObject && employeeObject.id"
        class="d-flex align-center"
      >
        <v-col cols="12">
          <v-tabs v-model="tab">
            <v-tab>Contact</v-tab>
            <v-tab>Home Country</v-tab>
          </v-tabs>
          <v-tabs-items v-model="tab">
            <v-tab-item>
              <v-card flat v-if="contactItem">
                <div class="text-right">
                  <v-icon small color="primary" @click="editForm = !editForm"
                    >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
                  >
                </div>
                <v-simple-table dense flat class="my-simple-table">
                  <tbody>
                    <tr>
                      <td style="width: 50%">Phone Number</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.phone_number
                        }}</span>
                        <v-text-field
                          v-else
                          autofocus
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.phone_number"
                          color="primary"
                          :hide-details="!errors.phone_number"
                          :error-messages="
                            errors && errors.phone_number
                              ? errors.phone_number[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Whatsapp Number</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.whatsapp_number
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.whatsapp_number"
                          color="primary"
                          :hide-details="!errors.whatsapp_number"
                          :error-messages="
                            errors && errors.whatsapp_number
                              ? errors.whatsapp_number[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Alertnate Email</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.local_email
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.local_email"
                          color="primary"
                          :hide-details="!errors.local_email"
                          :error-messages="
                            errors && errors.local_email
                              ? errors.local_email[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Relative Contact</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.phone_relative_number
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.phone_relative_number"
                          color="primary"
                          :hide-details="!errors.phone_relative_number"
                          :error-messages="
                            errors && errors.phone_relative_number
                              ? errors.phone_relative_number[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Relation</td>
                      <td>
                        <span v-if="!editForm">{{ contactItem.relation }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.relation"
                          color="primary"
                          :hide-details="!errors.relation"
                          :error-messages="
                            errors && errors.relation ? errors.relation[0] : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Local Address</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.local_address
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.local_address"
                          color="primary"
                          :hide-details="!errors.local_address"
                          :error-messages="
                            errors && errors.local_address
                              ? errors.local_address[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Local City</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.local_city
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.local_city"
                          color="primary"
                          :hide-details="!errors.local_city"
                          :error-messages="
                            errors && errors.local_city
                              ? errors.local_city[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Local Country</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.local_country
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.local_country"
                          color="primary"
                          :hide-details="!errors.local_country"
                          :error-messages="
                            errors && errors.local_country
                              ? errors.local_country[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                  </tbody>
                </v-simple-table>

                <v-card-actions>
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
                    @click="submit"
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-tab-item>

            <v-tab-item>
              <v-card flat v-if="contactItem">
                <div class="text-right">
                  <v-icon small color="primary" @click="editForm = !editForm"
                    >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
                  >
                </div>
                <v-simple-table dense flat class="my-simple-table">
                  <tbody>
                    <tr>
                      <td style="width: 50%">Address</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.home_address
                        }}</span>
                        <v-text-field
                          v-else
                          autofocus
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_address"
                          color="primary"
                          :hide-details="!errors.home_address"
                          :error-messages="
                            errors && errors.home_address
                              ? errors.home_address[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Tel</td>
                      <td>
                        <span v-if="!editForm">{{ contactItem.home_tel }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_tel"
                          color="primary"
                          :hide-details="!errors.home_tel"
                          :error-messages="
                            errors && errors.home_tel ? errors.home_tel[0] : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Mobile</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.home_mobile
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_mobile"
                          color="primary"
                          :hide-details="!errors.home_mobile"
                          :error-messages="
                            errors && errors.home_mobile
                              ? errors.home_mobile[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Fax</td>
                      <td>
                        <span v-if="!editForm">{{ contactItem.home_fax }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_fax"
                          color="primary"
                          :hide-details="!errors.home_fax"
                          :error-messages="
                            errors && errors.home_fax ? errors.home_fax[0] : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">City</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.home_city
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_city"
                          color="primary"
                          :hide-details="!errors.home_city"
                          :error-messages="
                            errors && errors.home_city
                              ? errors.home_city[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">State</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.home_state
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_state"
                          color="primary"
                          :hide-details="!errors.home_state"
                          :error-messages="
                            errors && errors.home_state
                              ? errors.home_state[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Country</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.home_country
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_country"
                          color="primary"
                          :hide-details="!errors.home_country"
                          :error-messages="
                            errors && errors.home_country
                              ? errors.home_country[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 200px">Personal Email</td>
                      <td>
                        <span v-if="!editForm">{{
                          contactItem.home_email
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          style="border-bottom: 1px solid #eaeaea"
                          class="small-input-font"
                          dense
                          v-model="contactItem.home_email"
                          color="primary"
                          :hide-details="!errors.home_email"
                          :error-messages="
                            errors && errors.home_email
                              ? errors.home_email[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                  </tbody>
                </v-simple-table>

                <v-card-actions>
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
                    @click="submit"
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-tab-item>
          </v-tabs-items></v-col
        >
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: ["employeeId", "employeeObject"],
  data() {
    return {
      editForm: false,
      loading: false,
      tab: null,
      other_info: false,
      contact_info: false,
      response: "",
      snackbar: false,
      errors: [],
      contactItem: {},
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      this.$axios
        .get(
          `employee/${this.employeeId}?company_id=${this.$auth.user.company_id}`
        )
        .then(({ data }) => {
          this.contactItem = data;
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

    close() {
      this.contact_info = false;
      this.errors = [];
      setTimeout(() => {
        this.$emit("close-popup");
      }, 1000);
    },

    can(item) {
      return true;
    },

    submit() {
      let payload = {
        ...this.contactItem,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.employeeId,
      };
      this.$axios
        .post(`employee/update/contact`, payload)
        .then(({ data }) => {
          this.loading = false;
          this.$emit("eventFromchild");
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.getInfo();
            this.close();
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
