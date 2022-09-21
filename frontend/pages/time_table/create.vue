<template>
  <div v-if="can(`time_table_create`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-10">
      <v-col md="6">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }} / Create</div>
      </v-col>
      <v-col md="6">
        <div class="text-right">
          <v-btn small to="/time_table" color="primary">
            <v-icon small>mdi-arrow-left</v-icon>&nbsp;Back
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-card>
      <v-card flat>
        <v-card-text>
          <v-row>
            <v-col md="12">
              <h3>Create {{ Model }}</h3>
            </v-col>
            <v-col cols="12" md="12">
              Name
              <v-text-field
                v-model="payload.name"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
              <span v-if="errors && errors.name" class="text-danger mt-2">{{
                errors.name[0]
              }}</span>
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="time_in_menu_ref"
                v-model="time_in_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.on_duty_time"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  On Duty Time
                  <v-text-field
                    v-model="payload.on_duty_time"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="time_in_menu"
                  v-model="payload.on_duty_time"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn x-small color="primary" @click="time_in_menu = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="$refs.time_in_menu_ref.save(payload.on_duty_time)"
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.on_duty_time"
                class="text-danger mt-2"
                >{{ errors.on_duty_time[0] }}</span
              >
            </v-col>
            <v-col cols="12" md="3">
              <v-menu
                ref="time_out_menu_ref"
                v-model="time_out_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.off_duty_time"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Off Duty Time
                  <v-text-field
                    v-model="payload.off_duty_time"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  format="24hr"
                  v-if="time_out_menu"
                  v-model="payload.off_duty_time"
                  full-width
                >
                  <v-spacer></v-spacer>
                  <v-btn x-small color="primary" @click="time_out_menu = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="$refs.time_out_menu_ref.save(payload.off_duty_time)"
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.off_duty_time"
                class="text-danger mt-2"
                >{{ errors.off_duty_time[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              Late Time (Minutes)
              <v-text-field
                v-model="payload.late_time"
                label=""
                type="number"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
              <span
                v-if="errors && errors.late_time"
                class="text-danger mt-2"
                >{{ errors.late_time[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              Early Time (Minutes)
              <v-text-field
                v-model="payload.early_time"
                type="number"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
              <span
                v-if="errors && errors.early_time"
                class="text-danger mt-2"
                >{{ errors.early_time[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="beginning_in_menu_ref"
                v-model="beginning_in_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.beginning_in"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Beginning In
                  <v-text-field
                    v-model="payload.beginning_in"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="beginning_in_menu"
                  v-model="payload.beginning_in"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn
                    x-small
                    color="primary"
                    @click="beginning_in_menu = false"
                  >
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="
                      $refs.beginning_in_menu_ref.save(payload.beginning_in)
                    "
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.beginning_in"
                class="text-danger mt-2"
                >{{ errors.beginning_in[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="beginning_out_menu_ref"
                v-model="beginning_out_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.beginning_out"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Beginning Out
                  <v-text-field
                    v-model="payload.beginning_out"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="beginning_out_menu"
                  v-model="payload.beginning_out"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn
                    x-small
                    color="primary"
                    @click="beginning_out_menu = false"
                  >
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="
                      $refs.beginning_out_menu_ref.save(payload.beginning_out)
                    "
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.beginning_out"
                class="text-danger mt-2"
                >{{ errors.beginning_out[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="ending_in_menu_ref"
                v-model="ending_in_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.ending_in"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Ending In
                  <v-text-field
                    v-model="payload.ending_in"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="ending_in_menu"
                  v-model="payload.ending_in"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn
                    x-small
                    color="primary"
                    @click="ending_in_menu = false"
                  >
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="$refs.ending_in_menu_ref.save(payload.ending_in)"
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.ending_in"
                class="text-danger mt-2"
                >{{ errors.ending_in[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="ending_out_menu_ref"
                v-model="ending_out_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.ending_out"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Ending Out
                  <v-text-field
                    v-model="payload.ending_out"
                    label=""
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="ending_out_menu"
                  v-model="payload.ending_out"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn
                    x-small
                    color="primary"
                    @click="ending_out_menu = false"
                  >
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="$refs.ending_out_menu_ref.save(payload.ending_out)"
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.ending_out"
                class="text-danger mt-2"
                >{{ errors.ending_out[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              Minutes for Absent In
              <v-text-field
                v-model="payload.absent_min_in"
                type="number"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
              <span
                v-if="errors && errors.absent_min_in"
                class="text-danger mt-2"
                >{{ errors.absent_min_in[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              Minutes for Absent Out
              <v-text-field
                v-model="payload.absent_min_out"
                type="number"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
              <span
                v-if="errors && errors.absent_min_out"
                class="text-danger mt-2"
                >{{ errors.absent_min_out[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="break_time_start_menu_ref"
                v-model="break_time_start_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.break_time_start"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Break Start
                  <v-text-field
                    v-model="payload.break_time_start"
                    label=""
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="break_time_start_menu"
                  v-model="payload.break_time_start"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn
                    x-small
                    color="primary"
                    @click="break_time_start_menu = false"
                  >
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="
                      $refs.break_time_start_menu_ref.save(
                        payload.break_time_start
                      )
                    "
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.break_time_start"
                class="text-danger mt-2"
                >{{ errors.break_time_start[0] }}</span
              >
            </v-col>

            <v-col cols="12" md="3">
              <v-menu
                ref="break_time_end_menu_ref"
                v-model="break_time_end_menu"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="payload.break_time_end"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  Break End
                  <v-text-field
                    v-model="payload.break_time_end"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    outlined
                    class="mt-2"
                  ></v-text-field>
                </template>
                <v-time-picker
                  v-if="break_time_end_menu"
                  v-model="payload.break_time_end"
                  full-width
                  format="24hr"
                >
                  <v-spacer></v-spacer>
                  <v-btn
                    x-small
                    color="primary"
                    @click="break_time_end_menu = false"
                  >
                    Cancel
                  </v-btn>
                  <v-btn
                    x-small
                    color="primary"
                    @click="
                      $refs.break_time_end_menu_ref.save(payload.break_time_end)
                    "
                  >
                    OK
                  </v-btn>
                </v-time-picker>
              </v-menu>
              <span
                v-if="errors && errors.break_time_end"
                class="text-danger mt-2"
                >{{ errors.break_time_end[0] }}</span
              >
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <div class="text-right">
                <v-btn
                  v-if="can(`time_table_create`)"
                  small
                  :loading="loading"
                  color="primary"
                  @click="store_schedule"
                >
                  Submit
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    Model: "Time Slot ",

    hours: 1,
    ticksLabels: [],

    week_days: [
      { label: "Sun", value: "Sunday" },
      { label: "Mon", value: "Monday" },
      { label: "Tue", value: "Tuesday" },
      { label: "Wed", value: "Wednesday" },
      { label: "Thu", value: "Thursday" },
      { label: "Fri", value: "Friday" },
      { label: "Sat", value: "Saturday" },
    ],
    loading: false,
    time_in_menu: false,
    time_out_menu: false,
    grace_time_in_menu: false,
    grace_time_out_menu: false,

    beginning_in_menu: false,
    ending_in_menu: false,

    beginning_out_menu: false,
    ending_out_menu: false,

    break_time_start_menu: false,
    break_time_end_menu: false,

    payload: {
      name: null,
      count_as_workday: 0,
      break_time_start_menu: false,
      break_time_end_menu: false,
      on_duty_time: null,
      off_duty_time: null,
      late_time: null,
      early_time: null,
      beginning_in: null,
      ending_in: null,
      beginning_out: null,
      ending_out: null,
      count_as_minute: "0",
      absent_min_in: null,
      absent_min_out: null,
    },

    errors: [],
    data: [],
    response: "",
    snackbar: false,
  }),
  async created() {
    for (let i = 1; i <= 24; i++) {
      this.ticksLabels.push(`${i}`);
    }
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    store_schedule() {
      let payload = this.payload;
      payload.company_id = this.$auth.user.company.id;
      this.loading = true;

      this.$axios
        .post(`/time_table`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Time Slot added successfully";
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
  },
};
</script>
