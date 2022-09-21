<template>
  <div v-if="can(`branch_edit_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-10">
      <v-col cols="10">
        <h3>Branch</h3>
        <div>Dashboard / Branch / Edit</div>
      </v-col>
      <v-col cols="2">
        <div class="display-1 pa-2 text-right">
          <v-btn
            small
            class="primary"
            :to="`/companies/details/${branch_payload.company_id}`"
          >
            <v-icon small>mdi-arrow-left</v-icon>&nbsp;Back
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-card>
      <v-tabs color="primary">
        <v-tab>
          <v-icon left> mdi-domain </v-icon>
        </v-tab>
        <v-tab>
          <v-icon left> mdi-account </v-icon>
        </v-tab>
        <v-tab>
          <v-icon left> mdi-lock </v-icon>
        </v-tab>

        <v-tab-item>
          <v-card flat>
            <v-card-text>
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
                    <span
                      v-if="errors && errors.name"
                      class="text-danger mt-2"
                      >{{ errors.name[0] }}</span
                    >
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
                      >{{ !upload.name ? "Upload Logo" : "File Uploaded" }}
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

                    <span
                      v-if="errors && errors.logo"
                      class="text-danger mt-2"
                      >{{ errors.logo[0] }}</span
                    >
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
                    <span
                      v-if="errors && errors.expiry"
                      class="text-danger mt-2"
                      >{{ errors.expiry[0] }}</span
                    >
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
                      small
                      :loading="loading"
                      color="primary"
                      @click="update_branch"
                    >
                      Submit
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card flat>
            <v-card-text>
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
                    <label class="col-form-label"
                      >Contact Person Position
                    </label>
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
                    <label class="col-form-label"
                      >Contact Person Whatsapp
                    </label>
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
                <v-col v-if="can(`branch_edit_access`)" cols="12">
                  <div class="text-right">
                    <v-btn
                      small
                      :loading="loading"
                      color="primary"
                      @click="update_contact"
                    >
                      Submit
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card flat>
            <v-card-text>
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
                  <span
                    v-if="errors && errors.email"
                    class="text-danger mt-2"
                    >{{ errors.email[0] }}</span
                  >
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
                      >Confirm Password
                      <span class="text-danger">*</span></label
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
                    <v-btn
                      small
                      :loading="loading"
                      color="primary"
                      @click="update_user"
                    >
                      Submit
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tab-item>
      </v-tabs>
    </v-card>
  </div>
</template>

<script>
export default {
  data: () => ({
    id: "",
    loading: false,
    upload: {
      name: "",
    },

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
    },
    e1: 1,
    errors: [],
    data: {},
    response: "",
    snackbar: false,
  }),
  async created() {
    this.getDataFromApi();
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    getDataFromApi() {
      this.id = this.$route.params.id;
      this.$axios.get(`branch/${this.id}`).then(({ data }) => {
        let r = data.record;
        this.branch_payload = r;
        this.contact_payload.contact_name = r.contact.name;
        this.contact_payload.contact_no = r.contact.number;
        this.contact_payload.contact_position = r.contact.position;
        this.contact_payload.contact_whatsapp = r.contact.whatsapp;

        this.login_payload.user_name = r.user.name;
        this.login_payload.email = r.user.email;

        let mf = this.formatted_date(r.member_from);
        let exp = this.formatted_date(r.expiry);
        this.branch_payload.member_from = mf;
        this.branch_payload.expiry = exp;
      });
    },

    formatted_date(v) {
      let [year, month, date] = v.split("/");
      return `${year}-${month}-${date}`;
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.upload.name = e.target.files[0] || "";
    },

    update_branch() {
      let payload = new FormData();

      console.log(this.branch_payload);

      payload.append("name", this.branch_payload.name);
      if (this.upload.name) {
        payload.append("logo", this.upload.name);
      }
      payload.append("location", this.branch_payload.location);
      payload.append("member_from", this.branch_payload.member_from);
      payload.append("expiry", this.branch_payload.expiry);
      payload.append("max_employee", this.branch_payload.max_employee);
      payload.append("max_devices", this.branch_payload.max_devices);
      payload.append("company_id", this.branch_payload.company_id);

      this.start_process(`/branch/${this.id}/update`, payload, `branch`);
    },
    update_contact() {
      let payload = this.contact_payload;
      let id = this.id;
      this.start_process(`/branch/${id}/update/contact`, payload, `Contact`);
    },
    update_user() {
      let payload = this.login_payload;
      let id = this.id;
      this.start_process(`/branch/${id}/update/user`, payload, `User`);
    },
    start_process(url, payload, model) {
      this.loading = true;

      this.$axios
        .post(url, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = model + " updated successfully";
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
