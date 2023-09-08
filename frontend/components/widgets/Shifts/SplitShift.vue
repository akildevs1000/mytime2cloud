<template>
  <v-stepper v-model="e6" vertical elevation="0">
    <v-stepper-step :complete="e6 > 1" step="1">
      Common Section
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
        <v-col cols="12" md="2">
          <v-menu
            ref="late_time_menu_ref"
            v-model="late_time_menu"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload.late_time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Late Time
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload.late_time"
                :hide-details="!errors.late_time"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="late_time_menu"
              v-model="payload.late_time"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="late_time_menu = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.late_time_menu_ref.save(payload.late_time)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.late_time" class="text-danger mt-2">{{
            errors.late_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="early_time_menu_ref"
            v-model="early_time_menu"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload.early_time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Early Time
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload.early_time"
                :hide-details="!errors.early_time"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="early_time_menu"
              v-model="payload.early_time"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="early_time_menu = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.early_time_menu_ref.save(payload.early_time)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.early_time" class="text-danger mt-2">{{
            errors.early_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="absent_min_in_menu_ref"
            v-model="absent_min_in_menu"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload.absent_min_in"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Absent In
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload.absent_min_in"
                :hide-details="!errors.absent_min_in"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="absent_min_in_menu"
              v-model="payload.absent_min_in"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn
                x-small
                color="primary"
                @click="absent_min_in_menu = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.absent_min_in_menu_ref.save(payload.absent_min_in)
                "
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span
            v-if="errors && errors.absent_min_in"
            class="text-danger mt-2"
            >{{ errors.absent_min_in[0] }}</span
          >
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="absent_min_out_menu_ref"
            v-model="absent_min_out_menu"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload.absent_min_out"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Absent Out
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload.absent_min_out"
                :hide-details="!errors.absent_min_out"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="absent_min_out_menu"
              v-model="payload.absent_min_out"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn
                x-small
                color="primary"
                @click="absent_min_out_menu = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.absent_min_out_menu_ref.save(payload.absent_min_out)
                "
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span
            v-if="errors && errors.absent_min_out"
            class="text-danger mt-2"
            >{{ errors.absent_min_out[0] }}</span
          >
        </v-col>
        <v-col cols="12" md="2">
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
              <v-btn
                x-small
                color="primary"
                @click="working_hours_menu = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.working_hours_menu_ref.save(payload.working_hours)
                "
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span
            v-if="errors && errors.working_hours"
            class="text-danger mt-2"
            >{{ errors.working_hours[0] }}</span
          >
        </v-col>
        <v-col cols="12" md="2">
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
                  $refs.overtime_interval_menu_ref.save(
                    payload.overtime_interval
                  )
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
        <v-col cols="12">
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

    <v-stepper-step :complete="e6 > 2" step="2">
      Second Section
    </v-stepper-step>

    <v-stepper-content step="2">
      <v-row>
        <v-col cols="12" md="2">
          <v-menu
            ref="time_in_menu_ref1"
            v-model="time_in_menu1"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload1.on_duty_time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              On Duty Time
              <v-text-field
                append-icon="mdi-clock-outline"
                :hide-details="!errors.on_duty_time"
                v-model="payload1.on_duty_time"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
                :class="payload1.shift_type_id !== 1 ? '' : 'red lighten-1'"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="time_in_menu1"
              v-model="payload1.on_duty_time"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="time_in_menu1 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.time_in_menu_ref1.save(payload1.on_duty_time)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.on_duty_time" class="text-danger mt-2">{{
            errors.on_duty_time[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="time_out_menu_ref1"
            v-model="time_out_menu1"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload1.off_duty_time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Off Duty Time
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload1.off_duty_time"
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
              v-if="time_out_menu1"
              v-model="payload1.off_duty_time"
              :min="payload1.on_duty_time"
              max="23:59"
              full-width
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="time_out_menu1 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.time_out_menu_ref1.save(payload1.off_duty_time)"
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
        <v-col cols="12" md="2">
          <v-menu
            ref="beginning_in_menu_ref1"
            v-model="beginning_in_menu1"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload1.beginning_in"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Beginning In
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload1.beginning_in"
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
              v-if="beginning_in_menu1"
              v-model="payload1.beginning_in"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn
                x-small
                color="primary"
                @click="beginning_in_menu1 = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.beginning_in_menu_ref1.save(payload1.beginning_in)
                "
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.beginning_in" class="text-danger mt-2">{{
            errors.beginning_in[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="beginning_out_menu_ref1"
            v-model="beginning_out_menu1"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload1.beginning_out"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Beginning Out
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload1.beginning_out"
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
              v-if="beginning_out_menu1"
              v-model="payload1.beginning_out"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn
                x-small
                color="primary"
                @click="beginning_out_menu1 = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.beginning_out_menu_ref1.save(payload1.beginning_out)
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
        <v-col cols="12" md="2">
          <v-menu
            ref="ending_in_menu_ref1"
            v-model="ending_in_menu1"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload1.ending_in"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Ending In
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload1.ending_in"
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
              v-if="ending_in_menu1"
              v-model="payload1.ending_in"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="ending_in_menu1 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.ending_in_menu_ref1.save(payload1.ending_in)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.ending_in" class="text-danger mt-2">{{
            errors.ending_in[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="ending_out_menu_ref1"
            v-model="ending_out_menu1"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload1.ending_out"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Ending Out
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload1.ending_out"
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
              v-if="ending_out_menu1"
              v-model="payload1.ending_out"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="ending_out_menu1 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.ending_out_menu_ref1.save(payload1.ending_out)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.ending_out" class="text-danger mt-2">{{
            errors.ending_out[0]
          }}</span>
        </v-col>
        <v-col cols="12">
          <div class="text-left mb-1">
            <v-btn
              v-if="can(`shift_create`)"
              small
              color="primary"
              @click="e6 = 3"
            >
              Continue
            </v-btn>
            <v-btn dark small color="background" @click="e6 = 1"> Back </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-stepper-content>

    <v-stepper-step :complete="e6 > 3" step="3">Third Section</v-stepper-step>

    <v-stepper-content step="3">
      <v-row>
        <v-col cols="12" md="2">
          <v-menu
            ref="time_in_menu_ref2"
            v-model="time_in_menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload2.on_duty_time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              On Duty Time
              <v-text-field
                append-icon="mdi-clock-outline"
                :hide-details="!errors.on_duty_time"
                v-model="payload2.on_duty_time"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
                :class="payload2.shift_type_id !== 1 ? '' : 'red lighten-1'"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="time_in_menu2"
              v-model="payload2.on_duty_time"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="time_in_menu2 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.time_in_menu_ref2.save(payload2.on_duty_time)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span
            v-if="errors1 && errors1.on_duty_time"
            class="text-danger mt-2"
            >{{ errors.on_duty_time[0] }}</span
          >
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="time_out_menu_ref2"
            v-model="time_out_menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload2.off_duty_time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Off Duty Time
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload2.off_duty_time"
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
              v-if="time_out_menu2"
              v-model="payload2.off_duty_time"
              :min="payload2.on_duty_time"
              max="23:59"
              full-width
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="time_out_menu2 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.time_out_menu_ref2.save(payload2.off_duty_time)"
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
        <v-col cols="12" md="2">
          <v-menu
            ref="beginning_in_menu_ref2"
            v-model="beginning_in_menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload2.beginning_in"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Beginning In
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload2.beginning_in"
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
              v-if="beginning_in_menu2"
              v-model="payload2.beginning_in"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn
                x-small
                color="primary"
                @click="beginning_in_menu2 = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.beginning_in_menu_ref2.save(payload2.beginning_in)
                "
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.beginning_in" class="text-danger mt-2">{{
            errors.beginning_in[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="beginning_out_menu_ref2"
            v-model="beginning_out_menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload2.beginning_out"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Beginning Out
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload2.beginning_out"
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
              v-if="beginning_out_menu2"
              v-model="payload2.beginning_out"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn
                x-small
                color="primary"
                @click="beginning_out_menu2 = false"
              >
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="
                  $refs.beginning_out_menu_ref2.save(payload2.beginning_out)
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
        <v-col cols="12" md="2">
          <v-menu
            ref="ending_in_menu_ref2"
            v-model="ending_in_menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload2.ending_in"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Ending In
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload2.ending_in"
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
              v-if="ending_in_menu2"
              v-model="payload2.ending_in"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="ending_in_menu2 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.ending_in_menu_ref2.save(payload2.ending_in)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors1 && errors1.ending_in" class="text-danger mt-2">{{
            errors.ending_in[0]
          }}</span>
        </v-col>
        <v-col cols="12" md="2">
          <v-menu
            ref="ending_out_menu_ref2"
            v-model="ending_out_menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="payload2.ending_out"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              Ending Out
              <v-text-field
                append-icon="mdi-clock-outline"
                v-model="payload2.ending_out"
                :hide-details="!errors1.ending_out"
                readonly
                v-bind="attrs"
                v-on="on"
                dense
                outlined
                class="mt-2"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="ending_out_menu2"
              v-model="payload2.ending_out"
              full-width
              format="24hr"
            >
              <v-spacer></v-spacer>
              <v-btn x-small color="primary" @click="ending_out_menu2 = false">
                Cancel
              </v-btn>
              <v-btn
                x-small
                color="primary"
                @click="$refs.ending_out_menu_ref2.save(payload2.ending_out)"
              >
                OK
              </v-btn>
            </v-time-picker>
          </v-menu>
          <span v-if="errors && errors.ending_out" class="text-danger mt-2">{{
            errors.ending_out[0]
          }}</span>
        </v-col>
        <v-col cols="12">
          <div class="text-left mb-1">
            <v-btn
              v-if="can(`shift_create`)"
              small
              color="primary"
              @click="submit"
            >
              Submit
            </v-btn>
            <v-btn dark small color="background" @click="e6 = 2"> Back </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-stepper-content>
  </v-stepper>
</template>

<script>
export default {
  props: ["shift_type_id"],

  data: () => ({
    date: null,
    menu: false,

    date1: null,
    menu1: false,

    e6: 1,
    week_days: [
      { label: "Sun", value: "Sun" },
      { label: "Mon", value: "Mon" },
      { label: "Tue", value: "Tue" },
      { label: "Wed", value: "Wed" },
      { label: "Thu", value: "Thu" },
      { label: "Fri", value: "Fri" },
      { label: "Sat", value: "Sat" },
    ],

    loading: false,

    late_time_menu: false,
    early_time_menu: false,
    absent_min_in_menu: false,
    absent_min_out_menu: false,
    working_hours_menu: false,
    overtime_interval_menu: false,

    time_in_menu1: false,
    time_out_menu1: false,
    beginning_in_menu1: false,
    beginning_out_menu1: false,
    ending_in_menu1: false,
    ending_out_menu1: false,

    time_in_menu2: false,
    time_out_menu2: false,
    beginning_in_menu2: false,
    beginning_out_menu2: false,
    ending_in_menu2: false,
    ending_out_menu2: false,

    payload: {
      name: "",
      late_time: "00:15",
      early_time: "00:15",
      absent_min_in: "01:00",
      absent_min_out: "01:00",
      working_hours: "09:00",
      overtime_interval: "00:30",
    },

    payload1: {
      on_duty_time: "09:00",
      off_duty_time: "14:00",

      beginning_in: "06:00",
      beginning_out: "17:00",

      ending_in: "06:00",
      ending_out: "13:00",
    },

    payload2: {
      on_duty_time: "17:00",
      off_duty_time: "21:00",

      beginning_in: "16:00",
      beginning_out: "18:00",

      ending_in: "20:00",
      ending_out: "23:59",
    },

    errors: [],
    errors1: [],

    shifts: [],
    data: [],
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
    submit() {
      let payload = {
        ...this.payload,
        shift_type_id: this.shift_type_id,
        company_id: this.$auth.user.company_id,

        on_duty_time:
          this.payload1.on_duty_time + "|" + this.payload2.on_duty_time,
        off_duty_time:
          this.payload1.off_duty_time + "|" + this.payload2.off_duty_time,
        beginning_in:
          this.payload1.beginning_in + "|" + this.payload2.beginning_in,
        beginning_out:
          this.payload1.beginning_out + "|" + this.payload2.beginning_out,
        ending_in: this.payload1.ending_in + "|" + this.payload2.ending_in,
        ending_out: this.payload1.ending_out + "|" + this.payload2.ending_out,
      };

      this.loading = true;
      this.$axios
        .post(`/shift`, payload)
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
            return;
          }
          this.snackbar = false;
          this.response = "Shift added successfully";
          setTimeout(() => {
            this.$router.push("/shift");
          }, 1000);
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
  },
};
</script>
