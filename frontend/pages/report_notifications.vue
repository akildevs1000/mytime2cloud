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
        <v-container>
          <v-row>
            <label class="col-form-label pt-5"><b>When </b></label>

            <v-col cols="3">
              <v-autocomplete
                v-model="payload.frequency"
                outlined
                dense
                placeholder="Frequency"
                :items="['Daily', 'Weekly', 'Monthly', 'Yearly']"
              >
              </v-autocomplete>
              <span v-if="errors && errors.to_report" class="error--text">{{
                errors.to_report[0]
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
            <label class="col-form-label pt-5"><b>Report Type </b></label>
            <v-col cols="3" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Daily Summary"
                value="Daily Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Daily Present"
                value="Daily Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Daily Absent"
                value="Daily Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Daily Missing"
                value="Daily Missing"
              ></v-checkbox>
            </v-col>
            <v-col cols="3" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Weekly Summary"
                value="Weekly Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Weekly Present"
                value="Weekly Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Weekly Absent"
                value="Weekly Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Weekly Missing"
                value="Weekly Missing"
              ></v-checkbox>
            </v-col>

            <v-col cols="3" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Monthly Summary"
                value="Monthly Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Monthly Present"
                value="Monthly Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Monthly Absent"
                value="Monthly Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Monthly Missing"
                value="Monthly Missing"
              ></v-checkbox>
            </v-col>

            <v-col cols="3" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Yearly Summary"
                value="Yearly Summary"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Yearly Present"
                value="Yearly Present"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
                label="Yearly Absent"
                value="Yearly Absent"
              ></v-checkbox>
              <v-checkbox
                dense
                v-model="payload.report_types"
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
            <v-col cols="1" class="pa-0 mr-7">
              <v-checkbox
                dense
                v-model="payload.mediums"
                label="Whatsapp"
                value="Whatsapp"
              ></v-checkbox>
            </v-col>
            <v-col cols="1" class="pa-0 ma-0">
              <v-checkbox
                dense
                v-model="payload.mediums"
                label="SMS"
                value="SMS"
              ></v-checkbox>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="3">
              <label class="col-form-label pt-5"><b>Recipients </b></label>
            </v-col>

            <v-col cols="2" style="margin-left: -7px;">
              <v-btn x-small fab class="background mt-4" dark @click="add">
                <v-icon>
                  mdi-plus
                </v-icon>
              </v-btn>
            </v-col>
          </v-row>

          <v-row dense v-for="(d, index) in payload.recipients" :key="index">
            <v-col cols="3">
              <v-text-field
                v-model="payload.recipients[index].email"
                type="email"
                placeholder="Email"
                outlined
                dense
              ></v-text-field>
            </v-col>

            <v-col md="2">
              <v-icon color="error" @click="deleteItem(index)"
                >mdi-delete</v-icon
              >
            </v-col>
          </v-row>

          <v-divider></v-divider>
          <v-row>
            <v-col cols="12">
              <v-btn small color="primary" @click="update_setting">
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
    payload: {
      report_types: [],
      mediums: [],
      frequency: null,
      time: null,
      recipients: [{ email: "" }]
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

    add() {
      this.payload.recipients.push({ email: "" });
    },
    deleteItem(i) {
      this.payload.recipients.splice(i, 1);
    },

    update_setting() {
      let payload = this.payload;
      console.log(payload);

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
    },
    
  }
};
</script>
