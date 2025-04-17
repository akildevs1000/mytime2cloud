<template>
  <div>
    <v-card>
      <v-card-text class="text-center">
        <table style="width: 900px; margin: auto" class="table-payslip">
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
                    <span style="float: left; height: 100px; padding-top: 30px">
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
                        style="height: 50px"
                      />
                    </span>
                    <span style="float: left"
                      ><div class="ml-3 mt-3">
                        <div style="border-top: 1px solid white">
                          <strong>{{ this.company_payload.name }}</strong>
                        </div>
                        <div class="w-10">
                          {{ this.company_payload.location }},
                        </div>
                        <div class="w-10">
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
                  <td style="text-align: right">
                    <div>
                      <div style="border-top: 1px solid white">
                        <strong
                          >Payslip No: {{ data.payslip_number }}
                          <!-- {{ empCode }}{{ month }}{{ year }} -->
                        </strong>
                      </div>
                      <div style="border-top: 1px solid white">
                        <strong> {{ currentMonth }} {{ currentYear }} </strong>
                      </div>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="border: 1px solid #ddd; padding-left: 10px">
              <table style="width: 100%">
                <tr>
                  <td>
                    <div style="font-weight: bold">Employee To :</div>
                    <span v-if="employee && employee.first_name">{{
                      employee.first_name
                    }}</span>
                    <span v-if="employee && employee.last_name">{{
                      employee.last_name
                    }}</span>

                    <div>
                      #{{ empCode }} ,
                      {{
                        (employee.department && employee.department.name) || " "
                      }}
                    </div>
                    <div></div>
                    <div>
                      {{
                        (employee.designation && employee.designation.name) ||
                        " "
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
                  border: 1px solid #ddd;
                  padding-left: 0px;
                  margin-top: 5px;
                "
              >
                <tr
                  style="
                    background-color: #4d71bf;
                    color: #fff;
                    font-weight: bold;
                  "
                >
                  <td style="padding-left: 10px; text-align: left">
                    Gross Earnings
                  </td>
                  <td style="text-align: right">
                    <!-- <span style="">AED</span> -->
                  </td>
                </tr>
                <tr>
                  <td style="width: 100%; padding-right: 20px">
                    <table style="width: 100%">
                      <tr>
                        <td
                          colspan="2"
                          style="text-align: center; font-weight: bold"
                        ></td>
                      </tr>
                      <tr v-for="(item, index) in data.earnings" :key="index">
                        <td style="text-align: left; padding-left: 10px">
                          {{ caps(item.label) }}
                        </td>
                        <td style="text-align: right">{{ item.value }}</td>
                      </tr>

                      <tr style="font-weight: bold">
                        <td
                          style="
                            width: 150px;
                            text-align: left;
                            padding-left: 10px;
                            font-weight: bold;
                          "
                        >
                          Total Earnings
                        </td>
                        <td style="text-align: right">
                          {{ data.salary_and_earnings }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr
                  style="
                    background-color: #4d71bf;
                    color: #fff;
                    font-weight: bold;
                  "
                >
                  <td style="padding-left: 10px; text-align: left">
                    Gross Deductions
                  </td>
                  <td style="text-align: right">
                    <!-- <span style="">AED</span> -->
                  </td>
                </tr>
                <tr>
                  <td style="width: 100%; padding-right: 20px">
                    <table style="width: 100%">
                      <tr>
                        <td
                          colspan="2"
                          style="text-align: center; font-weight: bold"
                        ></td>
                      </tr>
                      <tr v-for="(item, index) in data.deductions" :key="index">
                        <td style="text-align: left; padding-left: 10px">
                          {{ caps(item.label) }}
                        </td>
                        <td style="text-align: right">{{ item.value }}</td>
                      </tr>

                      <tr style="font-weight: bold">
                        <td
                          style="
                            width: 150px;
                            text-align: left;
                            padding-left: 10px;
                            font-weight: bold;
                          "
                        >
                          Total Deductions
                        </td>
                        <td style="text-align: right">
                          {{ data.deductedSalary }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr
                  style="
                    background-color: #4d71bf;
                    color: #fff;
                    font-weight: bold;
                  "
                >
                  <td style="padding-left: 10px; text-align: left">
                    Salary Summary
                  </td>
                  <td style="text-align: right">
                    <!-- <span style="">AED</span> -->
                  </td>
                </tr>
                <tr>
                  <td style="width: 100%; padding-right: 20px">
                    <table style="width: 100%">
                      <tr>
                        <td
                          style="padding-left: 10px; text-align: center"
                          colspan="2"
                        ></td>
                      </tr>
                      <tr style="">
                        <td style="padding-left: 10px; text-align: left">
                          Total Earnings
                        </td>
                        <td style="text-align: right">
                          {{ data.salary_and_earnings }}
                        </td>
                      </tr>
                      <tr style="">
                        <td style="padding-left: 10px; text-align: left">
                          Total Deductions
                        </td>
                        <td style="text-align: right">
                          - {{ data.deductedSalary }}
                        </td>
                      </tr>
                      <tr style="font-weight: bold">
                        <td style="padding-left: 10px; text-align: left">
                          Net Salary
                        </td>
                        <td style="text-align: right">
                          ({{ data.final_salary_in_words }} )
                          {{ numberRound(data.finalSalary) }}
                        </td>
                      </tr>

                      <tr style="font-weight: bold">
                        <td style="height: 60px"></td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td style="padding-left: 10px">
                    Note: This payslip is generated automatically. If you notice
                    any discrepancies or missing information, please contact the
                    HR team.
                  </td>
                </tr>
                <tr style="font-weight: bold">
                  <td style="height: 60px"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </v-card-text>
    </v-card>

    <!-- <v-card
      v-if="company_payload && company_payload.name"
      class="mx-auto"
      justify="center"
    >
      <v-card-text>
        <v-row>
          <v-col cols="4" class="reds">
            <div class="d-flex" style="border: 1px solid white">
              <v-img
                style="border-radius: 10%"
                :src="this.company_payload.logo"
                max-width="100"
                max-height="100"
              ></v-img>
              <div class="ml-3 mt-3">
                <div style="border-top: 1px solid white">
                  <strong>{{ this.company_payload.name }}</strong>
                </div>
                <div class="w-10">{{ this.company_payload.location }}</div>
                <div class="w-10">{{ this.company_payload.p_o_box_no }}</div>
              </div>
            </div>
          </v-col>
          <v-col cols="4" class="reds">
            <div class="text-center pt-2">
              <h5 class="text--grey">
                <u>PAYSLIP</u>
              </h5>
            </div>
          </v-col>
          <v-col cols="4" class="reds text-right">
            <div class="text-right">
              <div class="ml-2">
                <div style="border-top: 1px solid white">
                  <strong
                    >Payslip No: {{ data.payslip_number }}
                    
                  </strong>
                </div>
                <div style="border-top: 1px solid white">
                  <strong>Date: {{ data.date }} </strong>
                </div>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-text>
        <v-row>
          <v-col cols="6">
            <v-card outlined>
              <v-card-title> Employee Details </v-card-title>
              <v-card-text>
                <table
                  style="
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                  "
                >
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Employee Name
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      <span v-if="employee && employee.first_name">{{
                        employee.first_name
                      }}</span>
                      <span v-if="employee && employee.last_name">{{
                        employee.last_name
                      }}</span>
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Employee Id
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ empCode }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Department
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{
                        (employee.department && employee.department.name) ||
                        "---"
                      }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Designation
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{
                        (employee.designation && employee.designation.name) ||
                        "---"
                      }}
                    </td>
                  </tr>
                </table>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="6">
            <v-card outlined>
              <v-card-title> Other Details </v-card-title>
              <v-card-text>
                <table
                  style="
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                  "
                >
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Salary Month:
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ currentMonth }}, {{ currentYear }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Salary Type:
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ data.salary_type }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Presents
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ data.present }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Absent
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ data.absent }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Off
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ data.off }}
                    </td>
                  </tr>
                  <tr>
                    <th style="text-align: left; border-bottom: 1px solid #ccc">
                      Missing
                    </th>
                    <td
                      style="text-align: right; border-bottom: 1px solid #ccc"
                    >
                      {{ data.missing }}
                    </td>
                  </tr>
                </table>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="6">
            <v-card outlined>
              <v-card-title> Earning Details </v-card-title>
              <v-card-text>
                <table
                  style="
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                  "
                >
                  <thead>
                    <tr>
                      <th
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        Earnings
                      </th>
                      <th
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        Amount
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in data.earnings" :key="index">
                      <td
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        {{ caps(item.label) }}
                      </td>
                      <td
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        {{ item.value }}
                      </td>
                    </tr>
                    <tr>
                      <th
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        <strong>Total Earnings</strong>
                      </th>
                      <th
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        {{ data.salary_and_earnings }}
                      </th>
                    </tr>
                  </tbody>
                </table>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6">
            <v-card outlined>
              <v-card-title> Deduction Details </v-card-title>
              <v-card-text>
                <table
                  style="
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                  "
                >
                  <thead>
                    <tr>
                      <th
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        Deductions
                      </th>
                      <th
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        Amount
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in data.deductions" :key="index">
                      <td
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        {{ caps(item.label) }}
                      </td>
                      <td
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        {{ item.value }}
                      </td>
                    </tr>
                    <tr v-for="n in countdifference" :key="n">
                      <th
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                        scope="row"
                      >
                        &nbsp;
                      </th>
                      <td
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        &nbsp;
                      </td>
                    </tr>
                    <tr>
                      <th
                        style="
                          text-align: left;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                        scope="row"
                      >
                        Total Deductions
                      </th>
                      <th
                        style="
                          text-align: right;
                          padding: 5px;
                          border-bottom: 1px solid #ccc;
                        "
                      >
                        {{ data.deductedSalary }}
                      </th>
                    </tr>

                   
                  </tbody>
                </table>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12">
            <v-card outlined>
              <v-card-text class="text-right">
                <strong>Net Salary</strong>:
                {{ numberRound(this.data.earnedSubTotal) }}
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card> 

    <Preloader v-else />-->
  </div>
</template>

<script>
export default {
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
    numberRound(val) {
      if (val) return val.toFixed(2);
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
      let [employee_id, month, year] = this.$route.params.id.split("_");
      this.empCode = employee_id;

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
