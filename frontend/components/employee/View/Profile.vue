<template>
  <v-card flat>
    <v-card-text>
      <v-dialog v-model="dialogCropping" width="500">
        <v-card style="padding-top: 20px">
          <v-card-text>
            <!-- <img :src="imageUrl" alt="Preview Image" /> -->
            <!-- Cropping image step1 -->
            <VueCropper
              v-show="selectedFile"
              ref="cropper"
              :src="selectedFile"
              alt="Source Image"
              :aspectRatio="1"
              :autoCropArea="0.9"
              :viewMode="3"
            ></VueCropper>

            <!-- <div class="cropper-preview"></div> -->
          </v-card-text>

          <v-card-actions>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
              <v-btn
                class="danger btn btn-danger text-left"
                text
                @click="dialogCropping = false"
                style="float: left"
                >Cancel</v-btn
              >
            </div>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
              <v-btn
                class="primary btn btn-danger text-right"
                @click="saveCroppedImageStep2(), (dialog = false)"
                >Crop</v-btn
              >
            </div>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-row class="d-flex align-center">
        <v-col cols="4">
          <v-card class="pa-4 text-center" flat>
            <v-avatar size="120" @click="onpick_attachment">
              <img
                :src="previewImage || '/no-profile-image.jpg'"
                alt="Profile Image"
              />
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
            </v-avatar>

            <v-list dense class="mt-3">
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold"
                    >{{ employeeObject.title }}.
                    {{ employeeObject.full_name }}</v-list-item-title
                  >
                  <v-list-item-subtitle
                    >Position:
                    {{
                      employeeObject?.designation?.name ?? "---"
                    }}</v-list-item-subtitle
                  >
                  <v-list-item-subtitle
                    >Reporting To:
                    {{
                      employeeObject?.reporting_manager?.first_name ?? "---"
                    }}</v-list-item-subtitle
                  >
                </v-list-item-content>
              </v-list-item> </v-list
            ><v-divider></v-divider>
          </v-card>
        </v-col>
        <v-col>
          <div class="text-right">
            <v-icon small color="primary" @click="editForm = !editForm"
              >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
            >
          </div>
          <v-simple-table dense flat class="my-simple-table">
            <tbody>
              <tr>
                <td style="width: 200px">Full Name</td>
                <td>
                  <span v-if="!editForm">{{ employee.full_name }}</span>
                  <v-text-field
                    v-else
                    autofocus
                    :readonly="!editForm"
                    style="border-bottom: 1px solid #eaeaea"
                    class="small-input-font"
                    dense
                    v-model="employee.full_name"
                    color="primary"
                    :hide-details="!errors.full_name"
                    :error-messages="
                      errors && errors.full_name ? errors.full_name[0] : ''
                    "
                  />
                </td>
              </tr>
              <tr>
                <td style="width: 200px">Display Name</td>
                <td>
                  <span v-if="!editForm">{{ employee.display_name }}</span>
                  <v-text-field
                    v-else
                    :readonly="!editForm"
                    style="border-bottom: 1px solid #eaeaea"
                    class="small-input-font"
                    dense
                    v-model="employee.display_name"
                    color="primary"
                    :hide-details="!errors.display_name"
                    :error-messages="
                      errors && errors.display_name
                        ? errors.display_name[0]
                        : ''
                    "
                  />
                </td>
              </tr>
              <tr>
                <td style="width: 200px">First Name</td>
                <td>
                  <span v-if="!editForm">{{ employee.first_name }}</span>
                  <v-text-field
                    v-else
                    :readonly="!editForm"
                    style="border-bottom: 1px solid #eaeaea"
                    class="small-input-font"
                    dense
                    v-model="employee.first_name"
                    color="primary"
                    :hide-details="!errors.first_name"
                    :error-messages="
                      errors && errors.first_name ? errors.first_name[0] : ''
                    "
                  />
                </td>
              </tr>
              <tr>
                <td style="width: 200px">Last Name</td>
                <td>
                  <span v-if="!editForm">{{ employee.last_name }}</span>
                  <v-text-field
                    v-else
                    :readonly="!editForm"
                    style="border-bottom: 1px solid #eaeaea"
                    class="small-input-font"
                    dense
                    v-model="employee.last_name"
                    color="primary"
                    :hide-details="!errors.last_name"
                    :error-messages="
                      errors && errors.last_name ? errors.last_name[0] : ''
                    "
                  />
                </td>
              </tr>
              <tr>
                <td style="width: 200px">Employee Id</td>
                <td>
                  <span v-if="!editForm">{{ employee.employee_id }}</span>
                  <v-text-field
                    v-else
                    :readonly="!editForm"
                    style="border-bottom: 1px solid #eaeaea"
                    class="small-input-font"
                    dense
                    v-model="employee.employee_id"
                    color="primary"
                    :hide-details="!errors.employee_id"
                    :error-messages="
                      errors && errors.employee_id ? errors.employee_id[0] : ''
                    "
                  />
                </td>
              </tr>
              <tr>
                <td style="width: 200px">Employee Device Id</td>
                <td>
                  <span v-if="!editForm">{{ employee.system_user_id }}</span>
                  <v-text-field
                    v-else
                    :readonly="!editForm"
                    style="border-bottom: 1px solid #eaeaea"
                    class="small-input-font"
                    dense
                    v-model="employee.system_user_id"
                    color="primary"
                    :hide-details="!errors.system_user_id"
                    :error-messages="
                      errors && errors.system_user_id
                        ? errors.system_user_id[0]
                        : ''
                    "
                  />
                </td>
              </tr>

              <tr v-if="isCompany">
                <td style="width: 200px">Branch</td>
                <td>
                  <v-autocomplete
                    :append-icon="!editForm ? '' : 'mdi-menu-down'"
                    :items="branchesList"
                    item-value="id"
                    item-text="branch_name"
                    :readonly="!editForm"
                    class="small-input-font"
                    style="border-bottom: 1px solid #eaeaea"
                    dense
                    v-model="employee.branch_id"
                    color="primary"
                    :hide-details="!errors.branch_id"
                    :error-messages="
                      errors && errors.branch_id ? errors.branch_id[0] : ''
                    "
                    @change="getDepartments()"
                  />
                </td>
              </tr>

              <tr>
                <td style="width: 200px">Department</td>
                <td>
                  <v-autocomplete
                    :append-icon="!editForm ? '' : 'mdi-menu-down'"
                    :items="departments"
                    item-text="name"
                    item-value="id"
                    :readonly="!editForm"
                    class="small-input-font"
                    style="border-bottom: 1px solid #eaeaea"
                    dense
                    v-model="employee.department_id"
                    color="primary"
                    :hide-details="!errors.department_id"
                    :error-messages="
                      errors && errors.department_id
                        ? errors.department_id[0]
                        : ''
                    "
                  />
                </td>
              </tr>

              <tr>
                <td style="width: 200px">Sub Department</td>
                <td>
                  <v-autocomplete
                    :append-icon="!editForm ? '' : 'mdi-menu-down'"
                    :items="sub_departments"
                    item-text="name"
                    item-value="id"
                    :readonly="!editForm"
                    class="small-input-font"
                    style="border-bottom: 1px solid #eaeaea"
                    dense
                    v-model="employee.sub_department_id"
                    color="primary"
                    :hide-details="!errors.sub_department_id"
                    :error-messages="
                      errors && errors.sub_department_id
                        ? errors.sub_department_id[0]
                        : ''
                    "
                  />
                </td>
              </tr>

              <tr>
                <td style="width: 200px">Position</td>
                <td>
                  <v-autocomplete
                    :append-icon="!editForm ? '' : 'mdi-menu-down'"
                    :items="designations"
                    item-text="name"
                    item-value="id"
                    :readonly="!editForm"
                    class="small-input-font"
                    style="border-bottom: 1px solid #eaeaea"
                    dense
                    v-model="employee.designation_id"
                    color="primary"
                    :hide-details="!errors.designation_id"
                    :error-messages="
                      errors && errors.designation_id
                        ? errors.designation_id[0]
                        : ''
                    "
                  />
                </td>
              </tr>
            </tbody>
          </v-simple-table>
        </v-col>
      </v-row>
    </v-card-text>
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
        v-if="can('employee_edit')"
        :disabled="!editForm"
        x-small
        class="primary"
        :loading="loading"
        @click="store_data"
        >Save</v-btn
      >
    </v-card-actions>
  </v-card>
