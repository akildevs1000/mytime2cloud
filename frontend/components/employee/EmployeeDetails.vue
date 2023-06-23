<template>
  <v-card>
    <v-toolbar class="background" dense dark
      >Profile Details
      <v-spacer></v-spacer>
      <v-btn small class="primary"
        >Documents&nbsp;<v-icon small>
          mdi-file
          <!-- mdi-open-in-new -->
        </v-icon></v-btn
      >
    </v-toolbar>

    <v-card-text>
      <v-row class="pt-5">
        <v-col cols="3">
          <div class="mt-5" style="margin: 0 auto; width: 200px">
            <v-img
              style="
                width: 100%;
                height: 200px;
                border: 1px solid #5fafa3;
                border-radius: 50%;
                margin: 0 auto;
              "
              :src="previewImage || '/no-profile-image.jpg'"
            ></v-img>
            <br />
            <div class="text-center">
              <strong>{{ employeeObject.full_name }}</strong>
            </div>
            <div class="text-center">
              <strong>Employee ID: {{ employeeObject.employee_id }}</strong>
            </div>
            <div class="text-center">
              <span v-html="formatJoiningDate"></span>
            </div>
          </div>
        </v-col>
        <v-col cols="2">
          <table>
            <tr>
              <td>
                <strong>Display Name </strong><br />{{
                  employeeObject.display_name
                }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Department </strong><br />{{
                  employeeObject.department.name
                }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Sub Department </strong><br />{{
                  employeeObject.sub_department.name
                }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Designation </strong><br />{{
                  employeeObject.designation.name
                }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Role </strong><br />{{
                  employeeObject?.user?.role?.name || "---"
                }}
              </td>
            </tr>
          </table>
        </v-col>
        <v-col cols="2">
          <table>
            <tr>
              <td>
                <strong>Contact</strong><br />
                {{ employeeObject.phone_number || "---" }}
                <br />
                {{ employeeObject.local_email || "---" }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Address</strong><br />{{
                  employeeObject.local_address || "---"
                }}
                <br />
                {{ employeeObject.local_city || "---" }},
                {{ employeeObject.local_country || "---" }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Qualification</strong><br />{{
                  employeeObject.qualification.certificate
                }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Bank</strong>
                <br />
                {{ employeeObject.bank.bank_name }}
                <br />
                {{ employeeObject.bank.address }}
                <br />
                {{ employeeObject.bank.account_no }}
              </td>
            </tr>
          </table>
        </v-col>
        <v-col cols="3" style="max-width: 20%">
          <table>
            <tr>
              <td>
                <strong>Timezone</strong><br />{{
                  employeeObject.timezone.name
                }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Shift</strong><br />Straight Shift <br />10:00 to 20:00
              </td>
            </tr>
            <tr>
              <td>
                <strong
                  >Passport :
                  {{
                    (employeeObject.passport &&
                      employeeObject.passport.country) ||
                    "---"
                  }} </strong
                ><br />
                {{
                  (employeeObject.passport &&
                    employeeObject.passport.passport_no) ||
                  "---"
                }}<br />Expired on
                {{ formatDate(employeeObject.passport.expiry_date) }}
              </td>
            </tr>
            <tr>
              <td>
                <strong>Emirates ID </strong><br />{{
                  employeeObject.emirate.emirate_id
                }}<br />Expired on
                {{ formatDate(employeeObject.emirate.expiry) }}
              </td>
            </tr>
          </table>
        </v-col>
        <v-col cols="2">
          <table>
            <tr>
              <td>
                <div style="display: flex; align-items: center; height: 30px">
                  <strong style="width: 70px">Web Login</strong>
                  <v-switch disabled v-model="switchValue"></v-switch>
                </div>
                Last Login <br />08 June 2023
              </td>
            </tr>
            <tr>
              <td>
                <div style="display: flex; align-items: center; height: 30px">
                  <strong style="width: 90px">Mobile Login</strong>
                  <v-switch disabled v-model="switchValue"></v-switch>
                </div>
                Last Login <br />08 June 2023
              </td>
            </tr>
            <tr>
              <td>
                <div style="display: flex; align-items: center; height: 30px">
                  <strong style="width: 70px">Overtime</strong>
                  <v-switch disabled v-model="switchValue"></v-switch>
                </div>
              </td>
            </tr>
          </table>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>
<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
export default {
  components: {
    VueCropper,
  },
  props: ["employeeObject"],
  data: () => ({
    switchValue: true,
    image: "",
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
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
      employee_role_id: "",
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
    roles: [],
    data: [],
    errors: [],
    departments: [],
    department_id: "",
    payloadOptions: {},
  }),

  created() {
    this.getInfo(this.employeeId);

    this.payloadOptions = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      },
    };

    this.getDepartments();
    this.getSubDepartments();
    this.getDesignations();
    this.getRoles();

    try {
      let employee_id = this.$route.params.id;
      if (employee_id) {
        this.editItemId(employee_id);
      }
    } catch (error) {}
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
    formatJoiningDate() {
      let dateObj = new Date();

      let { joining_date } = this.employeeObject;

      if (joining_date) {
        dateObj = new Date(joining_date);
      }

      let day = dateObj.getDate();
      let month = dateObj.toLocaleString("default", { month: "long" });
      let year = dateObj.getFullYear();
      let daySuffix = this.setDaySuffix(day);
      return `<p>${day}<sup>${daySuffix}</sup> ${month} ${year}</p>`;
    },
  },
  methods: {
    formatDate(date) {
      let dateObj = new Date();

      if (date) {
        dateObj = new Date(date);
      }

      let day = dateObj.getDate();
      let month = dateObj.getMonth() + 1;
      let year = dateObj.getFullYear();
      return `${day}/${month}/${year}`;
    },
    setDaySuffix(day) {
      switch (day) {
        case 1:
        case 21:
        case 31:
          return "st";
          break;
        case 2:
        case 22:
          return "nd";
          break;
        case 3:
        case 23:
          return "rd";
          break;
        default:
          return "th";
          break;
      }
    },
    getDepartments() {
      this.$axios.get(`departments`, this.payloadOptions).then(({ data }) => {
        this.departments = data.data;
      });
    },
    getSubDepartments() {
      this.$axios
        .get(`sub-departments`, this.payloadOptions)
        .then(({ data }) => {
          this.sub_departments = data.data;
        });
    },
    getDesignations() {
      this.$axios.get(`designation`, this.payloadOptions).then(({ data }) => {
        this.designations = data.data;
      });
    },
    getRoles() {
      this.payloadOptions.params.role_type = "employee";

      this.$axios.get(`role`, this.payloadOptions).then(({ data }) => {
        this.roles = data.data;
      });
    },
    getInfo(id) {
      this.$axios
        .get(`employee-single/${id}`)
        .then(({ data }) => {
          this.employee = {
            title: data.title,
            display_name: data.display_name,
            first_name: data.first_name,
            last_name: data.last_name,
            employee_id: data.employee_id,
            system_user_id: data.system_user_id,
            department_id: data.department_id,
            sub_department_id: data.sub_department_id,
            designation_id: data.designation_id,
            employee_role_id: data.user.employee_role_id,
          };

          // this.employee.id = data.id;
          this.previewImage = data.profile_picture;
        })
        .catch((err) => console.log(err));
    },
    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;
    },
    can() {
      return true;
    },
    close() {
      this.dialog = false;
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
      }
    },
    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        if (obj[x]) {
          employee.append(x, obj[x]);
        }
      }

      employee.append("company_id", this.$auth.user.company.id);

      return employee;
    },
    store_data() {
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

            //this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
