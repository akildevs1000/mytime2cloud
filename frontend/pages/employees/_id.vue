<template>
  <div v-if="can('employee_edit')">
    <div v-if="!preloader">
      <div class="text-center ma-2">
        <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
          {{ response }}
        </v-snackbar>
      </div>
      <v-row class="mt-5 mb-10">
        <v-col cols="10">
          <h3>{{ Model }}</h3>
          <div>Dashboard / {{ Model }} / Edit</div>
        </v-col>

        <v-col cols="2">
          <div class="display-1 pa-2 text-right">
            <v-btn small class="primary" :to="`/employees`">
              <v-icon small>mdi-arrow-left</v-icon>&nbsp;Back
            </v-btn>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-card>
            <v-tabs color="primary">
              <v-tab>
                <v-icon left> mdi-account </v-icon>
              </v-tab>
              <v-tab>
                <v-icon left> mdi-phone </v-icon>
              </v-tab>
              <v-tab>
                <v-icon left> mdi-chart-bubble </v-icon>
              </v-tab>

              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <v-row dense>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Title <span class="text-danger">*</span></label>
                        <v-select v-model="payload.title" :items="titleItems" :hide-details="!errors.title"
                          :error="errors.title" :error-messages="errors && errors.title ? errors.title[0] : ''
                            " dense outlined></v-select>
                      </v-col>

                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">File No <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="text" :hide-details="!errors.file_no" v-model="payload.file_no"
                          :error="errors.file_no" :error-messages="errors && errors.file_no ? errors.file_no[0] : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="4" sm="12" cols="12" dense>
                        <label class="col-form-label">Display Name
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined :hide-details="!errors.display_name" type="text"
                          v-model="payload.display_name" :error="errors.display_name" :error-messages="errors && errors.display_name
                            ? errors.display_name[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="4" cols="12" sm="12" dense>
                        <label class="col-form-label">First Name <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="text" v-model="payload.first_name"
                          :hide-details="!errors.first_name" :error="errors.first_name" :error-messages="errors && errors.first_name
                            ? errors.first_name[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="4" cols="12" sm="12" dense>
                        <label class="col-form-label">Last Name <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="text" autocomplete="off" v-model="payload.last_name"
                          :hide-details="!errors.last_name" :error="errors.last_name" :error-messages="errors && errors.last_name
                            ? errors.last_name[0]
                            : ''
                            "></v-text-field>
                      </v-col>

                      <v-col md="6" sm="12" cols="12" dense>
                        <label class="col-form-label">Password <span class="text-danger">*</span></label>
                        <v-text-field dense outlined :hide-details="!errors.password" :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'
                          " :type="show_password ? 'text' : 'password'" v-model="payload.password"
                          class="input-group--focused" @click:append="show_password = !show_password"
                          :error="errors.password" :error-messages="errors && errors.password ? errors.password[0] : ''
                            "></v-text-field>
                      </v-col>

                      <v-col md="6" sm="12" cols="12" dense>
                        <label class="col-form-label">Confirm Password
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined :hide-details="!errors.password_confirmation" :append-icon="show_password_confirm ? 'mdi-eye' : 'mdi-eye-off'
                          " :type="show_password_confirm ? 'text' : 'password'" v-model="payload.password_confirmation"
                          class="input-group--focused" @click:append="
                            show_password_confirm = !show_password_confirm
                            " :error="errors.show_password_confirm" :error-messages="errors && errors.show_password_confirm
    ? errors.show_password_confirm[0]
    : ''
    "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Email <span class="text-danger">*</span></label>
                        <v-text-field dense readonly outlined type="text" v-model="payload.email"
                          :hide-details="!errors.email" :error="errors.email" :error-messages="errors && errors.email ? errors.email[0] : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Roles <span class="text-danger">*</span></label>
                        <v-select dense outlined :rules="Rules" v-model="payload.role_id" :items="roles" item-value="id"
                          item-text="name" :hide-details="!errors.role_id" :error="errors.role_id" :error-messages="errors && errors.role_id ? errors.role_id[0] : ''
                            "></v-select>
                      </v-col>
                    </v-row>
                    <v-row>
                      <div col="6" md="6" style="display: none">
                        <div class="col-sm-3 pt-5">
                          <div class="form-group">
                            <label class="col-form-label">Profile Picture</label>

                            <v-img style="
                                border-radius: 50%;
                                height: 120px;
                                width: 35%;
                                margin: 0 auto;
                              " :src="previewImage ||
                                payload.profile_picture ||
                                '/no-profile-image.jpg'
                                "></v-img>

                            <br />
                            <v-btn text small class="form-control primary" @click="onpick_attachment">{{
                              !upload.name
                              ? "Profile Picture"
                              : "File Uploaded"
                            }}
                              <v-icon right dark>mdi-cloud-upload</v-icon>
                            </v-btn>

                            <input required type="file" @change="attachment" style="display: none" accept="image/*"
                              ref="attachment_input" />

                            <span v-if="errors && errors.profile_picture" class="text-danger mt-2">{{
                              errors.profile_picture[0] }}</span>
                          </div>
                        </div>
                      </div>
                      <div col="6" md="6">
                        <div class="col-sm-3 pt-5">
                          <div class="form-group">
                            <label class="col-form-label">Profile Picture</label>

                            <v-img style="
                                border-radius: 50%;
                                height: 120px;
                                width: 35%;
                                margin: 0 auto;
                              " :src="image_name ||
                                payload.profile_picture ||
                                '/no-profile-image.jpg'
                                "></v-img>

                            <br />
                            <v-btn text small class="form-control primary" @click="$refs.FileInput.click()">{{
                              !upload.name
                              ? "Profile Picture"
                              : "File Uploaded"
                            }}
                              <v-icon right dark>mdi-cloud-upload</v-icon>
                            </v-btn>

                            <input ref="FileInput" type="file" accept="image/jpeg, image/png" style="display: none"
                              @change="onFileSelect" />
                            <span v-if="errors && errors.profile_picture" class="text-danger mt-2">{{
                              errors.profile_picture[0] }}</span>
                          </div>
                        </div>
                      </div>

                      <div col="6" md="6">
                        <div>
                          <v-dialog v-model="dialog" width="500">
                            <v-card style="padding-top: 20px">
                              <v-card-text>
                                <!-- <img :src="imageUrl" alt="Preview Image" /> -->
                                <VueCropper v-show="selectedFile" ref="cropper" :src="selectedFile" alt="Source Image"
                                  :aspectRatio="1" :autoCropArea="0.9" :viewMode="3"></VueCropper>

                                <!-- <div class="cropper-preview"></div> -->
                              </v-card-text>

                              <v-card-actions>
                                <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
                                  <v-btn class="danger btn btn-danger text-left" text @click="closePopup()"
                                    style="float: left">Cancel</v-btn>
                                </div>
                                <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
                                  <v-btn class="primary btn btn-danger text-right"
                                    @click="saveImage(), (dialog = false)">Crop</v-btn>
                                </div>
                              </v-card-actions>
                            </v-card>
                          </v-dialog>
                        </div>
                      </div>
                    </v-row>
                    <v-row>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn v-if="can('employee_edit')" small :loading="loading" color="primary"
                            @click="update_employee">
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
                    <v-row dense>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Phone Number
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="number" v-model="contact.phone_number"
                          :hide-details="!errors.phone_number" :error="errors.phone_number" :error-messages="errors && errors.phone_number
                            ? errors.phone_number[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Whatsapp Number
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="number" v-model="contact.whatsapp_number"
                          :hide-details="!errors.whatsapp_number" :error="errors.whatsapp_number" :error-messages="errors && errors.whatsapp_number
                            ? errors.whatsapp_number[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Relative Number
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="number" v-model="contact.phone_relative_number"
                          :hide-details="!errors.phone_relative_number" :error="errors.phone_relative_number"
                          :error-messages="errors && errors.phone_relative_number
                            ? errors.phone_relative_number[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Relation <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="text" v-model="contact.relation"
                          :hide-details="!errors.relation" :error="errors.relation" :error-messages="errors && errors.relation ? errors.relation[0] : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">City </label>
                        <v-text-field dense outlined type="text" v-model="contact.local_city"
                          :hide-details="!errors.local_city" :error="errors.city" :error-messages="errors && errors.local_city
                            ? errors.local_city[0]
                            : ''
                            "></v-text-field>
                      </v-col>

                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Country</label>
                        <v-text-field dense outlined type="text" v-model="contact.local_country"
                          :hide-details="!errors.local_country" :error="errors.local_country" :error-messages="errors && errors.local_country
                            ? errors.local_country[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="12" cols="12" sm="12" dense>
                        <label class="col-form-label">Address</label>
                        <v-textarea dense outlined type="text" :hide-details="!errors.local_address"
                          v-model="contact.local_address" class="input-group--focused"></v-textarea>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn v-if="can('employee_edit')" small :loading="loading" color="primary"
                            @click="update_contact">
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
                    <v-row dense>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Employee Id <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="text" v-model="other.employee_id"
                          :hide-details="!errors.employee_id" :error="errors.employee_id" :error-messages="errors && errors.employee_id
                            ? errors.employee_id[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Employee Device Id
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="text" v-model="other.system_user_id"
                          :hide-details="!errors.system_user_id" :error="errors.system_user_id" :error-messages="errors && errors.system_user_id
                            ? errors.system_user_id[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Joining Date
                          <span class="text-danger">*</span></label>
                        <v-text-field dense outlined type="date" v-model="other.joining_date"
                          :hide-details="!errors.joining_date" :error="errors.joining_date" :error-messages="errors && errors.joining_date
                            ? errors.joining_date[0]
                            : ''
                            "></v-text-field>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Department <span class="text-danger">*</span></label>
                        <v-select v-model="other.department_id" @change="getMultiple(other.department_id)"
                          :items="departments" dense item-text="name" item-value="id" outlined
                          :hide-details="!errors.department_id" :error="errors.department_id" :error-messages="errors && errors.department_id
                            ? errors.department_id[0]
                            : ''
                            "></v-select>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Designation <span class="text-danger">*</span></label>
                        <v-select v-model="other.designation_id" :items="designations" dense item-text="name"
                          item-value="id" outlined :hide-details="!errors.designation_id" :error="errors.designation_id"
                          :error-messages="errors && errors.designation_id
                            ? errors.designation_id[0]
                            : ''
                            "></v-select>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">sub Departments
                          <span class="text-danger">*</span></label>
                        <v-select v-model="other.sub_department_id" :items="subDepartments" dense item-text="name"
                          item-value="id" outlined :hide-details="!errors.sub_department_id"
                          :error="errors.sub_department_id" :error-messages="errors && errors.sub_department_id
                            ? errors.sub_department_id[0]
                            : ''
                            "></v-select>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Type </label>
                        <v-select v-model="other.type" :items="['limit', 'test']" dense outlined
                          :hide-details="!errors.type" :error="errors.type" :error-messages="errors && errors.type ? errors.type[0] : ''
                            "></v-select>
                      </v-col>
                      <v-col md="6" cols="12" sm="12" dense>
                        <label class="col-form-label">Grade </label>
                        <v-select v-model="other.grade" :items="['A', 'B', 'C']" dense outlined
                          :hide-details="!errors.grade" :error="errors.grade" :error-messages="errors && errors.grade ? errors.grade[0] : ''
                            "></v-select>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn v-if="can('employee_edit')" small :loading="loading" color="primary"
                            @click="update_other">
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
        </v-col>
      </v-row>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
import "cropperjs/dist/cropper.css";
// import Cropper from "cropperjs";
import VueCropper from "vue-cropperjs";
export default {
  layout({ $auth }) {
    let { is_master } = $auth.user;
    return is_master ? "default" : "employee";
  },
  //props: ["image_name"],
  components: { VueCropper },
  data: () => ({
    cropper: "",
    image:
      "https://images.pexels.com/photos/4218687/pexels-photo-4218687.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    preview: "preview",
    mime_type: "",
    cropedImage: "",
    autoCrop: false,
    selectedFile: "",
    image: "",
    dialog: false,
    files: "",
    image_name: "",

    file: "",

    showCropper: false,
    Model: "Employee",
    id: "",
    loading: false,
    preloader: false,
    firstClick: false,
    show_password: false,
    show_password_confirm: false,
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],

    upload: {
      name: "",
    },

    payload: {
      first_name: "",
      last_name: "",
      display_name: "",
      email: "",
      password: "",
      password_confirmation: "",
      role_id: "",
      title: "",
      file_no: "",
    },
    contact: {
      phone_number: "",
      whatsapp_number: "",
      phone_relative_number: "",
      relation: "",
      local_email: "",
      local_address: "",
      local_country: "",
      local_city: "",
    },
    other: {
      designation_id: "",
      department_id: "",
      sub_department_id: "",
      joining_date: "",
      employee_id: "",
      system_user_id: "",
      role_id: "",
      type: "",
      grade: "",
    },
    e1: 1,
    errors: [],
    departments: [],
    designations: [],
    subDepartments: [],
    roles: [],
    previewImage: null,
    Rules: [(v) => !!v || "This field is required"],

    data: {},
    response: "",
    snackbar: false,
  }),

  async created() {
    this.getDataFromApi();
    this.getDepartments();
    this.getRoles();
  },

  computed: {
    currentUserType() {
      return this.$auth.user;
    },
  },
  mounted: function () {

    this.$nextTick(function () {
      setTimeout(() => {
        // const cropper = this.$refs.cropper.$cropper;
        // cropper.on("crop", this.updatePreview);
      }, 5000);
    });
  },
  methods: {
    closePopup() {
      this.$refs.FileInput.value = null;
      this.dialog = false;
    },
    updatePreview() {
      const cropper = this.$refs.cropper.$cropper;
      const previewElement = document.querySelector(".cropper-preview");
      const previewCanvas = cropper.getCroppedCanvas({
        width: 200, // Adjust the width of the preview image
        height: 200, // Adjust the height of the preview image
      });

      // Clear previous preview content
      previewElement.innerHTML = "";

      // Append the new preview image
      previewElement.appendChild(previewCanvas);
    },

    saveImage() {
      const userId = this.$route.params.user_id;
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;
    },
    onFileSelect(e) {
      this.loading = true;

      const file = e.target.files[0];

      if (!file) return;
      this.mime_type = file.type;


      if (!this.uploadFilesizevalidation(e.target.files)) return false;

      if (typeof FileReader === "function") {
        this.dialog = true;
        const reader = new FileReader();
        reader.onload = (event) => {
          this.selectedFile = event.target.result;
          this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file);
        this.loading = false;
      } else {
      }
    },

    handleUploaded(event) {

    },

    getRoles() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id,
          role_type: "employee",
        },
      };
      this.$axios.get(`role`, options).then(({ data }) => {
        this.roles = data.data;
      });
    },
    getDepartments() {
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
        this.getMultiple();
      });
    },
    getDesignations(department_id = this.other.department_id) {
      let options = {
        params: {
          per_page: 100,
          department_id: department_id,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios
        .get(`designations-by-department`, options)
        .then(({ data }) => {
          this.designations = data;
        });
    },

    getSubdepartments(department_id = this.other.department_id) {
      let options = {
        params: {
          per_page: 100,
          department_id: department_id,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios
        .get(`sub-departments-by-department`, options)
        .then(({ data }) => {
          this.subDepartments = data;
        });
    },

    getMultiple(department_id) {
      if (this.firstClick) {
        this.getSubdepartments(department_id);
        this.getDesignations(department_id);
        this.other.designation_id = this.other.sub_department_id = null;
      } else {
        this.getSubdepartments(department_id);
        this.getDesignations(department_id);
        this.firstClick = true;
      }
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    getDataFromApi() {
      this.preloader = true;
      this.id = this.$route.params.id;
      this.$axios
        .get(`employee/${this.id}?company_id=${this.$auth.user.company.id}`)
        .then(({ data }) => {
          this.payload = {
            first_name: data.first_name,
            last_name: data.last_name,
            user_name: data.user.name,
            display_name: data.display_name,
            email: data.user.email,
            role_id: data.role_id,
            profile_picture: data.profile_picture,
            title: data.title,
            file_no: data.file_no == "null" ? "" : data.file_no,
          };

          this.contact = {
            phone_number: data.phone_number,
            whatsapp_number: data.whatsapp_number,
            phone_relative_number: data.phone_relative_number,
            relation: data.relation,

            local_email: data.local_email,
            local_address: data.local_address,
            local_country: data.local_country,
            local_city: data.local_city,
          };

          this.other = {
            designation_id: data.designation_id,
            department_id: data.department_id,
            joining_date: data.edit_joining_date,
            employee_id: data.employee_id,
            system_user_id: data.system_user_id,
            sub_department_id: data.sub_department_id,
            type: data.type,
            grade: data.grade,
          };

          this.preloader = false;
        });
    },

    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    uploadFilesizevalidation(file) {
      if (file) {
        if (file && file[0].size > 1024 * 100) {
          // e.preventDefault();
          //alert("File too big (> 1MB). Upload less than 1MB");

          this.errors["profile_picture"] = [
            "File too big (> 100kb). Upload less than 100Kb",
          ];
          return false;
        } else {
          this.errors["profile_picture"] = "";
          return true;
        }
      }
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;
      this.uploadFilesizevalidation(e.target.files);
      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
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

      return payload;
    },
    update_employee() {
      let payload = new FormData();
      payload.append("first_name", this.payload.first_name);
      payload.append("last_name", this.payload.last_name);
      payload.append("display_name", this.payload.display_name);
      payload.append("email", this.payload.email);
      payload.append("title", this.payload.title);
      payload.append("file_no", this.payload.file_no);
      let file = false;
      if (this.$refs.attachment_input.files[0]) {
        file = this.$refs.attachment_input.files[0];
      } else if (this.$refs.FileInput.files[0]) {

        file = this.$refs.FileInput.files[0];
        this.$refs.attachment_input = this.$refs.FileInput;
        // Convert the cropped canvas to a Blob
        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          // Create a FormData object and append the Blob as a file
          //const formData = new FormData();
          payload.append("profile_picture", blob, "cropped_image.jpg");
          this.updateToserver(file, payload);
          // Make an API call to upload the image
        }, "image/jpeg");
      } else {
        payload.delete("profile_picture");
        this.updateToserver(file, payload);
      }
    },
    updateToserver(file, payload) {

      // if (!file) {
      //   e.preventDefault();
      //   alert("No file chosen");
      //   return;
      // }
      if (file) {
        if (file.size > 1024 * 100) {
          // e.preventDefault();
          // alert("File too big (> 1MB). Upload less than 1MB");
          this.errors["profile_picture"] = [
            "File too big (> 100Kb). Upload less than 100Kb",
          ];
          return;
        } else {
          this.errors["profile_picture"] = "";
        }
      }
      if (this.payload.password) {
        payload.append("password", this.payload.password);
        payload.append(
          "password_confirmation",
          this.payload.password_confirmation
        );
      }
      if (this.upload.name) {
        payload.append("profile_picture", this.upload.name);
      }

      if (this.payload.role_id) {
        payload.append("role_id", this.payload.role_id);
      }

      this.start_process(`/employee/${this.id}/update`, payload, `Employee`);
    },

    update_contact() {
      this.start_process(
        `/employee/${this.id}/update/contact`,
        this.contact,
        `Contact`
      );
    },
    update_other() {
      this.start_process(
        `/employee/${this.id}/update/other`,
        this.other,
        `Other details`
      );
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
<style scoped>
.user {
  width: 140px;
  height: 140px;
  border-radius: 100%;
  border: 3px solid #2e7d32;
  position: relative;
}

.profile-img {
  height: 100%;
  width: 100%;
  border-radius: 50%;
}

.icon {
  position: absolute;
  top: 10px;
  right: 0;
  background: #e2e2e2;
  border-radius: 100%;
  width: 30px;
  height: 30px;
  line-height: 30px;
  vertical-align: middle;
  text-align: center;
  color: #0000ff;
  font-size: 14px;
  cursor: pointer;
}
</style>
