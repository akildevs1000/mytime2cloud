<template>
  <div v-if="can(`employee_access`)">
    <v-row class="mt-5 mb-5">
      <v-col cols="6" sm="6" md="6">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }}</div>
      </v-col>
    </v-row>
    <div v-if="can(`employee_view`)">
      <v-row>
        <v-col>
          <!-- <table>
            <tr>
              <td>
                <v-btn class="primary"> <b>Present</b> 20</v-btn>
              </td>
              <td>
                <v-btn class="primary"> <b>Performed Hours</b> 20</v-btn>
              </td>
              <td>
                <v-btn class="primary"> <b>OT Hours</b> 20</v-btn>
              </td>
              <td>
                <v-btn class="orange white--text"> <b>Absents</b> 20</v-btn>
              </td>
              <td>
                <v-btn class="orange white--text">
                  <b>Missing Hours</b> 20</v-btn
                >
              </td>
              <td>
                <v-btn class="orange white--text"> <b>Late Hours</b> 20</v-btn>
              </td>
            </tr>
          </table> -->

          <v-card class="mb-5" elevation="0">
            <table v-if="data && data.earnings" class=" employee-table">
              <tr class="background white--text">
                <td class="w-50">
                  Earnings
                </td>
                <td>
                  Deductions
                </td>
              </tr>
              <tr v-for="(item, index) in data.earnings" :key="index">
                <td class="w-50">{{ caps(item.label) }} {{ item.value }}</td>
                <td>
                  <span v-if="data && data.deductions[index]">
                    Absent {{ data.deductedSalary }}
                  </span>
                </td>
              </tr>
            </table>

            <!-- <table v-if="data && data.earnings" class="employee-table">
              <v-progress-linear
                v-if="loading"
                :active="loading"
                :indeterminate="loading"
                absolute
                color="primary"
              ></v-progress-linear>
              <tr>
                <td style="width:50%;">
                  {{ data.present }} Presents x {{ data.perDaySalary }} AED =
                  {{ data.earnedSalary }}
                </td>
                <td>{{ data.deductedSalary }}</td>
              </tr>

              <tr>
                <td>Sub Total: {{ data.earnedSubTotal }}</td>
                <td>Sub Total: {{ data.earnedSubTotal }}</td>
              </tr>
            </table>

            <table v-if="data" class="employee-table">
              <tr>
                <td>Net Salary : {{ data.finalSalary }}</td>
                <td>Net Salary : {{ data.finalSalary }}</td>
              </tr>
            </table> -->
          </v-card>
        </v-col>
      </v-row>
    </div>
    <NoAccess v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    tab: null,
    Model: "Payslip",
    data: {}
  }),
  created() {
    // this.loading = true;
  },
  mounted() {
    this.getDataFromApi();
  },

  methods: {
    can(per) {
      let u = this.$auth.user;
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
    getDataFromApi() {
      // this.loading = true;
      let id = this.$route.params.id;
      this.$axios
        .get(`/payslip/${id}`, {
          params: {
            company_id: this.$auth?.user?.company?.id
          }
        })
        .then(({ data }) => {
          console.log(data);
          this.data = data;
          // this.loading = false;
        });
    }
  }
};
</script>

<style scoped>
table.employee-table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  /* border: 1px solid #dddddd; */
  text-align: left;
  padding: 8px;
}

table.employee-table tr:nth-child(even) {
  background-color: #e9e9e9;
}
</style>
