<template>
  <v-card flat class="d-flex flex-column">
    <div class="text-right" v-if="can(!editForm ? 'employee_login_edit' : 'employee_login_view')">
      <v-icon small color="primary" @click="editForm = !editForm"
        >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
      >
    </div>
    <v-simple-table v-if="employee && can('employee_login_view')"  dense flat class="my-simple-table">
      <tbody>
        <tr>
          <td style="width: 200px">Email</td>
          <td>
            <span v-if="!editForm">{{ employee.email }}</span>
            <v-text-field
              v-else
              autofocus
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="employee.email"
              color="primary"
              :hide-details="!errors.email"
              :error-messages="errors && errors.email ? errors.email[0] : ''"
            />
          </td>
        </tr>
        <tr>
          <td style="width: 200px">Password</td>
          <td>
            <span v-if="!editForm">{{ employee.password }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="employee.password"
              color="primary"
              :hide-details="!errors.password"
              :error-messages="
                errors && errors.password ? errors.password[0] : ''
              "
            />
          </td>
        </tr>
        <tr>
          <td style="width: 200px">Confirm Password</td>
          <td>
            <span v-if="!editForm">{{ employee.password_confirmation }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="employee.password_confirmation"
              color="primary"
              :hide-details="!errors.password_confirmation"
              :error-messages="
                errors && errors.password_confirmation
                  ? errors.password_confirmation[0]
                  : ''
              "
            />
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
        @click="submit"
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
  props: ["employeeId"],
  data: () => ({
    editForm: false,
    image: "",
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
      email: "",
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
    data: [],
    errors: [],
    departments: [],
    department_id: "",
    roles: [],
    payloadOptions: {},
  }),

  created() {
    this.payloadOptions = {
      params: {
        per_page: 10,
        company_id: this.$auth.user.company_id,
      },
    };
    this.getInfo(this.employeeId);
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
  methods: {
    async getInfo(id) {
      this.loading = true;
      await this.$axios
        .get(`employee-single/${id}`)
        .then(({ data }) => {
          this.employee = data.user;

          if (!data.user.email || data.user.email !== "---") {
            this.employee.password = "********";
            this.employee.password_confirmation = "********";
          }
          this.employee.employee_id = id;
          this.employee.company_id = this.$auth.user.company_id;
          this.loading = false;
        })
        .catch((err) => console.log(err));
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    close() {
      this.dialog = false;
    },
    submit() {
      this.loading = true;
      this.$axios
        .post(`/employee-login-update/${this.employee.id || 0}`, this.employee)
        .then(({ data }) => {
          this.loading = false;

          console.log("employee", this.employee);

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employee Updated successfully";
            this.employee.id = data.record; //updating new User created ID
            this.$emit("eventFromchild");

            this.$emit("close-popup");
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
