<template>
  <div class="mt-8">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-container>
      <v-btn dark class="primary" @click="addEarning" small>
        <v-icon small>mdi-plus</v-icon> Earnings
      </v-btn>
      <v-row class="mt-5">
        <v-col cols="5">
          <label class="mb-1">Basic Salary</label>
          <v-text-field
            outlined
            dense
            v-model="payroll.basic_salary"
            color="primary"
          />
          <span v-if="errors && errors.basic_salary" class="text-danger">{{
            errors.basic_salary[0]
          }}</span>
        </v-col>

        <v-col cols="5">
          <label class="mb-1">Effective Date</label>
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
                outlined
                dense
                v-model="payroll.effective_date"
                readonly
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker v-model="payroll.effective_date" no-title scrollable>
              <v-spacer></v-spacer>
              <v-btn text color="primary" @click="menu = false"> Cancel </v-btn>
              <v-btn
                text
                color="primary"
                @click="$refs.menu.save(payroll.effective_date)"
              >
                OK
              </v-btn>
            </v-date-picker>
          </v-menu>
          <span v-if="errors && errors.effective_date" class="text-danger">{{
            errors.effective_date[0]
          }}</span>
        </v-col>
      </v-row>
      <v-row v-for="(d, index) in payroll.earnings" :key="index">
        <v-col cols="5">
          <label class="mb-1">Earning Label</label>
          <v-text-field outlined dense v-model.number="d.label" />
          <span
            v-if="errors && errors[`earnings.${index}.label`]"
            class="text-danger"
            >{{ errors[`earnings.${index}.label`][0] }}</span
          >
        </v-col>
        <v-col cols="5">
          <label class="mb-1">Earning Value</label>
          <v-text-field outlined dense v-model="d.value" />
          <span
            v-if="errors && errors[`earnings.${index}.value`]"
            class="text-danger"
            >The earning value field is required.</span
          >
        </v-col>
        <v-col cols="2">
          <v-btn
            dark
            class="error"
            fab
            @click="removeEarningItem(index)"
            x-small
          >
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn class="primary" small @click="save_payroll_info">Save</v-btn>
        </v-col>
      </v-row>
    </v-container>
    <table>
      <tr>
        <th></th>
        <td style="text-align: right">
          <v-icon
            v-if="can(`employee_personal_edit_access`)"
            @click="payroll_info = true"
            small
            class="grey"
            style="border-radius: 50%; padding: 5px"
            color="secondary"
            >mdi-pencil</v-icon
          >
        </td>
      </tr>
      <tr>
        <th>Basic Salary</th>
        <td>
          {{ payroll.basic_salary }}
        </td>
      </tr>
      <tr>
        <th>Effective</th>
        <td>
          {{ payroll.effective_date_formatted }}
        </td>
      </tr>
      <tr v-for="(item, index) in payroll.earnings" :key="index">
        <th>{{ item.label }}</th>
        <td>
          {{ item.value }}
        </td>
      </tr>
    </table>
  </div>
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
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
    removeEarningItem(index) {
      this.payroll.earnings.splice(index, 1);
    },
    addEarning() {
      let obj = { label: "", value: "" };
      this.payroll.earnings.push(obj);
    },
    getInfo() {
      this.$axios
        .get(`payroll/${this.employeeId}`, {
          params: {
            company_id: this.$auth?.user?.company?.id,
          },
        })
        .then(({ data }) => {
          this.payroll = data || { earnings: [] };
        });
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
          }
        })
        .catch((e) => {
          this.errors = [];
          console.log(e);
        });
    },
    close_payroll_info() {
      this.payroll_info = false;
      this.errors = [];
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
