<template>
  <div v-if="can('setting_company_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!preloader">
      <v-row class="mt-5 mb-5">
        <v-col cols="10">
          <h3>Report Notifications</h3>
          <div>Dashboard / Report Notifications</div>
        </v-col>
      </v-row>
      <v-card elevation="0" class="pa-3">
        <table>
          <tr>
            <th style="width:200px;">
              <label class="col-form-label"><b>When </b></label>
            </th>
            <th>
              <label class="col-form-label"><b>Report Type </b></label>
            </th>
            <th>
              <label class="col-form-label"><b>Medium </b></label>
            </th>
            <th>
              <label class="col-form-label"><b>To </b></label>
            </th>
            <th>
              <label class="col-form-label"><b>Cc </b></label>
            </th>
            <th>
              <label class="col-form-label"><b>Bcc </b></label>
            </th>
          </tr>
          <tr>
            <td>Daily at 09:30</td>
            <td style="max-width:250px;">
                <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Reports
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">Daily Summary</div>
              </v-tooltip>
            </td>
            <td>Email, Whatsapp</td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
          </tr>
          <tr>
            <td>Daily at 09:30</td>
            <td style="max-width:50px;">
              <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Reports
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">Daily Summary</div>
              </v-tooltip>
            </td>
            <td>Email, Whatsapp</td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
          </tr>
          <tr>
            <td>Daily at 09:30</td>
            <td style="max-width:50px;">
              <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Reports
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">Daily Summary</div>
              </v-tooltip>
            </td>
            <td>Email, Whatsapp</td>
            <td style="max-width:50px;">
             <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip>
            </td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
          </tr>
          <tr>
            <td>Daily at 09:30</td>
            <td style="max-width:50px;">
              <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Reports
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">Daily Summary</div>
              </v-tooltip>
            </td>
            <td>Email</td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
            <td>  <v-tooltip top color="background">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small color="primary" dark v-bind="attrs" v-on="on">
                    Emails
                  </v-btn>
                </template>

                <div v-for="(n, i) in 16" :key="i" class="">francisgill1000@gmail.com</div>
              </v-tooltip></td>
          </tr>
        </table>
      </v-card>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    color: "primary",
    e1: 1,
    menu2: false,
    preloader: false,
    loading: false,
    response: false,
    id: "",
    snackbar: false,
    to: "",
    cc: "",
    bcc: "",
    payload: {
      report_types: [],
      mediums: [],
      frequency: null,
      time: null,
      tos: [],
      ccs: [],
      bccs: []
    },

    errors: []
  }),

  created() {
    this.preloader = false;
    this.id = this.$auth?.user?.company?.id;
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    add_to() {
      this.payload.tos.push(this.to);
      this.to = "";
    },
    add_cc() {
      this.payload.ccs.push(this.cc);
      this.cc = "";
    },
    add_bcc() {
      this.payload.bccs.push(this.bcc);
      this.bcc = "";
    },
    deleteTO(i) {
      this.payload.tos.splice(i, 1);
    },

    deleteCC(i) {
      this.payload.ccs.splice(i, 1);
    },

    deleteBCC(i) {
      this.payload.bccs.splice(i, 1);
    },

    update_setting() {
      let payload = this.payload;

      this.$axios
        .post("/report_notifications", this.payload)
        .then(({ data }) => {
          console.log(data);
          return;
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = model + " updated successfully";
            if (model == "Company") {
              location.reload();
            }
          }
        })
        .catch(e => console.log(e));
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
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
