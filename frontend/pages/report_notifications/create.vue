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
          <h3>Automation</h3>
          <div>Dashboard / Automation</div>
        </v-col>
      </v-row>
      <v-card elevation="0" class="pa-3">
        <v-card-title>
          <label class="col-form-label"
            ><b>Create Automation </b></label
          >
          <v-spacer></v-spacer>
          <v-btn small fab color="background" dark to="/report_notifications">
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
        </v-card-title>
        <v-container>
          <v-row>
            <label class="col-form-label pt-3"><b>When </b></label>
            <v-col cols="3">
              <v-autocomplete
                :hide-details="!payload.frequency"
                v-model="payload.frequency"
                outlined
                dense
                placeholder="Frequency"
                :items="['Daily', 'Weekly', 'Monthly', 'Yearly']"
              >
              </v-autocomplete>
              <span v-if="errors && errors.frequency" class="error--text">{{
                errors.frequency[0]
              }}</span>
            </v-col>
            <v-col cols="3">
              <v-menu
                ref="menu"
                v-model="menu2"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.time"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    :hide-details="!payload.time"
                    outlined
                    dense
                    v-model="payload.time"
                    placeholder="Time"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="menu2"
                  v-model="payload.time"
                  full-width
                  @click:minute="$refs.menu.save(payload.time)"
                ></v-time-picker>
              </v-menu>
              <span v-if="errors && errors.time" class="text-danger mt-2">{{
                errors.time[0]
              }}</span>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row dense>
            <label class="col-form-label pt-5"><b>Reports</b></label>
            <v-col cols="2" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Daily Summary"
                value="Daily Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Daily Present"
                value="Daily Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Daily Absent"
                value="Daily Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Daily Missing"
                value="Daily Missing"
              ></v-checkbox>
              <span v-if="errors && errors.frequency" class="error--text">{{
                errors.reports[0]
              }}</span>
            </v-col>
            <v-col cols="2" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Weekly Summary"
                value="Weekly Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Weekly Present"
                value="Weekly Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Weekly Absent"
                value="Weekly Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Weekly Missing"
                value="Weekly Missing"
              ></v-checkbox>
            </v-col>
            <v-col cols="2" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Monthly Summary"
                value="Monthly Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Monthly Present"
                value="Monthly Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Monthly Absent"
                value="Monthly Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Monthly Missing"
                value="Monthly Missing"
              ></v-checkbox>
            </v-col>
            <v-col cols="2" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Yearly Summary"
                value="Yearly Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Yearly Present"
                value="Yearly Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Yearly Absent"
                value="Yearly Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.reports"
                label="Yearly Missing"
                value="Yearly Missing"
              ></v-checkbox>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row dense>
            <label class="col-form-label pt-5"><b>Medium </b></label>

            <v-col cols="1" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.mediums"
                label="Email"
                value="Email"
              ></v-checkbox>
            </v-col>
            <v-col cols="2" class="pa-0 mr-7">
              <v-checkbox
                dense
                v-model="payload.mediums"
                label="Whatsapp"
                value="Whatsapp"
              ></v-checkbox>
            </v-col>
            <v-col cols="12" class="pa-0 ma-0">
              <span v-if="errors && errors.mediums" class="error--text">{{
                errors.mediums[0]
              }}</span>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="3">
              <label class="col-form-label pt-5"
                ><b>To </b>(Press enter to add email address/es)</label
              >

              <v-text-field
                :hide-details="!to"
                @keyup.enter="add_to"
                v-model="to"
                type="email"
                placeholder="Email"
                outlined
                dense
              ></v-text-field>

              <v-chip
                color="primary"
                class="ma-1"
                v-for="(item, index) in payload.tos"
                :key="index"
              >
                <span class="mx-1">{{ item }}</span>
                <v-icon small @click="deleteTO(index)"
                  >mdi-close-circle-outline</v-icon
                >
              </v-chip>
              <span v-if="errors && errors.tos" class="error--text">{{
                errors.tos[0]
              }}</span>
            </v-col>

            <v-col cols="3">
              <label class="col-form-label pt-5"
                ><b>Cc </b>(Press enter to add email address/es)</label
              >
              <v-text-field
                @keyup.enter="add_cc"
                v-model="cc"
                type="email"
                placeholder="Email"
                outlined
                dense
              ></v-text-field>

              <v-chip
                color="primary"
                class="ma-1"
                v-for="(item, index) in payload.ccs"
                :key="index"
              >
                <span class="mx-1">{{ item }}</span>
                <v-icon small @click="deleteCC(index)"
                  >mdi-close-circle-outline</v-icon
                >
              </v-chip>
            </v-col>

            <v-col cols="3">
              <label class="col-form-label pt-5"
                ><b>Bcc </b>(Press enter to add email address/es)</label
              >
              <v-text-field
                @keyup.enter="add_bcc"
                v-model="bcc"
                type="email"
                placeholder="Email"
                outlined
                dense
              ></v-text-field>

              <v-chip
                color="primary"
                class="ma-1"
                v-for="(item, index) in payload.bccs"
                :key="index"
              >
                <span class="mx-1">{{ item }}</span>
                <v-icon small @click="deleteBCC(index)"
                  >mdi-close-circle-outline</v-icon
                >
              </v-chip>
            </v-col>
          </v-row>

          <v-divider></v-divider>
          <v-row>
            <v-col cols="12">
              <v-btn small color="primary" @click="store">
                Submit
              </v-btn>
            </v-col>
          </v-row>
        </v-container>
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
      reports: [],
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

    store() {
      this.payload.company_id = this.id;
      this.$axios
        .post("/report_notification", this.payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
            return;
          }

          this.snackbar = data.status;
          this.response = data.message;

        console.log("🚀 ~ file: create.vue ~ line 397 ~ .then ~ data", data)

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
