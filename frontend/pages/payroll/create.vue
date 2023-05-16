<template>
  <div v-if="can('setting_company_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!preloader">
      <v-row class="mt-5 mb-5">
        <v-col cols="10">
          <h3>Payroll Formula</h3>
          <div>Dashboard / Payroll Formula</div>
        </v-col>
      </v-row>
      <v-card elevation="0" class="pa-3">
        <v-card-title>
          <label class="col-form-label pt-0 mt-5"
            ><b>Create Payroll Formula</b></label
          >
          <v-spacer></v-spacer>
          <v-btn small fab color="background" dark to="/report_notifications">
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
        </v-card-title>
        <v-container>
          <v-row>
            <v-col cols="2">
              <label class="col-form-label pt-0 mt-5"
                ><b>Salary calculation formula</b></label
              >
            </v-col>
            <v-col cols="10">
              <div style="display: inline-flex;">
                <v-radio-group v-model="payload.salary_type" row>
                  <v-radio label="Basic Salary" value="basic_salary"></v-radio>
                  <v-radio label="Net Salary" value="net_salary"></v-radio>
                </v-radio-group>
              </div>
              <span
                  v-if="errors && errors.salary_type"
                  class="text-danger"
                  >{{ errors.salary_type[0] }}</span
                >
            </v-col>

            <v-col cols="2">
              <label class="col-form-label"><b>OT formula</b></label>
            </v-col>
            <v-col cols="10">
              <div style="display: inline-flex;">
                <input
                  class="form-control"
                  type="text"
                  outlined
                  dense
                  value="Per Hour Salary"
                  readonly
                />
                <span class="pa-2">x</span>
                <input
                  v-model="payload.ot_value"
                  class="form-control"
                  type="text"
                  outlined
                  dense
                />
              </div>
              <span
                  v-if="errors && errors.ot_value"
                  class="text-danger"
                  >{{ errors.ot_value[0] }}</span
                >
            </v-col>

            <v-col cols="2">
              <label class="col-form-label"
                ><b>Late Deduction formula</b></label
              >
            </v-col>
            <v-col cols="8">
              <div style="display: inline-flex;">
                <input
                  class="form-control"
                  type="text"
                  outlined
                  dense
                  value="Per Hour Salary"
                  readonly
                />
                <span class="pa-2">x</span>
                <input
                  v-model="payload.deduction_value"
                  class="form-control"
                  type="text"
                  outlined
                  dense
                />
              </div>
              <span
                  v-if="errors && errors.deduction_value"
                  class="text-danger"
                  >{{ errors.deduction_value[0] }}</span
                >
            </v-col>
            <v-col cols="12">
              <v-btn small color="primary" @click="store">
                Submit
              </v-btn>
            </v-col>
          </v-row>
        </v-container>
      </v-card>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    payload: {
      salary_type: "basic_salary",
      ot_value:1.5,
      deduction_value:1.5,
    },
    preloader: false,
    loading: false,
    response: false,
    snackbar: false,
    errors: []
  }),

  created() {
    this.preloader = false;
    this.payload.company_id = this.$auth?.user?.company?.id;
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    store() {
      this.errors = [];
      this.$axios
        .post("/payroll_formula", this.payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
            return;
          }

          this.snackbar = data.status;
          this.response = data.message;
        })
        .catch(e => console.log(e));
    }
  }
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
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
<style>
.tiptap-vuetify-editor__content {
  min-height: 400px !important;
}

.ProseMirror .ProseMirror-focused {
  height: 400px !important;
}

.tiptap-icon .v-icon {
  color: white !important;
}
.tiptap-icon .v-btn--icon {
  color: white !important;
}
</style>
