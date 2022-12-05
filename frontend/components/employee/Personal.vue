<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="personal_info" max-width="700px">
      <v-card>
        <v-card-actions>
          <span class="headline">Personal Info </span>
        </v-card-actions>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("nationality")
                  }}</label>
                  <input
                    v-model="personalItem.nationality"
                    class="form-control"
                    type=""
                  />
                  <span
                    v-if="errors && errors.nationality"
                    class="text-danger mt-2"
                    >{{ errors.nationality[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("Date of birth")
                  }}</label>
                  <input
                    min="0"
                    v-model="personalItem.date_of_birth"
                    class="form-control"
                    type="date"
                  />
                  <span
                    v-if="errors && errors.date_of_birth"
                    class="text-danger mt-2"
                    >{{ errors.date_of_birth[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("religion") }}</label>
                  <input
                    v-model="personalItem.religion"
                    class="form-control"
                    type=""
                  />
                  <span
                    v-if="errors && errors.religion"
                    class="text-danger mt-2"
                    >{{ errors.religion[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("marital status")
                  }}</label>
                  <select
                    v-model="personalItem.marital_status"
                    class="form-control"
                    aria-label="Default select"
                  >
                    <option value="">select...</option>
                    <option value="1">Married</option>
                    <option value="2">Single</option>
                  </select>
                  <span
                    v-if="errors && errors.marital_status"
                    class="text-danger mt-2"
                    >{{ errors.marital_status[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("Employment of Spouse")
                  }}</label>
                  <input
                    min="0"
                    v-model="personalItem.no_of_spouse"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.no_of_spouse"
                    class="text-danger mt-2"
                    >{{ errors.no_of_spouse[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("no of children")
                  }}</label>
                  <input
                    min="0"
                    v-model="personalItem.no_of_children"
                    class="form-control"
                    type="number"
                  />
                  <span
                    v-if="errors && errors.no_of_children"
                    class="text-danger mt-2"
                    >{{ errors.no_of_children[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("father name")
                  }}</label>
                  <input
                    min="0"
                    v-model="personalItem.father_name"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.father_name"
                    class="text-danger mt-2"
                    >{{ errors.father_name[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("mother name")
                  }}</label>
                  <input
                    min="0"
                    v-model="personalItem.mother_name"
                    class="form-control"
                    type="text"
                  />
                  <span
                    v-if="errors && errors.mother_name"
                    class="text-danger mt-2"
                    >{{ errors.mother_name[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("gender") }}</label>
                  <select
                    v-model="personalItem.gender"
                    class="form-select"
                    aria-label="Default select"
                  >
                    <option value="">Select...</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
                  </select>
                  <span
                    v-if="errors && errors.gender"
                    class="text-danger mt-2"
                    >{{ errors.gender[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="12">
                <a
                  href="javascrip:void(0)"
                  @click="add_other_personal_info = !add_other_personal_info"
                  >{{
                    caps(
                      `${add_other_personal_info ? "hide" : "show"} other field`
                    )
                  }}</a
                >
              </v-col>
              <v-row v-if="add_other_personal_info">
                <v-col cols="6">
                  <div class="form-group">
                    <label class="col-form-label">{{
                      caps("other text")
                    }}</label>
                    <input
                      v-model="personalItem.other_text"
                      class="form-control"
                    />
                    <span
                      v-if="errors && errors.other_text"
                      class="text-danger"
                      >{{ errors.other_text[0] }}</span
                    >
                  </div>
                </v-col>

                <v-col cols="6">
                  <div class="form-group">
                    <label class="col-form-label">{{
                      caps("other value")
                    }}</label>
                    <input
                      v-model="personalItem.other_value"
                      class="form-control"
                    />
                    <span
                      v-if="errors && errors.other_value"
                      class="text-danger mt-2"
                      >{{ errors.other_value[0] }}</span
                    >
                  </div>
                </v-col>
              </v-row>
              <span v-if="errors && errors.length" class="error--text">{{
                errors
              }}</span>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn class="error" small @click="close_personal_info">
            Cancel
          </v-btn>
          <v-btn class="primary" small @click="save_personal_info">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <table>
      <!-- <tr>
        <th></th>
        <td style="text-align: right;">
          <v-icon
            v-if="can(`employee_personal_edit_access`)"
            @click="personal_info = true"
            small
            class="grey"
            style="border-radius: 50%; padding: 5px"
            color="secondary"
            >mdi-pencil</v-icon
          >
        </td>
      </tr> -->
      <tr>
        <th>Nationality</th>
        <td>
          {{ caps(personalItem.nationality || "---") }}
        </td>
        <td style="text-align: right;">
          <v-icon
            v-if="can(`employee_personal_edit_access`)"
            @click="personal_info = true"
            small
            class="grey"
            style="border-radius: 50%; padding: 5px"
            color="secondary"
            >mdi-pencil</v-icon
          >
        </td>
      </tr>
      <tr>
        <th>Religion</th>
        <td>
          {{ caps(personalItem.religion || "---") }}
        </td>
      </tr>
      <tr>
        <th>Marital Status</th>
        <td>
          {{
            personalItem.marital_status
              ? personalItem.marital_status == 1
                ? "Married"
                : "Single"
              : "---"
          }}
        </td>
      </tr>
      <tr>
        <th>Date of Birth</th>
        <td>
          {{ personalItem.date_of_birth || "---" }}
        </td>
      </tr>

      <tr>
        <th>No. of Children</th>
        <td>
          {{ personalItem.no_of_children || "---" }}
        </td>
      </tr>

      <tr>
        <th>Father Name</th>
        <td>
          {{ caps(personalItem.father_name || "---") }}
        </td>
      </tr>
      <tr>
        <th>Gender</th>
        <td>
          <span v-if="personalItem.gender == 1">
            Male
          </span>
          <span v-else-if="personalItem.gender == 2">
            Female
          </span>
          <span v-else>
            ---
          </span>
        </td>
      </tr>
      <tr>
        <th>Mother Name</th>
        <td>
          {{ caps(personalItem.mother_name || "---") }}
        </td>
      </tr>
      <tr>
        <th>Employment of Spouse</th>
        <td>
          {{ caps(personalItem.no_of_spouse || "---") }}
        </td>
      </tr>
      <tr>
        <th>{{ caps(personalItem.other_text || "---") }}</th>
        <td>
          {{ caps(personalItem.other_value || "---") }}
        </td>
      </tr>
    </table>
  </div>
</template>

<script>
export default {
  props: ["personalItem"],
  data() {
    return {
      personal_info: false,
      add_other_personal_info: false,
      snackbar: false,
      response: "",
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
    can(item) {
      return true;
    },
    close_personal_info() {
      this.personal_info = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },
    save_personal_info() {
      let payload = {
        ...this.personalItem,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.personalItem.employee_id
      };

      this.$axios
        .post(`personalinfo`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.close_personal_info();
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
