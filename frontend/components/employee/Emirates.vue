<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-container>
      <v-row>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("emirate id") }}</label>
            <input
              v-model="emirateItems.emirate_id"
              type="text"
              class="form-control"
            />
            <span v-if="errors && errors.emirate_id" class="text-danger mt-2">{{
              errors.emirate_id[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("name") }}</label>
            <input
              v-model="emirateItems.name"
              type="text"
              class="form-control"
            />
            <span v-if="errors && errors.name" class="text-danger mt-2">{{
              errors.name[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("gender") }}</label>
            <select
              v-model="emirateItems.gender"
              class="form-select"
              aria-label="Default select"
            >
              <option value="">Select...</option>
              <option value="1">Male</option>
              <option value="2">Female</option>
            </select>
            <span v-if="errors && errors.gender" class="text-danger mt-2">{{
              errors.gender[0]
            }}</span>
          </div>
        </v-col>

        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("nationality") }}</label>
            <input
              v-model="emirateItems.nationality"
              class="form-control"
              type="text"
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
            <label class="col-form-label">{{ caps("issue date") }}</label>
            <input
              v-model="emirateItems.issue"
              type="date"
              class="form-control"
            />
            <span v-if="errors && errors.issue" class="text-danger mt-2">{{
              errors.issue[0]
            }}</span>
          </div>
        </v-col>

        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("expiry date") }}</label>
            <input
              v-model="emirateItems.expiry"
              type="date"
              class="form-control"
            />
            <span v-if="errors && errors.expiry" class="text-danger mt-2">{{
              errors.expiry[0]
            }}</span>
          </div>
        </v-col>

        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("Date of birth") }}</label>
            <input
              v-model="emirateItems.date_of_birth"
              type="date"
              class="form-control"
            />
            <span
              v-if="errors && errors.date_of_birth"
              class="text-danger mt-2"
              >{{ errors.expiry[0] }}</span
            >
          </div>
        </v-col>

        <span v-if="errors && errors.length" class="error--text">{{
          errors
        }}</span>
      </v-row>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn class="primary" small @click="save_emirate_info">Save</v-btn>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      emirate_info: false,
      errors: [],
      snackbar: false,
      response: "",
      emirateItems: {},
    };
  },
  created() {
    this.getInfo(this.employeeId);
  },
  methods: {
    getInfo(id) {
      this.$axios.get(`emirate/${id}`).then(({ data }) => {
        this.emirateItems = {
          ...data,
          employee_id: id,
        };
      });
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    can(item) {
      return true;
    },

    close_emirate_info() {
      this.emirate_info = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },

    save_emirate_info() {
      let payload = {
        ...this.emirateItems,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.emirateItems.employee_id,
      };

      this.$axios
        .post(`emirate`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Emirates Info has been added";
            this.close_emirate_info();
          }
        })
        .catch((e) => console.log(e));
    },
  },
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