</template>
<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
export default {
  components: {
    VueCropper,
  },
  props: ["employeeId", "viewDialog", "employeeObject"],
  data: () => ({
    editForm: false,
    image: "",
    leave_managers: [],
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    selectedFile: "",
    upload_edit: {
      name: "",
    },

    attrs: [],
    dialog: false,
    editDialog: false,
    tab: null,
    m: false,
    expand: false,
    expand2: false,
    boilerplate: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,

    on: "",
    color: "background",
    files: "",
    Model: "Employee",
    endpoint: "employee",
    search: "",
    loading: false,
    total: 0,
    next_page_url: "",
    prev_page_url: "",
    current_page: 1,
    per_page: 8,
    response: "",
    ListName: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    employee: {
      title: "",
      display_name: "",
      employee_id: "",
      system_user_id: "",
      profile_picture: "",
      //employee_role_id: "",
      leave_group_id: "",
      reporting_manager_id: "",
    },
    upload: {
      name: "",
    },
    previewImage: null,
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    editedIndex: -1,
    response: "",
    departments: [],
    sub_departments: [],
    designations: [],
    leave_groups: [],
    data: [],
    errors: [],
    departments: [],
    department_id: "",
    filterBranchId: null,
    branchesList: [],
    isCompany: true,
  }),

  async created() {
    this.getInfo(this.employeeId);

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.employee.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }

    try {
      const { data } = await this.$axios.get(`branches_list`, {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company_id,
        },
      });
      this.branchesList = data;
    } catch (error) {
      // Handle the error
      console.error("Error fetching branch list", error);
    }
  },
  mounted() {
    //this.getDataFromApi();
  },
  watch: {
    dialog(val) {
      val || this.close();
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  computed: {
    formatPassword() {
      let user = this.employeeObject.user;
      if (!user.email || user.email !== "---") {
        return "********";
      }
      return null;
    },
  },
  methods: {
    getInfo(id) {
      this.$axios
        .get(`employee-single/${id}`)
        .then(({ data }) => {
          this.employee = {
            title: data.title,
            display_name: data.display_name,
            full_name: data.full_name,
            first_name: data.first_name,
            last_name: data.last_name,
            employee_id: data.employee_id,
            system_user_id: data.system_user_id,
            department_id: data.department_id,
            sub_department_id: data.sub_department_id,
            designation_id: data.designation_id,
            leave_group_id: data.leave_group_id,
            reporting_manager_id: data.reporting_manager_id,
            branch_id: data.branch_id,
          };
          this.previewImage = data.profile_picture;
          this.getDepartments();
          this.getSubDepartments();
          this.getDesignations();
        })
        .catch((err) => console.log(err));
    },
    getDesignations() {
      this.$axios
        .get(`designation`, {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
            //department_ids: this.$auth.user.assignedDepartments,
          },
        })
        .then(({ data }) => {
          this.designations = data.data;
        })
        .catch((err) => console.log(err));
    },
    getDepartments() {
      this.$axios
        .get(`departments`, {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
            branch_id: this.employee.branch_id,
          },
        })
        .then(({ data }) => {
          this.departments = data.data;
        });
    },
    getSubDepartments() {
      this.$axios
        .get(`sub-departments`, {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
            department_id: this.employee.department_id,
          },
        })
        .then(({ data }) => {
          this.sub_departments = data.data;
        });
    },
    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;

      this.dialogCropping = false;
    },
    can() {
      return true;
    },
    close() {
      this.dialog = false;
      this.$emit("close-popup");
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["profile_picture"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          //croppedimage step6
          // this.previewImage = e.target.result;

          this.selectedFile = event.target.result;

          this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        this.dialogCropping = true;
      }
    },
    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        if (obj[x]) {
          employee.append(x, obj[x]);
        }
      }

      employee.append("company_id", this.$auth.user.company_id);

      return employee;
    },

    store_data() {
      console.log(this.employee);
      let final = Object.assign(this.employee);
      let employee = this.mapper(final);

      //croppedimageStep3
      if (this.$refs.attachment_input.files[0]) {
        this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          // Create a FormData object and append the Blob as a file
          //const formData = new FormData();
          employee.append("profile_picture", blob, "cropped_image.jpg");
          employee.append("attachment_input", blob, "cropped_image.jpg");

          //croppedimagesptep4 //push to API in blob method only
          this.saveToAPI(employee);
        }, "image/jpeg");
      } else {
        employee.delete("profile_picture");
        this.saveToAPI(employee);
      }
    },
    saveToAPI(employee) {
      this.$axios
        .post(`/employee-update/${this.employeeId}`, employee)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees Updated successfully";
            this.$emit("eventFromchild");
            setTimeout(() => {
              this.$emit("close-popup");
            }, 1000);

            //this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
