<template>
  <v-card flat v-if="can('access')">
    <v-card-text>
      <v-row class="d-flex align-center">
        <v-col cols="12">
          <v-tabs v-model="tab">
            <v-tab>Bank</v-tab>
            <v-tab>Payroll</v-tab>
          </v-tabs>
          <v-tabs-items v-model="tab">
            <v-tab-item>
              <v-card flat class="d-flex flex-column">
                <div class="text-right" v-if="can(!editForm ? 'edit' : 'view')">
                  <v-icon small color="primary" @click="editForm = !editForm"
                    >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
                  >
                </div>
                <v-simple-table v-if="can('view')" dense flat class="my-simple-table">
                  <tbody>
                    <tr>
                      <td style="width: 200px">Account Name</td>
                      <td>
                        <span v-if="!editForm">{{ data.account_title }}</span>
                        <v-text-field
                          v-else
                          autofocus
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="data.account_title"
                          color="primary"
                          :hide-details="!errors.account_title"
                          :error-messages="
                            errors && errors.account_title
                              ? errors.account_title[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Bank Name</td>
                      <td>
                        <span v-if="!editForm">{{ data.bank_name }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="data.bank_name"
                          color="primary"
                          :hide-details="!errors.bank_name"
                          :error-messages="
                            errors && errors.bank_name
                              ? errors.bank_name[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>A/C Number</td>
                      <td>
                        <span v-if="!editForm">{{ data.account_no }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="data.account_no"
                          color="primary"
                          :hide-details="!errors.account_no"
                          :error-messages="
                            errors && errors.account_no
                              ? errors.account_no[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Iban Number</td>
                      <td>
                        <span v-if="!editForm">{{ data.iban }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="data.iban"
                          color="primary"
                          :hide-details="!errors.iban"
                          :error-messages="
                            errors && errors.iban ? errors.iban[0] : ''
                          "
                        />
                      </td>
                    </tr>
                    <!-- <tr>
          <td>Branch</td>
          <td>
            <span v-if="!editForm">{{ data.branch || "Dubai" }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="data.branch"
              color="primary"
              :hide-details="!errors.branch"
              :error-messages="errors && errors.branch ? errors.branch[0] : ''"
            />
          </td>
        </tr> -->
                    <tr>
                      <td>Address</td>
                      <td>
                        <span v-if="!editForm">{{
                          data.address || "Dubai"
                        }}</span>
                        <v-text-field
                          v-else
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="data.address"
                          color="primary"
                          :hide-details="!errors.address"
                          :error-messages="
                            errors && errors.address ? errors.address[0] : ''
                          "
                        />
                      </td>
                    </tr>
                    <!-- <tr>
          <td>Currency</td>
          <td>
            <span v-if="!editForm">{{ data.currency || "AED" }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="data.currency"
              color="primary"
              :hide-details="!errors.currency"
              :error-messages="
                errors && errors.currency ? errors.currency[0] : ''
              "
            />
          </td>
        </tr> -->
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
            </v-tab-item>
            <v-tab-item>
              <EmployeeViewPayroll
                :employeeId="employeeId"
                :employeeObject="employeeObject"
                @eventFromchild="close"
              />
            </v-tab-item> </v-tabs-items
        ></v-col>
      </v-row>
    </v-card-text>
  </v-card>
  <NoAccess v-else />
</template>

<script>
export default {
  props: ["employeeId", "employeeObject"],
  data() {
    return {
      tab: null,
      editForm: false,
      endpoint: "bankinfo",
      add_other_bank_info: false,
      loading: false,
      popup: false,
      snackbar: false,
      response: "",
      errors: [],
      //data: {},
      data: {
        bank_name: "",
        account_no: "",
        account_title: "",
        address: "",
        other_text: "",
        other_value: "",
        iban: "",
      },
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
          //this.data = data;

          this.data = {
            bank_name: data.bank_name,
            address: data.address,
            account_no: data.account_no,
            account_title: data.account_title,
            iban: data.iban,
            other_text: data.other_text,
            other_value: data.other_value,
          };
        })
        .catch((err) => {
          console.log(err);
        });
    },
    can(per) {
      return this.$pagePermission.can("employee_bank_" + per, this);
    },
    submit() {
      this.loading = true;
      let payload = {
        ...this.data,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.employeeId,
      };
      this.$axios
        .post(`bankinfo`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.$emit("eventFromchild");
            this.$emit("close-popup");
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.getInfo();
            this.close();
          }
        })
        .catch((e) => {
          console.log(e);
          this.loading = false;
        });
    },
    close() {
      this.popup = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },
  },
};
</script>
