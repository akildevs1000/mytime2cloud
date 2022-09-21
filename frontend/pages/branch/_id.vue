<template>
  <div v-if="can(`branch_edit_access`)">
    <v-stepper v-model="e1">
      <v-stepper-header>
        <v-stepper-step :complete="e1 > 1" step="1">
          Branch Info
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step :complete="e1 > 2" step="2">
          Contact Info
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step step="3"> Login Info </v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="1">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Branch Name</label>
                <span class="text-danger">*</span>
                <input
                  v-model="branch_payload.name"
                  class="form-control"
                  type=""
                />
                <span v-if="errors && errors.name" class="text-danger mt-2">{{
                  errors.name[0]
                }}</span>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Branch Logo</label>
                <br />

                <v-btn
                  style="width: 33%"
                  small
                  class="form-control primary accent--text"
                  @click="onpick_attachment"
                  >{{ !branch_payload.logo ? "Upload Logo" : "File Uploaded" }}
                  <v-icon right dark>mdi-cloud-upload</v-icon></v-btn
                >

                <input
                  required
                  type="file"
                  @change="attachment"
                  style="display: none"
                  accept="image/*"
                  ref="attachment_input"
                />

                <span v-if="errors && errors.logo" class="text-danger mt-2">{{
                  errors.logo[0]
                }}</span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Member From </label>
                <span class="text-danger">*</span>
                <input
                  v-model="branch_payload.member_from"
                  class="form-control"
                  type="date"
                />
                <span
                  v-if="errors && errors.member_from"
                  class="text-danger mt-2"
                  >{{ errors.member_from[0] }}</span
                >
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Expiry Date </label>
                <span class="text-danger">*</span>
                <input
                  v-model="branch_payload.expiry"
                  type="date"
                  class="form-control"
                />
                <span v-if="errors && errors.expiry" class="text-danger mt-2">{{
                  errors.expiry[0]
                }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label"
                  >Max Employees <span class="text-danger">*</span></label
                >
                <input
                  v-model="branch_payload.max_employee"
                  type="number"
                  class="form-control"
                />
                <span
                  v-if="errors && errors.max_employee"
                  class="text-danger mt-2"
                  >{{ errors.max_employee[0] }}</span
                >
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label"
                  >Max Devices <span class="text-danger">*</span></label
                >
                <input
                  v-model="branch_payload.max_devices"
                  type="number"
                  class="form-control"
                />
                <span
                  v-if="errors && errors.max_devices"
                  class="text-danger mt-2"
                  >{{ errors.max_devices[0] }}</span
                >
              </div>
            </div>

            <div class="col-sm-12">
              <div class="form-group">
                <label class="col-form-label">Location </label>
                <span class="text-danger">*</span>
                <textarea
                  v-model="branch_payload.location"
                  id=""
                  cols="30"
                  rows="3"
                  class="form-control"
                ></textarea>
                <span
                  v-if="errors && errors.location"
                  class="text-danger mt-2"
                  >{{ errors.location[0] }}</span
                >
              </div>
            </div>
          </div>
          <v-row>
            <v-col cols="12">
              <div class="text-right">
                <v-btn
                  branch_edit_access
                  small
                  :loading="loading"
                  color="primary"
                  @click="validate_branch"
                >
                  Next
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="2">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Contact Person Name </label>
                <span class="text-danger">*</span>
                <input
                  v-model="contact_payload.contact_name"
                  class="form-control"
                  type="text"
                />
                <span
                  v-if="errors && errors.contact_name"
                  class="text-danger mt-2"
                  >{{ errors.contact_name[0] }}</span
                >
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Contact Person Number </label>
                <span class="text-danger">*</span>
                <input
                  v-model="contact_payload.contact_no"
                  class="form-control"
                  type="number"
                />
                <span
                  v-if="errors && errors.contact_no"
                  class="text-danger mt-2"
                  >{{ errors.contact_no[0] }}</span
                >
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Contact Person Position </label>
                <span class="text-danger">*</span>
                <input
                  v-model="contact_payload.contact_position"
                  class="form-control"
                  type="text"
                />
                <span
                  v-if="errors && errors.contact_position"
                  class="text-danger mt-2"
                  >{{ errors.contact_position[0] }}</span
                >
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label">Contact Person Whatsapp </label>
                <span class="text-danger">*</span>
                <input
                  v-model="contact_payload.contact_whatsapp"
                  class="form-control"
                  type="number"
                />
                <span
                  v-if="errors && errors.contact_whatsapp"
                  class="text-danger mt-2"
                  >{{ errors.contact_whatsapp[0] }}</span
                >
              </div>
            </div>
          </div>

          <v-row>
            <v-col cols="12">
              <div class="text-right">
                <v-btn small color="secondary" @click="e1 = 1"> Back </v-btn>
                <v-btn
                  small
                  :loading="loading"
                  color="primary"
                  @click="validate_contact"
                >
                  Next
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="3">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label"></label>
                Name <span class="text-danger">*</span>
                <input
                  v-model="login_payload.user_name"
                  class="form-control"
                  type=""
                />
              </div>
              <span
                v-if="errors && errors.user_name"
                class="text-danger mt-2"
                >{{ errors.user_name[0] }}</span
              >
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label"></label>
                Email <span class="text-danger">*</span>
                <input
                  v-model="login_payload.email"
                  class="form-control"
                  type="email"
                />
              </div>
              <span v-if="errors && errors.email" class="text-danger mt-2">{{
                errors.email[0]
              }}</span>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label"
                  >Password <span class="text-danger">*</span></label
                >
                <input
                  v-model="login_payload.password"
                  class="form-control"
                  type="password"
                />
                <span
                  v-if="errors && errors.password"
                  class="text-danger mt-2"
                  >{{ errors.password[0] }}</span
                >
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="col-form-label"
                  >Confirm Password <span class="text-danger">*</span></label
                >
                <input
                  v-model="login_payload.password_confirmation"
                  class="form-control"
                  type="password"
                />
                <span
                  v-if="errors && errors.password_confirmation"
                  class="text-danger mt-2"
                  >{{ errors.password_confirmation[0] }}</span
                >
              </div>
            </div>
          </div>
          <v-row>
            <v-col cols="12">
              <div class="text-right">
                <v-btn small color="secondary" @click="e1 = 2"> Back </v-btn>
                <v-btn
                  small
                  :loading="loading"
                  color="primary"
                  @click="validate_user"
                >
                  Next
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </div>
</template>

<script>
export default {
  data: () => ({
    loading: false,
    branch_payload: {
      name: "",
      logo: "",
      location: "",
      member_from: "",
      expiry: "",
      max_employee: "",
      max_devices: "",
    },
    contact_payload: {
      contact_name: "",
      contact_no: "",
      contact_position: "",
      contact_whatsapp: "",
    },
    login_payload: {
      user_name: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
    e1: 1,
    errors: [],
  }),
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.branch_payload.logo = e.target.files[0] || "";
    },
    validate_branch() {
      this.loading = true;
      this.errors = [];

      let payload = new FormData();
      payload.append("name", this.branch_payload.name);
      payload.append("logo", this.branch_payload.logo);
      payload.append("location", this.branch_payload.location);
      payload.append("member_from", this.branch_payload.member_from);
      payload.append("expiry", this.branch_payload.expiry);
      payload.append("max_employee", this.branch_payload.max_employee);
      payload.append("max_devices", this.branch_payload.max_devices);
      payload.append("company_id", this.$route.params.id);

      this.$axios
        .post("/branch/validate", payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.e1 = 2;
          }
        })
        .catch((e) => console.log(e));
    },
    validate_contact() {
      this.loading = true;
      this.errors = [];

      this.$axios
        .post("branch/contact/validate", this.contact_payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.e1 = 3;
          }
        })
        .catch((e) => console.log(e));
    },
    validate_user() {
      this.loading = true;
      this.errors = [];

      this.$axios
        .post("branch/user/validate", this.login_payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.store_data();
          }
        })
        .catch((e) => console.log(e));
    },

    store_data() {
      let payload = new FormData();

      payload.append("name", this.branch_payload.name);
      payload.append("logo", this.branch_payload.logo);
      payload.append("location", this.branch_payload.location);
      payload.append("member_from", this.branch_payload.member_from);
      payload.append("expiry", this.branch_payload.expiry);
      payload.append("max_employee", this.branch_payload.max_employee);
      payload.append("max_devices", this.branch_payload.max_devices);

      payload.append("contact_name", this.contact_payload.contact_name);
      payload.append("contact_no", this.contact_payload.contact_no);
      payload.append("contact_position", this.contact_payload.contact_position);
      payload.append("contact_whatsapp", this.contact_payload.contact_whatsapp);

      payload.append("user_name", this.login_payload.user_name);
      payload.append("email", this.login_payload.email);
      payload.append("password", this.login_payload.password);

      payload.append("company_id", this.$route.params.id);

      this.$axios
        .post("/branch", payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.$router.push(`/companies/details/${this.$route.params.id}`);
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
