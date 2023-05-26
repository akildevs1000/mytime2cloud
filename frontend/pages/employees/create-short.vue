<template>
  <div v-if="can('employee_create')">
    <div v-if="!preloader">
      <v-row class="mt-0 mb-3">
        <v-col cols="10">
          <h3>{{ Model }}</h3>
          <div>Dashboard / {{ Model }} / Create</div>
        </v-col>
      </v-row>
      <v-btn color="primary" @click="openDialog">Open Dialog</v-btn>
      <v-dialog v-model="dialogVisible" max-width="900px">
        <v-card>
          <v-toolbar class="background" dark>
            <span class="headline">Create Employee</span>
          </v-toolbar>
          <v-card-title>
            <v-row>
              <v-col md="12"> </v-col>
              <v-col md="6">
                <v-row>
                  <v-col md="12">
                    <label class="col-form-label"
                      >Title <span class="text-danger">*</span></label
                    >
                    <v-select
                      v-model="payload.title"
                      :items="titleItems"
                      :hide-details="!errors.title"
                      :error="errors.title"
                      :error-messages="
                        errors && errors.title ? errors.title[0] : ''
                      "
                      dense
                      outlined
                    ></v-select>
                  </v-col>
                  <v-col md="12">
                    <label class="col-form-label"
                      >Display Name <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.display_name"
                      type="text"
                      v-model="payload.display_name"
                      :error="errors.display_name"
                      :error-messages="
                        errors && errors.display_name
                          ? errors.display_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="12">
                    <label class="col-form-label"
                      >Employee Device ID
                      <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="text"
                      v-model="payload.system_user_id"
                      :hide-details="!errors.system_user_id"
                      :error="errors.system_user_id"
                      :error-messages="
                        errors && errors.system_user_id
                          ? errors.system_user_id[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="12">
                    <label class="col-form-label"
                      >Employee Id<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="text"
                      v-model="payload.employee_id"
                      :hide-details="!errors.employee_id"
                      :error="errors.employee_id"
                      :error-messages="
                        errors && errors.employee_id
                          ? errors.employee_id[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-col>
              <v-col md="6">
                <div style="text-align: center;" class="mt-5">
                  <v-img
                    style="width: 50%; margin: 0 auto; border-radius: 50%;"
                    :src="previewImage || '/no-profile-image.jpg'"
                  ></v-img>
                  <v-btn x-small class="btn primary" @click="onpick_attachment"
                    >{{ !upload.name ? "Upload" : "Change" }} Profile Image
                    <v-icon right dark>mdi-cloud-upload</v-icon>
                  </v-btn>
                  <input
                    required
                    type="file"
                    @change="attachment"
                    style="display: none"
                    accept="image/*"
                    ref="attachment_input"
                  />

                  <span
                    v-if="errors && errors.profile_picture"
                    class="text-danger mt-2"
                    >{{ errors.profile_picture[0] }}</span
                  >
                </div>
              </v-col>
            </v-row>
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12">
                <div class="text-right">
                  <v-btn
                    v-if="can('employee_create')"
                    small
                    :loading="loading"
                    color="primary"
                    @click="validate_employee"
                  >
                    Submit
                  </v-btn>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    dialogVisible: false,
    Model: "Employee",
    preloader: false,
    loading: false,
    show_password: false,
    show_password_confirm: false,
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],

    upload: {
      name: ""
    },
    payload: {
      first_name: "",
      last_name: "",
      display_name: "",
      user_name: "",
      email: "",
      password: "",
      role_id: "",
      password_confirmation: "",
      employee_id: "",
      system_user_id: ""
    },
    contact: {
      phone_number: "",
      whatsapp_number: "",
      phone_relative_number: "",
      relation: ""
    },
    other: {
      designation_id: "",
      department_id: "",
      sub_department_id: "",
      joining_date: "",
      grade: "",
      type: ""
    },
    previewImage: null,
    e1: 1,
    errors: [],
    departments: [],
    designations: [],
    subDepartments: [],
    roles: [],
    Rules: [v => !!v || "This field is required"]
  }),
  created() {
    this.preloader = false;
    this.getDepartments();
    this.getRoles();
  },
  methods: {
    openDialog() {
      this.dialogVisible = true;
    },
    closeDialog() {
      this.dialogVisible = false;
    },
    getRoles() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id,
          role_type: "employee"
        }
      };
      this.$axios.get(`role`, options).then(({ data }) => {
        this.roles = data.data;
      });
    },

    getDepartments() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id
        }
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
      });
    },
    getDesignations(department_id) {
      let options = {
        params: {
          per_page: 100,
          department_id: department_id,
          company_id: this.$auth.user.company.id
        }
      };
      this.$axios
        .get(`designations-by-department`, options)
        .then(({ data }) => {
          this.designations = data;
        });
    },

    getSubdepartments(department_id) {
      let options = {
        params: {
          per_page: 100,
          department_id: department_id,
          company_id: this.$auth.user.company.id
        }
      };
      this.$axios
        .get(`sub-departments-by-department`, options)
        .then(({ data }) => {
          this.subDepartments = data;
        });
    },

    getMultiple(department_id) {
      this.getSubdepartments(department_id);
      this.getDesignations(department_id);
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      console.log("file", file);

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["profile_picture"] = [
          "File too big (> 1MB). Upload less than 1MB"
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = e => {
          this.previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    mapper(obj) {
      let payload = new FormData();

      for (let x in obj) {
        payload.append(x, obj[x]);
      }
      payload.append("profile_picture", this.upload.name);
      payload.append("company_id", this.$auth.user.company.id);

      return payload;
    },
    validate_employee() {
      this.loading = true;
      this.errors = [];

      let payload = this.mapper(this.payload);

      const file = this.$refs.attachment_input.files[0];
      console.log("file", file);
      // if (!file) {
      //   e.preventDefault();
      //   alert("No file chosen");
      //   return;
      // }

      if (file.size > 1024 * 1024) {
        e.preventDefault();
        this.errors["profile_picture"] = [
          "File too big (> 1MB). Upload less than 1MB"
        ];
        return;
      }

      this.$axios
        .post("/employee/validate", payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
            return false;
          }
          this.e1 = 2;
        })
        .catch(e => console.log(e));
    },
    validate_contact() {
      this.loading = true;
      this.errors = [];

      this.$axios
        .post("employee/contact/validate", this.contact)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.e1 = 3;
          }
        })
        .catch(e => console.log(e));
    },
    validate_other() {
      this.loading = true;
      this.errors = [];

      this.$axios
        .post("employee/other/validate", this.other)
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.store_data();
          }
        })
        .catch(e => console.log(e));
    },

    store_data() {
      this.loading = true;
      let final = Object.assign(this.payload, this.contact, this.other);
      let payload = this.mapper(final);

      this.$axios
        .post("/employee", payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.$router.push("/employees");
          }
        })
        .catch(e => console.log(e));
    }
  }
};
</script>

<style scoped>
.v-text-field.v-text-field--enclosed .v-text-field__details {
  padding-top: 0px;
  margin-bottom: 8px;
  display: none;
}
</style>
