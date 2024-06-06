<template>
  <div>
    <v-card elevation="0">
      <v-card-text class="text-center" elevation="0">
        <table style="width: 100%; margin: auto" class="table-payslip">
          <!-- <tr>
              <td class="text-center">{{ company_payload.name }}</td>
            </tr>
            <tr>
              <td class="text-center">
                Payslip Of the Month : {{ currentMonth }}, {{ currentYear }}
              </td>
            </tr> -->
          <tr>
            <td>
              <table style="width: 100%">
                <tr>
                  <td style="width: 50%">
                    <span
                      style="
                        float: left1;
                        height: 60px;
                        padding-top: 30px;
                        padding-left: 10px;
                        line-height: 18px;
                      "
                    >
                      <!-- <div class="outer-wrapper">
                          <div class="frame">
                            <img
                              class="img1"
                              :src="this.company_payload.logo"
                              alt="Cloudy Sky"
                            />
                          </div>
                        </div> -->
                      <img
                        :src="this.company_payload.logo"
                        style="width: 150px; max-height: 100px"
                      />
                    </span>
                    <span style="float: left1; line-height: 16px"
                      ><div class="ml-3 mt-0">
                        <div style="border-top: 1px solid white">
                          <strong>{{ this.company_payload.name }}</strong>
                        </div>
                        <div class="w-10" style="font-size: 12px">
                          {{ this.company_payload.location }},
                        </div>
                        <div class="w-10" style="font-size: 12px">
                          {{ this.company_payload.p_o_box_no }}
                        </div>
                      </div></span
                    >
                    <!-- <div class="d-flex111" style="border: 1px solid white">
                        <img
                          :src="this.company_payload.logo"
                          style="height: 50px"
                        />
                        <div class="ml-3 mt-3">
                          <div style="border-top: 1px solid white">
                            <strong>{{ this.company_payload.name }}</strong>
                          </div>
                          <div class="w-10">
                            {{ this.company_payload.location }},
                          </div>
                          <div class="w-10">
                            {{ this.company_payload.p_o_box_no }}
                          </div>
                        </div>
                      </div> -->
                  </td>
                  <td style="text-align: right; line-height: 1.6">
                    <table style="width: 100%; text-align: right">
                      <tr>
                        <td style="font-size: 28px; text-align: right">
                          Payslip
                        </td>
                      </tr>
                      <tr>
                        <td style="text-align: right; font-size: 12px">
                          Date: {{ data.date }}
                        </td>
                      </tr>
                      <tr>
                        <td style="text-align: right; font-size: 12px">
                          Month: {{ data.payslip_month_year }}
                        </td>
                      </tr>
                    </table>
                    <!-- <div>
                      <div style="border-top: 1px solid white; font-size: 13px">
                        <strong>
                          {{ data.payslip_number }}
                           
                        </strong>
                      </div>
                      <div style="border-top: 1px solid white; font-size: 13px">
                        <strong>Date: {{ data.date }} </strong>
                      </div>
                      <div style="border-top: 1px solid white; font-size: 13px">
                        <strong> {{ data.payslip_month_year }} </strong>
                      </div>
                    </div> -->
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td
              style="
                border: 0px solid #ddd;
                padding-left: 10px;
                padding-top: 20px;
                line-height: 18px;
              "
            >
              <table style="width: 100%">
                <tr>
                  <td>
                    <div style="font-weight: bold">Pay To :</div>
                    <span
                      v-if="employee && employee.first_name"
                      style="font-size: 12px"
                      >{{ employee.first_name }}</span
                    >
                    <span
                      v-if="employee && employee.last_name"
                      style="font-size: 12px"
                      >{{ employee.last_name }}</span
                    >

                    <div style="font-size: 12px">
                      #{{ empCode }} ,
                      {{
                        caps(employee.department && employee.department.name) ||
                        " "
                      }}
                    </div>
                    <div></div>
                    <div style="font-size: 12px">
                      {{
                        caps(
                          employee.designation && employee.designation.name
                        ) || " "
                      }}
                    </div>
                  </td>

                  <td style="text-align: right; padding-right: 0px">
                    <table style="width: 100%">
                      <tr>
                        <td>Total Days</td>
                        <td>{{ data.total_month_days }}</td>
                      </tr>
                      <tr>
                        <td>Present</td>
                        <td>{{ data.present }}</td>
                      </tr>
                      <tr>
                        <td>Absent</td>
                        <td>{{ data.absent }}</td>
                      </tr>
                      <tr>
                        <td>Late</td>
                        <td>{{ data.late }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- <tr>
              <td>
                <table
                  style="width: 100%; border: 1px solid #ddd; padding-left: 0px"
                >
                  <tr>
                    <td style="width: 150px; padding-left: 10px">
                      Employee Name
                    </td>
                    <td style="text-align: left">
                      <span v-if="employee && employee.first_name">{{
                        employee.first_name
                      }}</span>
                      <span v-if="employee && employee.last_name">{{
                        employee.last_name
                      }}</span>
                    </td>
                    <td>Total Days</td>
                    <td>{{ data.total_month_days }}</td>
                  </tr>
  
                  <tr>
                    <td style="width: 150px; padding-left: 10px">Employee Id</td>
                    <td style="text-align: left">{{ empCode }}</td>
                    <td>Present</td>
                    <td>{{ data.present }}</td>
                  </tr>
                  <tr>
                    <td style="width: 150px; padding-left: 10px">Department</td>
                    <td style="text-align: left">
                      {{
                        (employee.department && employee.department.name) || "---"
                      }}
                    </td>
                    <td>Present</td>
                    <td>{{ data.present }}</td>
                  </tr>
                  <tr>
                    <td style="width: 150px; padding-left: 10px">Designation</td>
                    <td style="text-align: left">
                      {{
                        (employee.designation && employee.designation.name) ||
                        "---"
                      }}
                    </td>
                    <td>Absent</td>
                    <td>{{ data.absent }}</td>
                  </tr>
                  <tr>
                    <td style="width: 150px; padding-left: 10px">Branch</td>
                    <td style="text-align: left">
                      {{
                        (employee.designation && employee.designation.name) ||
                        "---"
                      }}
                    </td>
                    <td>Off/Late</td>
                    <td>{{ data.absent }}</td>
                  </tr>
                </table>
              </td>
            </tr> -->
          <tr>
            <td>
              <table
                style="
                  width: 100%;
                  border: 0px solid #ddd;
                  padding-left: 0px;
                  margin-top: 10px;
                  line-height: 40px;
                "
              >
                <tr
                  style="
                    background-color: #4d5973;
                    color: #fff;

                    height: 38px;
                  "
                >
                  <td style="padding-left: 10px; text-align: left">
                    # Earnings
                  </td>
                  <td style="text-align: right">
                    <!-- <span style="">AED</span> -->
                  </td>
                </tr>
                <tr>
                  <td style="width: 100%; padding-right: 0px">
                    <table style="width: 100%; padding-right: 40px">
                      <tr>
                        <td
                          colspan="2"
                          style="text-align: center; font-weight: bold"
                        ></td>
                      </tr>
                      <tr
                        v-for="(item, index) in data.earnings"
                        :key="index"
                        style="border-bottom: 1px solid #ddd"
                      >
                        <td style="text-align: left; padding-left: 10px">
                          {{ index + 1 }} &nbsp;&nbsp;&nbsp;&nbsp;{{
                            caps(item.label)
                          }}
                        </td>
                        <td style="text-align: right; padding-right: 40px">
                          {{ item.value }}
                        </td>
                      </tr>

                      <tr style="font-weight: bold">
                        <td
                          style="
                            width: 150px;
                            text-align: left;
                            padding-left: 10px;
                            font-weight: bold;
                            padding-bottom: 20px;
                          "
                        >
                          Total Earnings
                        </td>
                        <td
                          style="
                            text-align: right;
                            padding-bottom: 20px;
                            padding-right: 40px;
                          "
                        >
                          {{ data.salary_and_earnings }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr
                  style="
                    background-color: #4d5973;
                    color: #fff;
                    margin-top: 20px;
                    height: 30px;
                    line-height: 20px;
                  "
                >
                  <td
                    style="
                      padding-left: 10px;
                      text-align: left;
                      line-height: 20px;
                    "
                  >
                    # Deductions
                  </td>
                  <td style="text-align: right; line-height: 30px">
                    <!-- <span style="">AED</span> -->
                  </td>
                </tr>
                <tr>
                  <td style="width: 100%; padding-right: 0px">
                    <table style="width: 100%; line-height: 30px">
                      <tr>
                        <td
                          colspan="2"
                          style="text-align: center; font-weight: bold"
                        ></td>
                      </tr>
                      <tr
                        v-for="(item, index) in data.deductions"
                        :key="index"
                        style="border-bottom: 1px solid #ddd"
                      >
                        <td style="text-align: left; padding-left: 10px">
                          {{ index + 1 }} &nbsp;&nbsp;&nbsp;&nbsp;
                          {{ caps(item.label) }}
                        </td>
                        <td style="text-align: right; padding-right: 40px">
                          {{ item.value }}
                        </td>
                      </tr>

                      <tr style="font-weight: bold">
                        <td
                          style="
                            width: 150px;
                            text-align: left;
                            padding-left: 10px;
                            font-weight: bold;
                            padding-bottom: 20px;
                          "
                        >
                          Total Deductions
                        </td>
                        <td
                          style="
                            text-align: right;
                            padding-bottom: 20px;
                            padding-right: 40px;
                          "
                        >
                          {{ data.deductedSalary }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr style="background-color: #4d5973; color: #fff">
                  <td
                    style="
                      padding-left: 10px;
                      text-align: left;
                      line-height: 30px;
                    "
                  >
                    Salary Summary
                  </td>
                  <td style="text-align: right; line-height: 30px">
                    <!-- <span style="">AED</span> -->
                  </td>
                </tr>
                <tr>
                  <td style="width: 100%; padding-right: 0px">
                    <table style="width: 100%">
                      <tr>
                        <td
                          style="padding-left: 10px; text-align: center"
                          colspan="2"
                        ></td>
                      </tr>
                      <tr style="border-bottom: 1px solid #ddd">
                        <td style="padding-left: 10px; text-align: left">
                          Total Earnings
                        </td>
                        <td style="text-align: right; padding-right: 40px">
                          {{ data.salary_and_earnings }}
                        </td>
                      </tr>
                      <tr style="border-bottom: 1px solid #ddd">
                        <td style="padding-left: 10px; text-align: left">
                          Total Deductions
                        </td>
                        <td style="text-align: right; padding-right: 40px">
                          - {{ data.deductedSalary }}
                        </td>
                      </tr>
                      <tr
                        style="font-weight: bold; border-bottom: 1px solid #ddd"
                      >
                        <td style="padding-left: 10px; text-align: left">
                          Net Salary
                        </td>
                        <td style="text-align: right; padding-right: 40px">
                          {{ numberRound(data.finalSalary) }}
                        </td>
                      </tr>
                      <tr>
                        <td
                          colspan="2"
                          style="text-align: right; padding-right: 40px"
                        >
                          {{ data.final_salary_in_words }}
                        </td>
                      </tr>

                      <tr style="font-weight: bold">
                        <td style="height: 50px"></td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td
                    style="
                      padding-left: 10px;
                      font-size: 14px;
                      line-height: 14px;
                    "
                  >
                    Note:
                    <div>
                      This payslip is generated automatically. If you notice any
                      discrepancies or missing information, please contact the
                      HR team.
                    </div>
                  </td>
                </tr>
                <tr style="font-weight: bold">
                  <td style="height: 30px"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  props: ["paySlipInput"],
  data: () => ({
    tab: null,
    Model: "Payslip",
    month: new Date().getMonth(),
    year: new Date().getFullYear().toString().slice(-2),
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
    employee: {},
    earnings: [],
    deductions: [],
  }),
  computed: {
    // PaySlipNumber() {
    //   return this.empCode + new Date().getMonth() + 1 + this.currentYear;
    // },
  },
  created() {
    let today = new Date();
    let y = today.getFullYear();
    let m = today.getMonth() + 1;

    this.currentYear = y;
    this.currentMonth = today.toLocaleString("default", { month: "long" });

    this.getDataFromApi();
    this.getCompanyDataFromApi();

    // this.loading = true;
  },
  mounted() {
    let today = new Date();
    let y = today.getFullYear();
    let m = today.getMonth() + 1;

    this.currentYear = y;
    this.currentMonth = today.toLocaleString("default", { month: "long" });

    this.getDataFromApi();
    this.getCompanyDataFromApi();
  },

  methods: {
    numberRound(val) {
      if (val) return val.toFixed(0);
    },
    can(per) {
      return this.$pagePermission.can(per, this);
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
      let [employee_id, month, year] = this.paySlipInput.split("_"); // this.$route.params.id.split("_");
      this.empCode = employee_id;

      console.log(this.paySlipInput);

      this.$axios
        .get(`/payslip/${employee_id}`, {
          params: {
            company_id: this.$auth?.user?.company?.id,
            employee_id,
            month,
            year,
          },
        })
        .then(({ data }) => {
          this.data = data;
          this.employee = data.employee;
          this.earnings = data.earnings;
          this.deductions = data.deductions;

          this.countdifference = data.earnings.length - data.deductions.length;

          // this.getdownloadLink =
          //   this.$axios.defaults.baseURL +
          //   "/donwload-payslip-pdf?company_id=" +
          //   this.$auth.user.company_id +
          //   "&employee_id=" +
          //   this.data.employee_id +
          //   "&month=" +
          //   this.data.month +
          //   "&year=" +
          //   this.data.year;

          // this.loading = false;
        });
    },
    getCompanyDataFromApi() {
      let company_id = this.$auth?.user?.company?.id;
      this.$axios.get(`company/${company_id}`).then(({ data }) => {
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
