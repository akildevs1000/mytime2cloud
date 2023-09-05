<template>
  <div>
    <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
      {{ response }}
    </v-snackbar>
    <v-card-text>
      <v-container>
        <v-row>
          <v-form ref="form" v-model="valid" lazy-validation>
            <v-col md="12">
              <v-autocomplete
                class="mt-5"
                placeholder="Employee Device Id"
                v-model="editItems.UserIDs"
                :items="employees"
                :item-text="`name_with_id`"
                item-value="system_user_id"
                dense
                outlined
                multiple
              >
              </v-autocomplete>
              <!-- <v-text-field
                  v-model="editItems.UserID"
                  label="User Id"
                ></v-text-field> -->
            </v-col>
            <v-col md="12">
              <DateRangePickerCommon @selected-dates="handleDatesFilter" />
              <!-- <v-menu
                  ref="menu"
                  v-model="menu"
                  :close-on-content-click="false"
                  :return-value.sync="date"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="editItems.date"
                      label="Date"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker v-model="editItems.date" no-title scrollable>
                    <v-spacer></v-spacer>
                    <v-btn text color="primary" @click="menu = false">
                      Cancel
                    </v-btn>
                    <v-btn
                      text
                      color="primary"
                      @click="$refs.menu.save(editItems.date)"
                    >
                      Save
                    </v-btn>
                  </v-date-picker>
                </v-menu> -->
            </v-col>
            <v-col cols="12">
              <ul>
                <li v-for="(item, index) in result" :key="index">
                  {{ item }}
                </li>
              </ul>
            </v-col>
          </v-form>
        </v-row>
      </v-container>
    </v-card-text>
    <v-card-actions>
      <v-spacer></v-spacer>
      <span class="mx-2">Render As: </span>

      <v-btn
        :loading="loading"
        class="primary"
        small
        @click="renderByType(endpoint)"
        >Log</v-btn
      >
      <v-btn class="background" dark small @click="renderByType(`render_off`)">
        Week Off</v-btn
      >
      <v-btn class="error" small @click="renderByType(`render_absent`)"
        >Absent</v-btn
      >
    </v-card-actions>
  </div>
</template>

<script>
import DateRangePickerCommon from "./Snippets/DateRangePickerCommon.vue";

export default {
  props: ["endpoint"],
  data: () => ({
    snackbar: false,
    loading: false,
    response: null,
    result: [],
    editItems: {
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",

      UserIDs: [],
      dates: [],
      time: null,
    },
  }),
  computed: {
    employees() {
      return this.$store.state.employees.map((e) => ({
        system_user_id: e.system_user_id,
        first_name: e.first_name,
        last_name: e.last_name,
        display_name: e.display_name,
        name_with_id: `${e.first_name} - ${e.system_user_id}`,
      }));
    },
  },
  created() {},
  methods: {
    handleDatesFilter(dates) {
      this.editItems.dates = dates;
    },
    renderByType(type) {
      const { UserID, date, reason, UserIDs, dates } = this.editItems;
      const company_id = this.$auth.user.company_id;
      if (!UserIDs.length || !dates.length) {
        alert("System User Id and Date field is required");
        return;
      }
      this.loading = true;
      let payload = {
        params: {
          date,
          UserID,
          updated_by: this.$auth.user.id,
          company_id,
          manual_entry: true,
          reason,
          UserIDs,
          dates,
        },
      };
      console.log(payload);
      // return;
      let endpoint = "/" + type;
      if (type != "render_off" && type != "render_absent") {
        endpoint = "render_logs";
      }
      this.$axios
        .get(endpoint, payload)
        .then(({ data }) => {
          console.log(data);
          this.loading = false;
          // this.snackbar = true; //snackbar : false,
          this.response =
            endpoint !== "render_logs" ? data.message : (this.result = data);
          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
  },
  components: { DateRangePickerCommon },
};
</script>
