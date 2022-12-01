<template>
  <v-stepper v-model="e6" vertical>
    <v-stepper-step :complete="e6 > 1" step="1">
      First Section
    </v-stepper-step>

    <v-stepper-content step="1">
      <v-row>
        <v-col cols="12" md="12">
          Shift Name <span class="error--text">*</span>
          <v-text-field
            :hide-details="!errors.name"
            :error-messages="errors.name && errors.name[0]"
            class="mt-1"
            outlined
            dense
            v-model="payload.name"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
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
                :hide-details="!errors.on_duty_time"
                v-model="payload.on_duty_time"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
                :class="payload.shift_type_id !== 1 ? '' : 'red lighten-1'"
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
          <span v-if="errors && errors.on_duty_time" class="text-danger mt-2">{{
            errors.on_duty_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="6">
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
                :hide-details="!errors.off_duty_time"
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
                :hide-details="!errors.beginning_in"
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
              <v-btn x-small color="primary" @click="beginning_in_menu = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.beginning_in_menu_ref.save(payload.beginning_in)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.beginning_in" class="text-danger mt-2">{{
            errors.beginning_in[0]
          }}</span>
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
                :hide-details="!errors.beginning_out"
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
                :hide-details="!errors.ending_in"
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
              <v-btn x-small color="primary" @click="ending_in_menu = false">
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
          <span v-if="errors && errors.ending_in" class="text-danger mt-2">{{
            errors.ending_in[0]
          }}</span>
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
                :hide-details="!errors.ending_out"
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
              <v-btn x-small color="primary" @click="ending_out_menu = false">
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
          <span v-if="errors && errors.ending_out" class="text-danger mt-2">{{
            errors.ending_out[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="3">
          <div class="text-left mb-1">
            <v-btn
              v-if="can(`shift_create`)"
              small
              color="primary"
              @click="e6 = 2"
            >
              Continue
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-stepper-content>

    <v-stepper-step :complete="e6 > 2" step="2">Second Section</v-stepper-step>

    <v-stepper-content step="2">
      <v-row>
        <v-col cols="12" md="12">
          Shift Name <span class="error--text">*</span>
          <v-text-field
            :hide-details="!errors.name"
            :error-messages="errors.name && errors.name[0]"
            class="mt-1"
            outlined
            dense
            v-model="payload.name"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
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
                :hide-details="!errors.on_duty_time"
                v-model="payload.on_duty_time"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
                :class="payload.shift_type_id !== 1 ? '' : 'red lighten-1'"
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
          <span v-if="errors && errors.on_duty_time" class="text-danger mt-2">{{
            errors.on_duty_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="6">
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
                :hide-details="!errors.off_duty_time"
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
                :hide-details="!errors.beginning_in"
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
              <v-btn x-small color="primary" @click="beginning_in_menu = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.beginning_in_menu_ref.save(payload.beginning_in)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.beginning_in" class="text-danger mt-2">{{
            errors.beginning_in[0]
          }}</span>
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
                :hide-details="!errors.beginning_out"
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
                :hide-details="!errors.ending_in"
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
              <v-btn x-small color="primary" @click="ending_in_menu = false">
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
          <span v-if="errors && errors.ending_in" class="text-danger mt-2">{{
            errors.ending_in[0]
          }}</span>
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
                :hide-details="!errors.ending_out"
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
              <v-btn x-small color="primary" @click="ending_out_menu = false">
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
          <span v-if="errors && errors.ending_out" class="text-danger mt-2">{{
            errors.ending_out[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="3">
          <div class="text-left mb-1">
            <v-btn
              v-if="can(`shift_create`)"
              small
              color="primary"
              @click="e6 = 3"
            >
              Continue
            </v-btn>
            <v-btn dark small color="background" @click="e6 = 1">
              Back
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-stepper-content>

    <v-stepper-step :complete="e6 > 3" step="3">Common Section</v-stepper-step>

    <v-stepper-content step="3">
      <v-row>
        <v-col cols="12" md="3">
          Late Time (Minutes)
          <v-text-field
            v-model="payload.late_time"
            :hide-details="!errors.late_time"
            type="number"
            dense
            outlined
            class="mt-2"
          ></v-text-field>
          <span v-if="errors && errors.late_time" class="text-danger mt-2">{{
            errors.late_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="3">
          Early Time (Minutes)
          <v-text-field
            v-model="payload.early_time"
            type="number"
            :hide-details="!errors.early_time"
            dense
            outlined
            class="mt-2"
          ></v-text-field>
          <span v-if="errors && errors.early_time" class="text-danger mt-2">{{
            errors.early_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="3">
          Minutes for Absent In
          <v-text-field
            v-model="payload.absent_min_in"
            type="number"
            :hide-details="!errors.absent_min_in"
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
            :hide-details="!errors.absent_min_out"
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
          <div class="mb-1">
            Minimum Working Hours<span class="error--text">*</span>
          </div>
          <v-text-field
            :hide-details="!errors.working_hours"
            :error="errors.working_hours"
            :error-messages="
              errors && errors.working_hours ? errors.working_hours[0] : ''
            "
            outlined
            dense
            v-model="payload.working_hours"
            x-small
            type="number"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          Overtime start after duty hours (Minutes)
          <span class="error--text">*</span>
          <v-text-field
            :hide-details="!errors.overtime_interval"
            :error-messages="
              errors.overtime_interval && errors.overtime_interval[0]
            "
            class="mt-1"
            outlined
            dense
            v-model="payload.overtime_interval"
            label=""
            type="number"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="12">
          <b>Holidays</b>
          <br />
          <v-checkbox
            style="float: left"
            class="mr-5"
            v-for="(week_day, index) in week_days"
            :key="index"
            v-model="payload.days"
            :label="week_day.label"
            :value="week_day.value"
            :error-messages="errors.days && errors.days[0]"
          ></v-checkbox>
        </v-col>

        <v-col cols="12">
          <div class="text-left">
            <v-btn
              v-if="can(`shift_create`)"
              small
              color="primary"
              @click="store_shift"
            >
              Submit
            </v-btn>
            <v-btn dark small color="background" @click="e6 = 2">
              Back
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-stepper-content>
  </v-stepper>
</template>

<script>
export default {
  data: () => ({
    manual_shift: {},

    date: null,
    menu: false,

    time_tables: [],
    shift_types: [],
    shift_last_id: "",
    shiftList: true,
    isChange: false,
    isAuto: false,

    week_days: [
      { label: "Sun", value: "Sun" },
      { label: "Mon", value: "Mon" },
      { label: "Tue", value: "Tue" },
      { label: "Wed", value: "Wed" },
      { label: "Thu", value: "Thu" },
      { label: "Fri", value: "Fri" },
      { label: "Sat", value: "Sat" }
    ],

    payload: {
      shift_type_id: "6"
    },

    // payload: {
    //   overtime: 0,
    //   shift_type_id: "",
    //   days: [],
    //   working_hours: 0
    // },

    errors: [],
    data: [],
    response: "",
    snackbar: false,

    Model: "Shift",
    e1: 1,
    e6: 1,

    loading: false,
    time_in_menu: false,
    time_out_menu: false,
    grace_time_in_menu: false,
    grace_time_out_menu: false,

    beginning_in_menu: false,
    ending_in_menu: false,

    beginning_out_menu: false,
    ending_out_menu: false,

    shift_id: [],
    payload: {
      days: []
    },

    errors: [],
    shifts: [],
    data: [],
    response: "",
    snackbar: false
  }),
  async created() {
    let options = {
      per_page: 1000,
      company_id: this.$auth.user.company.id
    };

    this.$axios.get("shift_type", { params: options }).then(({ data }) => {
      this.shift_types = data;
    });

    this.getShifts();
  },
  watch: {},
  computed: {},
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e == per || per == "/")) || u.is_master
      );
    },

    getShifts() {
      let payload = {
        params: {
          shift_type_id: 6,
          company_id: this.$auth.user.company.id
        }
      };

      this.$axios
        .get("shift_by_type", payload)
        .then(({ data }) => {
          this.shifts = data;
        })
        .catch(err => console.log(err));
    },

    store_shift() {
      this.payload.company_id = this.$auth.user.company.id;
      this.loading = true;
      this.$axios
        .post(`/shift`, this.payload)
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Shift added successfully";
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    }
  }
};
</script>

<style scoped>
.v-stepper.v-stepper--vertical.v-sheet.theme--light {
  box-shadow: none;
}
</style>
