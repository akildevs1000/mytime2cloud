<template>
  <v-card>
    <v-card-text>
      <v-row>
        <v-col md="6" sm="12" cols="12" dense>
          <v-row>
            <v-col md="12" sm="12" cols="12">
              <label class="col-form-label"
                >Title <span class="text-danger">*</span></label
              >
              <v-select
                v-model="employee.title"
                :items="titleItems"
                :hide-details="!errors.title"
                :error="errors.title"
                :error-messages="errors && errors.title ? errors.title[0] : ''"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col md="12" sm="12" cols="12" dense>
              <label class="col-form-label"
                >Display Name <span class="text-danger">*</span></label
              >
              <v-text-field
                dense
                outlined
                :hide-details="!errors.display_name"
                type="text"
                v-model="employee.display_name"
                :error="errors.display_name"
                :error-messages="
                  errors && errors.display_name ? errors.display_name[0] : ''
                "
              ></v-text-field>
            </v-col>
            <v-col md="12" cols="12" sm="12" dense>
              <label class="col-form-label"
                >Employee ID <span class="text-danger">*</span></label
              >
              <v-text-field
                dense
                outlined
                type="text"
                v-model="employee.employee_id"
                :hide-details="!errors.employee_id"
                :error="errors.employee_id"
                :error-messages="
                  errors && errors.employee_id ? errors.employee_id[0] : ''
                "
              ></v-text-field>
            </v-col>
            <v-col md="12" cols="12" sm="12" dense>
              <label class="col-form-label"
                >Employee Device Id<span class="text-danger">*</span></label
              >
              <v-text-field
                dense
                outlined
                type="text"
                v-model="employee.system_user_id"
                :hide-details="!errors.system_user_id"
                :error="errors.system_user_id"
                :error-messages="
                  errors && errors.system_user_id
                    ? errors.system_user_id[0]
                    : ''
                "
              ></v-text-field>
            </v-col>
          </v-row>
        </v-col>
        <v-col class="col-sm-6">
          <div class="form-group pt-15" style="margin: 0 auto; width: 200px">
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
            <v-btn small class="form-control primary" @click="onpick_attachment"
              >{{ !upload.name ? "Upload" : "Change" }}
              Profile Image
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
    </v-card-text>

    <v-divider></v-divider>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn small color="grey white--text" @click="employeeDialog = false">
        Close
      </v-btn>

      <v-btn
        v-if="can('employee_create')"
        small
        :loading="loading"
        color="primary"
        @click="store_data"
      >
        Submit
      </v-btn>
    </v-card-actions>
  </v-card>
</template>
<script>
export default {
  data: () => ({
    attrs: [],
    dialog: false,
    editDialog: false,
    tab: null,
    employeeDialog: false,
    comp: "WorkInfo",
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
      display_name: "",
      employee_id: "",
      system_user_id: "",
      department_id: "",
    },
    upload: {
      name: "",
    },
    previewImage: null,
    payload: {},
    personalItem: {},
    contactItem: {},
    emirateItems: {},
    setting: {},
    employeeId: "",

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    options: {},
    Model: "Employee",
    endpoint: "employee",
    search: "",
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
  }),
  async created() {
    // this.loading = false;
    this.boilerplate = true;
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
      this.upload_edit.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      console.log("file", file);

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
          this.previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        employee.append(x, obj[x]);
      }
      employee.append("profile_picture", this.upload.name);
      employee.append("company_id", this.$auth.user.company.id);

      return employee;
    },
    store_data() {
      let final = Object.assign(this.employee);
      let employee = this.mapper(final);

      this.$axios
        .post("/employee-store", employee)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees inserted successfully";
            this.getDataFromApi();
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
