<template>
  <div class="mt-8">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="bank_info" max-width="700px">
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
                  <label class="col-form-label">{{ caps("bank name") }}</label>
                  <input v-model="BankInfo.bank_name" class="form-control" />
                  <span
                    v-if="errors && errors.bank_name"
                    class="text-danger mt-2"
                    >{{ errors.bank_name[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("bank address")
                  }}</label>
                  <input v-model="BankInfo.address" class="form-control" />
                  <span
                    v-if="errors && errors.address"
                    class="text-danger mt-2"
                    >{{ errors.address[0] }}</span
                  >
                </div>
              </v-col>
              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("account no") }}</label>
                  <input v-model="BankInfo.account_no" class="form-control" />
                  <span
                    v-if="errors && errors.account_no"
                    class="text-danger mt-2"
                    >{{ errors.account_no[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{
                    caps("account title")
                  }}</label>
                  <input
                    v-model="BankInfo.account_title"
                    class="form-control"
                  />
                  <span
                    v-if="errors && errors.account_title"
                    class="text-danger mt-2"
                    >{{ errors.account_title[0] }}</span
                  >
                </div>
              </v-col>

              <v-col cols="6">
                <div class="form-group">
                  <label class="col-form-label">{{ caps("iban") }}</label>
                  <input v-model="BankInfo.iban" class="form-control" />
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
                    caps(`${add_other_bank_info ? "hide" : "show"} other field`)
                  }}</a
                >
              </v-col>
              <v-row v-if="add_other_bank_info">
                <v-col cols="6">
                  <div class="form-group">
                    <label class="col-form-label">{{
                      caps("other text")
                    }}</label>
                    <input v-model="BankInfo.other_text" class="form-control" />
                    <span
                      v-if="errors && errors.other_text"
                      class="text-danger"
                      >{{ errors.other_text[0] }}</span
                    >
                  </div>
                </v-col>

                <v-col cols="6">
                  <div class="form-group">
                    <label class="col-form-label">{{
                      caps("other value")
                    }}</label>
                    <input
                      v-model="BankInfo.other_value"
                      class="form-control"
                    />
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
    <table>
      <tr>
        <th></th>
        <td style="text-align: right;">
          <v-icon
            v-if="can(`employee_personal_edit_access`)"
            @click="bank_info = true"
            small
            class="grey"
            style="border-radius: 50%; padding: 5px"
            color="secondary"
            >mdi-pencil</v-icon
          >
        </td>
      </tr>
      <tr>
        <th>Name</th>
        <td>
          {{ caps(BankInfo.bank_name) }}
        </td>
      </tr>

      <tr>
        <th>Account No</th>
        <td>
          {{ caps(BankInfo.account_no) }}
        </td>
      </tr>

      <tr>
        <th>Title</th>
        <td>
          {{ caps(BankInfo.account_title) }}
        </td>
      </tr>

      <tr>
        <th>Iban</th>
        <td>
          {{ caps(BankInfo.iban) }}
        </td>
      </tr>

      <tr>
        <th>Address</th>
        <td>
          {{ caps(BankInfo.address) }}
        </td>
      </tr>
    </table>
  </div>
</template>

<script>
export default {
  props: ["BankInfo"],
  data() {
    return {
      add_other_bank_info: false,
      bank_info: false,
      snackbar: false,
      response: "",
      errors: []
    };
  },
  methods: {
    can(item) {
      return true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, c => c.toUpperCase());
      }
    },
    save_bank_info() {
      let payload = {
        ...this.BankInfo,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.BankInfo.employee_id
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
            this.close_bank_info();
          }
        })
        .catch(e => console.log(e));
    },
    close_bank_info() {
      this.bank_info = false;
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
