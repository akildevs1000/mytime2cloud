<template>
  <div>
    <v-dialog v-model="contact_info" max-width="700px">
      <v-card>
        <v-card-actions>
          <span class="headline">Contact Info </span>
          <v-spacer></v-spacer>
        </v-card-actions>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("local address")
                  }}</label>
                  <input
                    v-model="contactItem.local_address"
                    class="form-control"
                  />
                  <span
                    v-if="errors && errors.local_address"
                    class="text-danger mt-2"
                    >{{ errors.local_address[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  email") }}</label>
                  <input
                    v-model="contactItem.local_email"
                    class="form-control"
                    type="email"
                  />
                  <span
                    v-if="errors && errors.local_email"
                    class="text-danger mt-2"
                    >{{ errors.local_email[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("phone number")
                  }}</label>
                  <input
                    v-model="contactItem.phone_number"
                    class="form-control"
                    type="number"
                  />
                  <span
                    v-if="errors && errors.phone_number"
                    class="text-danger mt-2"
                    >{{ errors.phone_number[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("whatsapp number")
                  }}</label>
                  <input
                    v-model="contactItem.whatsapp_number"
                    class="form-control"
                    type="number"
                  />
                  <span
                    v-if="errors && errors.whatsapp_number"
                    class="text-danger mt-2"
                    >{{ errors.whatsapp_number[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("phone relative number")
                  }}</label>
                  <input
                    v-model="contactItem.phone_relative_number"
                    class="form-control"
                    type="number"
                  />
                  <span
                    v-if="errors && errors.phone_relative_number"
                    class="text-danger mt-2"
                    >{{ errors.phone_relative_number[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("relation") }}</label>
                  <input
                    v-model="contactItem.relation"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.relation"
                    class="text-danger mt-2"
                    >{{ errors.relation[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("local city") }}</label>
                  <input
                    v-model="contactItem.local_city"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.local_city"
                    class="text-danger mt-2"
                    >{{ errors.local_city[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("local country")
                  }}</label>
                  <input
                    v-model="contactItem.local_country"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.local_country"
                    class="text-danger mt-2"
                    >{{ errors.local_country[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="12">
                <a
                  href="javascrip:void(0)"
                  @click="add_other_contact_info = !add_other_contact_info"
                  >{{
                    caps(
                      `${
                        add_other_contact_info ? "hide" : "show"
                      } Home Country Details`
                    )
                  }}</a
                >
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  address") }}</label>
                  <input
                    v-model="contactItem.home_address"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.home_address"
                    class="text-danger"
                    >{{ errors.home_address[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("tel") }}</label>
                  <input
                    v-model="contactItem.home_tel"
                    class="form-control"
                    type="number"
                  />
                  <span v-if="errors && errors.home_tel" class="text-danger">{{
                    errors.home_tel[0]
                  }}</span>
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  mobile") }}</label>
                  <input
                    v-model="contactItem.home_mobile"
                    class="form-control"
                    type="number"
                  />
                  <span
                    v-if="errors && errors.home_mobile"
                    class="text-danger"
                    >{{ errors.home_mobile[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  fax") }}</label>
                  <input
                    v-model="contactItem.home_fax"
                    class="form-control"
                    type="number"
                  />
                  <span v-if="errors && errors.home_fax" class="text-danger">{{
                    errors.home_fax[0]
                  }}</span>
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  city") }}</label>
                  <input
                    v-model="contactItem.home_city"
                    class="form-control"
                    type="text"
                  />
                  <span v-if="errors && errors.home_city" class="text-danger">{{
                    errors.home_city[0]
                  }}</span>
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("state") }}</label>
                  <input
                    v-model="contactItem.home_state"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.home_state"
                    class="text-danger"
                    >{{ errors.home_state[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  country") }}</label>
                  <input
                    v-model="contactItem.home_country"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.home_country"
                    class="text-danger"
                    >{{ errors.home_country[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6" v-if="add_other_contact_info">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("  email") }}</label>
                  <input
                    v-model="contactItem.home_email"
                    class="form-control"
                    type="email"
                  />
                  <span
                    v-if="errors && errors.home_email"
                    class="text-danger"
                    >{{ errors.home_email[0] }}</span
                  >
                </div>
              </v-col>
              <span v-if="errors && errors.length" class="error--text">{{
                errors
              }}</span>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn class="error" small @click="close_contact_info">
            Cancel
          </v-btn>
          <v-btn class="primary" small @click="save_contact_info">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-responsive class="overflow-y-auto" max-height="700">
      <v-responsive height="100vh" class="text-center pa-2">
        <v-responsive min-height="0vh"></v-responsive>
        <div class="text-center text-body-2 mb-12">
          <table>
            <tr>
              <!-- <td>{{ contactItem }}</td> -->
            </tr>
            <tr>
              <th></th>
              <td style="text-align: right;">
                <v-icon
                  v-if="can(`employee_personal_edit_access`)"
                  @click="contact_info = true"
                  small
                  class="grey"
                  style="border-radius: 50%; padding: 5px"
                  color="secondary"
                  >mdi-pencil</v-icon
                >
              </td>
            </tr>
            <tr>
              <th colspan="2">
                <v-toolbar color="#E9E9E9" small dense flat rounded="2">
                  Local Country
                </v-toolbar>
              </th>
            </tr>

            <tr>
              <th>Email</th>
              <td>
                {{ caps(contactItem.local_email) }}
              </td>
            </tr>

            <tr>
              <th>Address</th>
              <td>
                {{ caps(contactItem.local_address) }}
              </td>
            </tr>

            <tr>
              <th>Whatsapp Number</th>
              <td>
                {{ contactItem.whatsapp_number }}
              </td>
            </tr>
            <tr>
              <th>Phone Number</th>
              <td>
                {{ contactItem.phone_number }}
              </td>
            </tr>
            <tr>
              <th>Phone Relative Number</th>
              <td>
                {{ contactItem.phone_relative_number }}
              </td>
            </tr>

            <tr>
              <th>Relation</th>
              <td>
                {{ contactItem.relation }}
              </td>
            </tr>

            <tr>
              <th>City</th>
              <td>
                {{ caps(contactItem.local_city) }}
              </td>
            </tr>
            <tr>
              <th>Country</th>
              <td>
                {{ caps(contactItem.local_country) }}
              </td>
            </tr>
            <th colspan="2">
              <v-toolbar color="#E9E9E9" small dense flat rounded="2">
                Home Country
              </v-toolbar>
            </th>

            <tr>
              <th>Email</th>
              <td>
                {{ caps(contactItem.home_email) }}
              </td>
            </tr>

            <tr>
              <th>Fax</th>
              <td>
                {{ caps(contactItem.home_fax) }}
              </td>
            </tr>

            <tr>
              <th>Telephone</th>
              <td>
                {{ caps(contactItem.home_tel) }}
              </td>
            </tr>

            <tr>
              <th>Phone Number</th>
              <td>
                {{ caps(contactItem.home_mobile) }}
              </td>
            </tr>

            <tr>
              <th>Address</th>
              <td>
                {{ caps(contactItem.home_address) }}
              </td>
            </tr>

            <tr>
              <th>City</th>
              <td>
                {{ caps(contactItem.home_city) }}
              </td>
            </tr>

            <tr>
              <th>State</th>
              <td>
                {{ caps(contactItem.home_state) }}
              </td>
            </tr>

            <tr>
              <th>Country</th>
              <td>
                {{ caps(contactItem.home_country) }}
              </td>
            </tr>
          </table>
        </div>
      </v-responsive>
    </v-responsive>
  </div>
</template>

<script>
export default {
  props: ["contactItem"],
  data() {
    return {
      add_other_contact_info: false,
      contact_info: false,
      errors: []
    };
  },

  methods: {
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, c => c.toUpperCase());
      }
    },

    close_contact_info() {
      this.contact_info = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },

    can(item) {
      return true;
    },

    save_contact_info() {
      let payload = {
        ...this.contactItem,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.contactItem.id
      };
      console.log(payload);
      this.$axios
        .post(`employee/update/contact`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.close_contact_info();
          }
        })
        .catch(e => console.log(e));
    }
  }
};
</script>

<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #fbfdff;
}
</style>
