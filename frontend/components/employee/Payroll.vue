<template>
  <div class="mt-8">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-container>
      <v-row v-if="payroll">
        <v-col cols="6">
          <v-row>
            <v-col cols="5">
              <v-text-field
                label="Basic Salary"
                outlined
                hide-details
                dense
                v-model="payroll.basic_salary"
                color="primary"
              />
              <span v-if="errors && errors.basic_salary" class="text-danger">{{
                errors.basic_salary[0]
              }}</span>
            </v-col>

            <v-col cols="5">
              <v-menu
                ref="menu"
                v-model="menu"
                :close-on-content-click="false"
                :return-value.sync="date"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    label="Effective Date"
                    outlined
                    hide-details
                    dense
                    v-model="payroll.effective_date"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="payroll.effective_date"
                  no-title
                  scrollable
                >
                  <v-spacer></v-spacer>
                  <v-btn text color="primary" @click="menu = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    text
                    color="primary"
                    @click="$refs.menu.save(payroll.effective_date)"
                  >
                    OK
                  </v-btn>
                </v-date-picker>
              </v-menu>
              <span
                v-if="errors && errors.effective_date"
                class="text-danger"
                >{{ errors.effective_date[0] }}</span
              >
            </v-col>
          </v-row>
          <v-row v-for="(d, index) in payroll.earnings" :key="index">
            <v-col cols="5">
              <v-text-field
                label="Earning Label"
                outlined
                hide-details
                dense
                v-model.number="d.label"
              />
              <span
                v-if="errors && errors[`earnings.${index}.label`]"
                class="text-danger"
                >{{ errors[`earnings.${index}.label`][0] }}</span
              >
            </v-col>
            <v-col cols="5">
              <v-text-field
                label="Earning Value"
                outlined
                hide-details
                dense
                v-model="d.value"
              />
              <span
                v-if="errors && errors[`earnings.${index}.value`]"
                class="text-danger"
                >The earning value field is required.</span
              >
            </v-col>
            <v-col cols="2">
              <v-icon @click="removeEarningItem(index)">mdi-close</v-icon>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-btn
                fab
                icon
                x-small
                @click="addEarning"
                class="primary white--text"
              >
                <v-icon>mdi-plus</v-icon>
              </v-btn>
            </v-col>
            <v-col cols="4" class="text-right">
              <v-btn class="grey white--text" small @click="cancel"
                >Cancel</v-btn
              >
              <v-btn class="primary" small @click="save_payroll_info"
                >Save</v-btn
              >
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
export default {
  props: ["employeeId", "currentItem"],
  data() {
    return {
      displayEditform: false,
      loading: false,
      payroll_info: false,
      menu: false,
      date: false,
      snackbar: false,
      response: "",
      errors: [],
      earningsErrors: [],
      payroll: {
        earnings: [],
      },
    };
  },
  created() {
    this.getInfo();
  },
  computed: {
    net_salary() {
      const { earnings } = this.payroll;
      const basic_salary = parseInt(this.payroll.basic_salary);

      if (earnings && earnings.length) {
        const reducer = (acc, cv) => acc + parseInt(cv.value);

        return `${basic_salary + earnings.reduce(reducer, 0)}`;
      }

      return basic_salary;
    },
  },
  methods: {
    displayEdit() {
      this.displayEditform = true;
    },
    cancel() {
      this.displayEditform = false;
    },
    removeEarningItem(index) {
      this.payroll.earnings.splice(index, 1);
    },
    addEarning() {
      let obj = { label: "", value: "" };
      this.payroll.earnings.push(obj);
    },
    getInfo() {
      this.loading = true;

      // Create a local copy to avoid mutating Vuex state directly
      this.payroll = this.currentItem.payroll
        ? {
            ...this.currentItem.payroll,
            earnings: [...this.currentItem.payroll.earnings],
          }
        : { earnings: [] };

      if (this.payroll.earnings.length === 0) {
        this.addEarning();
      }
    },

    can(item) {
      return true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    save_payroll_info() {
      this.errors = [];

      let payload = {
        ...this.payroll,
        net_salary: this.net_salary,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.employeeId,
      };
      this.loading = true;
      this.$axios
        .post(`/payroll`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = data.message;
            this.payroll = data.record || { earnings: [] };
            this.close_payroll_info();
          }
        })
        .catch((e) => {
          this.errors = [];
        });
    },
    close_payroll_info() {
      this.payroll_info = false;
      this.errors = [];
      this.cancel();
      this.$emit("close-popup");
    },
  },
};
</script>
