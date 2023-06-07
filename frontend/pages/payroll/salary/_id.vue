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
            <!-- <table v-if="data && data.earnings" class="employee-table">
              <tr class="background white--text">
                <td class="w-50">Earnings</td>
                <td>Deductions</td>
              </tr>
              <tr v-for="(item, index) in data.earnings" :key="index">
                <td class="w-50">{{ caps(item.label) }} {{ item.value }}</td>
                <td>
                  <span v-if="data && data.deductions[index]">
                    Absent {{ data.deductedSalary }}
                  </span>
                </td>
              </tr>
            </table> -->
            <v-row>
              <a
                :href="getdownloadLink"
                style="
                  font-size: 40px;
                  vertical-align: inherit;
                  cursor: pointer;
                  text-align: right;
                "
              >
                <span class="mdi mdi-download-box"></span>
              </a>
            </v-row>
            <v-row>
              <div class="container mt-5 mb-5" id="printMe">
                <div class="row">
                  <div class="col-md-12">
                    <div class="text-center lh-1 mb-2">
                      <h6 class="fw-bold">Payslip</h6>
                      <span class="fw-normal"
                        >Payment slip for the month of {{ currentMonth }}
                        {{ currentYear }}</span
                      >
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <div class="row">
                          <div class="col-md-3 addressheight">
                            <address>
                              <div>
                                <img
                                  :src="this.company_payload.logo"
                                  width="60px"
                                />
                              </div>
                              <strong>{{ this.company_payload.name }}</strong
                              ><br />
                              {{ this.company_payload.p_o_box_no }}
                            </address>
                          </div>
                          <div class="col-md-6 text-right addressheight">
                            <address>
                              <div style="height: 60px"></div>
                              <strong>EMP ID: {{ empCode }}</strong
                              ><br />
                              Name: {{ contact_payload.name }}<br />
                              Designation: {{ contact_payload.position }}<br />
                            </address>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="row">
                            <div class="col-md-6 addressheight2">
                              <table class="mt-4 table table-bordered">
                                <thead class="bg-dark text-white">
                                  <tr>
                                    <th scope="col">Earnings</th>
                                    <th scope="col">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr
                                    v-for="(item, index) in data.earnings"
                                    :key="index"
                                  >
                                    <td class="w-50">
                                      {{ caps(item.label) }}
                                    </td>
                                    <td class="w-50 text-right">
                                      {{ item.value }}
                                    </td>
                                  </tr>
                                  <tr>
                                    <th class="w-50">
                                      <strong>Total Earnings</strong>
                                    </th>
                                    <th class="w-50 text-right">
                                      {{ data.salary_and_earnings }}
                                    </th>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                            <div class="col-md-6 text-right addressheight2">
                              <table class="mt-4 table table-bordered">
                                <thead class="bg-dark text-white">
                                  <tr>
                                    <th scope="col">Deductions</th>
                                    <th scope="col">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr
                                    v-for="(item, index) in data.deductions"
                                    :key="index"
                                  >
                                    <td class="w-50">
                                      {{ caps(item.label) }}
                                    </td>
                                    <td class="w-50 text-right">
                                      {{ item.value }}
                                    </td>
                                  </tr>
                                  <tr v-for="n in countdifference" :key="n">
                                    <th scope="row">&nbsp;</th>
                                    <td class="text-right">&nbsp;</td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Total Deductions</th>
                                    <th class="text-right">
                                      {{ data.deductedSalary }}
                                    </th>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="border col-md-12">
                        <div class="row">
                          <div class="col-md-5">
                            <span
                              >Present Days: {{ this.data.presentDays }}</span
                            >
                          </div>

                          <div class="col-md-5 text-right">
                            <span>Absent Days: {{ this.data.absentDays }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="border col-md-12">
                        <div class="d-flex flex-column">
                          <span
                            ><strong
                              >Net Salary: {{ this.data.earnedSubTotal }}
                            </strong>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </v-row>
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
    currentYear: "",
    currentMonth: "",
    getdownloadLink: "",
    data: {},
    empCode: "",
    countdifference: 0,
    company_payload: {
      name: "",
      logo: "",
      member_from: "",
      expiry: "",
      max_branches: "",
      max_employee: "",
      max_devices: "",
      mol_id: "",
      p_o_box_no: "",
    },
    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    earnings: [],
    deductions: [],
  }),
  created() {
    // this.loading = true;

    let today = new Date();
    let y = today.getFullYear();
    let m = today.getMonth() + 1;

    this.currentYear = y;
    this.currentMonth = today.toLocaleString("default", { month: "long" });
  },
  mounted() {
    this.getDataFromApi();
    this.getCompanyDataFromApi();
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
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getDataFromApi() {
      // this.loading = true;
      //let id = this.$route.params.id;
      let [id, rowid, month, year] = this.$route.params.id.split("_");
      let employee_ids = [];
      employee_ids.push(rowid);
      this.empCode = id;

      this.$axios
        .get(`/payslip/${rowid}`, {
          params: {
            company_id: this.$auth?.user?.company?.id,
            employee_id: id,
            month: month,
            year: year,
            employee_ids: employee_ids,
          },
        })
        .then(({ data }) => {
          console.log("Payslip", data);
          this.data = data;

          this.earnings = data[0].earnings;
          this.deductions = data[0].deductions;

          this.countdifference =
            data[0].earnings.length - data[0].deductions.length;
          console.log("countdifference", this.countdifference);

          this.getdownloadLink =
            this.$axios.defaults.baseURL +
            "/donwload-payslip-pdf?company_id=" +
            this.$auth.user.company.id +
            "&employee_id=" +
            data.employee_id +
            "&month=" +
            data.month +
            "&year=" +
            data.year;

          // this.loading = false;
        });
    },
    getCompanyDataFromApi() {
      let company_id = this.$auth?.user?.company?.id;
      this.$axios.get(`company/${company_id}`).then(({ data }) => {
        console.log("company", data);
        let r = data.record;
        this.company_payload = r;
        this.contact_payload = r.contact;

        this.preloader = false;
      });
    },
    printContent() {
      const printableContent = document.getElementById("printMe");
      const printWindow = window.open("", "", "height=1000,width=1000");
      printWindow.document.write(printableContent.innerHTML);
      printWindow.print();
    },
  },
};
</script>

<style scoped>
.addressheight {
  height: 180px;
}
.addressheight2 {
  min-height: 300px;
}
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
