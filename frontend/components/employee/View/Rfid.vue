<template>
  <v-card v-if="employeeId" flat class="d-flex flex-column">
    <div class="text-right">
      <v-icon small color="primary" @click="editForm = !editForm"
        >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
      >
    </div>
    <v-simple-table dense flat class="my-simple-table">
      <tbody>
        <tr>
          <td style="width: 200px">RFID</td>
          <td>
            <span v-if="!editForm">{{ employee.rfid_card_number }}</span>
            <v-text-field
              v-else
              autofocus
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="employee.rfid_card_number"
              color="primary"
              :hide-details="!errors.rfid_card_number"
              :error-messages="
                errors && errors.rfid_card_number
                  ? errors.rfid_card_number[0]
                  : ''
              "
            />
          </td>
        </tr>
        <tr>
          <td style="width: 200px">PIN</td>
          <td>
            <span v-if="!editForm">{{ employee.rfid_card_password }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="employee.rfid_card_password"
              color="primary"
              :hide-details="!errors.rfid_card_password"
              :error-messages="
                errors && errors.rfid_card_password
                  ? errors.rfid_card_password[0]
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
        v-if="can('employee_edit')"
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
export default {
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
    this.getRoles();
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
          this.employee.employee_id = id;
          this.employee.company_id = this.$auth.user.company_id;
          this.employee.rfid_card_number = data.rfid_card_number;
          this.employee.rfid_card_password = data.rfid_card_password;

          this.loading = false;
        })
        .catch((err) => console.log(err));
    },
    can() {
      return true;
    },
    getRoles() {
      this.payloadOptions.params.role_type = "employee";

      this.$axios.get(`role`, this.payloadOptions).then(({ data }) => {
        this.roles = data.data;
      });
    },
    close() {
      this.dialog = false;
    },
    submit() {
      this.loading = true;
      let payload = {
        params: {
          rfid_card_number: this.employee.rfid_card_number,
          rfid_card_password: this.employee.rfid_card_password,
          employee_id: this.employee.employee_id,
        },
      };
      this.$axios
        .post(`/employee-rfid-update/${this.employee.id || 0}`, payload.params)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.color = "red";
            this.errors = data.errors;
            this.snackbar = true;
            this.response = data.message;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees Updated successfully";
            setTimeout(() => {
              this.color = "background";
              this.$emit("eventFromchild");
              this.$emit("close-popup");
            }, 1000);
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
