<template>
  <div class="mt-8">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="popup" max-width="700px">
      <v-card>
        <v-card-actions>
          <span class="headline">Bank Info </span>
          <v-spacer></v-spacer>
        </v-card-actions>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">Bank Name</label>
                  <input v-model="data.bank_name" class="form-control" />
                  <span
                    v-if="errors && errors.bank_name"
                    class="text-danger mt-2"
                    >{{ errors.bank_name[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">Bank Address</label>
                  <input v-model="data.address" class="form-control" />
                  <span
                    v-if="errors && errors.address"
                    class="text-danger mt-2"
                    >{{ errors.address[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">Account No</label>
                  <input v-model="data.account_no" class="form-control" />
                  <span
                    v-if="errors && errors.account_no"
                    class="text-danger mt-2"
                    >{{ errors.account_no[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">Account Title</label>
                  <input v-model="data.account_title" class="form-control" />
                  <span
                    v-if="errors && errors.account_title"
                    class="text-danger mt-2"
                    >{{ errors.account_title[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">IBAN</label>
                  <input v-model="data.iban" class="form-control" />
                  <span v-if="errors && errors.iban" class="text-danger mt-2">{{
                    errors.iban[0]
                  }}</span>
                </div>
              </v-col>

              <v-col cols="12">
                <a
                  href="javascrip:void(0)"
                  @click="add_other_bank_info = !add_other_bank_info"
                  >{{
                    `${add_other_bank_info ? "hide" : "show"} Other Field`
                  }}</a
                >
              </v-col>
              <v-row v-if="add_other_bank_info">
                <v-col cols="6">
                  <div class="form-group">
                    <label class="col-form-label">Other Text</label>
                    <input v-model="data.other_text" class="form-control" />
                    <span
                      v-if="errors && errors.other_text"
                      class="text-danger"
                      >{{ errors.other_text[0] }}</span
                    >
                  </div>
                </v-col>

                <v-col cols="6">
                  <div class="form-group">
                    <label class="col-form-label">Other Value</label>
                    <input v-model="data.other_value" class="form-control" />
                    <span
                      v-if="errors && errors.other_value"
                      class="text-danger mt-2"
                      >{{ errors.other_value[0] }}</span
                    >
                  </div>
                </v-col>
              </v-row>
              <span v-if="errors && errors.length" class="error--text">{{
                errors
              }}</span>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn class="error" small @click="close_bank_info"> Cancel </v-btn>
          <v-btn class="primary" small @click="save_bank_info">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <KeyValueTable :data="table_data" @open-edit="popup = true" />
  </div>
</template>

<script>
import KeyValueTable from "./KeyValueTable.vue";

export default {
  components: { KeyValueTable },
  props: ["employeeId","hideEditBtn"],
  data() {
    return {
      endpoint: "bankinfo",
      add_other_bank_info: false,
      popup: false,
      snackbar: false,
      response: "",
      errors: [],
      data: {},
      table_data: {}
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      this.$axios
        .get(`${this.endpoint}/${this.employeeId}`)
        .then(({ data }) => {
          this.data = data;

          this.table_data = {
            "Bank Name": data.bank_name,
            "Bank Address": data.address,
            "Account No": data.account_no,
            "Account Title": data.account_title,
            IBAN: data.iban
          };

          if (data.other_text) {
            this.table_data[data.other_text] = data.other_value;
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    can(item) {
      return true;
    },
    save_bank_info() {
      let payload = {
        ...this.data,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.employeeId
      };
      this.$axios
        .post(`bankinfo`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.getInfo();
            this.close_bank_info();
          }
        })
        .catch(e => console.log(e));
    },
    close_bank_info() {
      this.popup = false;
      this.errors = [];
      setTimeout(() => {}, 300);
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
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #fbfdff;
}
</style>
