<template>
  <v-card flat v-if="payroll && can('access')" class="d-flex flex-column">
    <div class="text-right" v-if="can(!editForm ? 'edit' : 'view')">
      <v-icon small color="primary" @click="editForm = !editForm"
        >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
      >
    </div>
    <v-simple-table v-if="can('view')" dense flat class="my-simple-table">
      <tbody>
        <tr>
          <td>Effective Date</td>
          <td>
            <span v-if="!editForm">{{ payroll.effective_date }}</span>

            <v-menu
              v-else
              ref="menu"
              v-model="menu"
              :close-on-content-click="false"
              :return-value.sync="menu"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  append-icon="mdi-calendar"
                  style="border-bottom: 1px solid #eaeaea"
                  class="small-input-font"
                  dense
                  v-model="payroll.effective_date"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  :hide-details="!errors.effective_date"
                  :error-messages="
                    errors && errors.effective_date
                      ? errors.effective_date[0]
                      : ''
                  "
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="payroll.effective_date"
                no-title
                scrollable
                @input="menu = false"
              ></v-date-picker>
            </v-menu>
          </td>
        </tr>
        <tr>
          <td style="width: 50%">Basic Salary</td>
          <td>
            <span v-if="!editForm">{{ payroll.basic_salary }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              style="border-bottom: 1px solid #eaeaea"
              class="small-input-font"
              dense
              v-model="payroll.basic_salary"
              color="primary"
              autofocus
              :hide-details="!errors.basic_salary"
              :error-messages="
                errors && errors.basic_salary ? errors.basic_salary[0] : ''
              "
            />
          </td>
        </tr>
        
        <tr>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </v-simple-table>

    <v-simple-table v-if="can('view')" dense flat class="mt-5 my-simple-table">
      <tbody>
        <tr>
          <th>Particulars</th>
          <td colspan="2" class="text-right">
            <v-icon
              :disabled="!editForm"
              small
              color="primary"
              @click="addEarning"
              >mdi-plus-circle</v-icon
            >
          </td>
        </tr>

        <tr v-for="(d, index) in payroll.earnings" :key="index">
          <td style="width: 50%">
            <span v-if="!editForm">{{ d.label }}</span>
            <v-text-field
              :autofocus="payroll.earnings.length - 1 ? true : false || payroll.earnings.length == 1"
              v-else
              style="border-bottom: 1px solid #eaeaea"
              class="small-input-font"
              dense
              v-model="d.label"
              color="primary"
              :hide-details="!errors.label"
              :error-messages="errors && errors.label ? errors.label[0] : ''"
            />
          </td>
          <td>
            <span v-if="!editForm">{{ d.value }}</span>
            <v-text-field
              v-else
              style="border-bottom: 1px solid #eaeaea"
              class="small-input-font"
              dense
              v-model="d.value"
              color="primary"
              type="number"
              :hide-details="!errors.value"
              :error-messages="errors && errors.value ? errors.value[0] : ''"
            />
          </td>
          <td class="text-right"><v-icon :disabled="!editForm" small color="error" @click="removeEarningItem(index)">mdi-close</v-icon></td>
        </tr>
      </tbody>
    </v-simple-table>

    <v-card-actions class="mt-auto">
      <v-spacer></v-spacer>
      <v-btn
        :disabled="!editForm"
        x-small
        class="grey white--text"
        @click="close_payroll_info"
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
  <NoAccess v-else />
</template>

<script>
export default {
  props: ["employeeId", "employeeObject"],
  data() {
    return {
      editForm: false,
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
    removeEarningItem(index) {
      this.payroll.earnings.splice(index, 1);
    },
    addEarning() {
      let obj = { label: "Add Item", value: 100 };
      this.payroll.earnings.push(obj);
    },
    getInfo() {
      this.loading = true;

      // Deep clone to completely break reactivity
      this.payroll = this.employeeObject.payroll
        ? JSON.parse(JSON.stringify(this.employeeObject.payroll))
        : { earnings: [] };

      if (this.payroll.earnings.length === 0) {
        this.addEarning();
      }
      this.loading = false;
    },
    can(per) {
      return this.$pagePermission.can("employee_payroll_" + per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    submit() {
      this.loading = true;
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
            this.close_payroll_info();
          }
        })
        .catch((e) => {
          this.errors = [];
          this.loading = false;
        });
    },
    close_payroll_info() {
      this.payroll_info = false;
      this.editForm = false;
      this.errors = [];
      this.$emit("close-popup");
    },
  },
};
</script>
