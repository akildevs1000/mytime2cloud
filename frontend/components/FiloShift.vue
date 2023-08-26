<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col cols="12" md="4" class="mt-1">
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
      <v-col cols="12" md="4">
        Minimum Working Hours <span class="error--text">*</span>

        <v-menu
          ref="working_hours_menu_ref"
          v-model="working_hours_menu"
          :close-on-content-click="false"
          :nudge-right="40"
          :return-value.sync="payload.working_hours"
          transition="scale-transition"
          offset-y
          max-width="290px"
          min-width="290px"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              append-icon="mdi-clock-outline"
              v-model="payload.working_hours"
              readonly
              :hide-details="!errors.working_hours"
              v-bind="attrs"
              v-on="on"
              dense
              outlined
              class="mt-2"
            ></v-text-field>
          </template>
          <v-time-picker
            format="24hr"
            v-if="working_hours_menu"
            v-model="payload.working_hours"
            full-width
          >
            <v-spacer></v-spacer>
            <v-btn x-small color="primary" @click="working_hours_menu = false">
              Cancel
            </v-btn>
            <v-btn
              x-small
              color="primary"
              @click="$refs.working_hours_menu_ref.save(payload.working_hours)"
            >
              OK
            </v-btn>
          </v-time-picker>
        </v-menu>
        <span v-if="errors && errors.working_hours" class="text-danger mt-2">{{
          errors.working_hours[0]
        }}</span>
      </v-col>
      <v-col cols="12" md="4">
        Overtime start after duty hours <span class="error--text">*</span>

        <v-menu
          ref="overtime_interval_menu_ref"
          v-model="overtime_interval_menu"
          :close-on-content-click="false"
          :nudge-right="40"
          :return-value.sync="payload.overtime_interval"
          transition="scale-transition"
          offset-y
          max-width="290px"
          min-width="290px"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              append-icon="mdi-clock-outline"
              v-model="payload.overtime_interval"
              readonly
              :hide-details="!errors.working_hours"
              v-bind="attrs"
              v-on="on"
              dense
              outlined
              class="mt-2"
            ></v-text-field>
          </template>
          <v-time-picker
            format="24hr"
            v-if="overtime_interval_menu"
            v-model="payload.overtime_interval"
            full-width
          >
            <v-spacer></v-spacer>
            <v-btn
              x-small
              color="primary"
              @click="overtime_interval_menu = false"
            >
              Cancel
            </v-btn>
            <v-btn
              x-small
              color="primary"
              @click="
                $refs.overtime_interval_menu_ref.save(payload.overtime_interval)
              "
            >
              OK
            </v-btn>
          </v-time-picker>
        </v-menu>
        <span
          v-if="errors && errors.overtime_interval"
          class="text-danger mt-2"
          >{{ errors.overtime_interval[0] }}</span
        >
      </v-col>
      <!-- <v-col cols="12" md="12">
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
      </v-col> -->
    </v-row>
    <v-row>
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
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["shift_type_id"],
  data: () => ({
    date: null,
    working_hours_menu: null,
    overtime_interval_menu: false,

    week_days: [
      { label: "Sun", value: "Sun" },
      { label: "Mon", value: "Mon" },
      { label: "Tue", value: "Tue" },
      { label: "Wed", value: "Wed" },
      { label: "Thu", value: "Thu" },
      { label: "Fri", value: "Fri" },
      { label: "Sat", value: "Sat" },
    ],

    payload: {
      working_hours: "09:00",
      overtime_interval: "01:00",
      days: ["Sun"],
    },

    errors: [],
    loading: false,
    response: "",
    snackbar: false,
  }),
  async created() {},
  watch: {},
  computed: {},
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    store_shift() {
      this.payload.company_id = this.$auth.user.company_id;
      this.payload.shift_type_id = this.shift_type_id;

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
    },
  },
};
</script>
