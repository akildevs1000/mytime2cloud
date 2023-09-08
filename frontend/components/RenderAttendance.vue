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
                <template v-if="employees.length" #prepend-item>
                  <v-list-item @click="toggleEmployeeSelection">
                    <v-list-item-action>
                      <v-checkbox
                        @click="toggleEmployeeSelection"
                        v-model="selectAllEmployee"
                        :indeterminate="isIndeterminateEmployee"
                        :true-value="true"
                        :false-value="false"
                      ></v-checkbox>
                    </v-list-item-action>
                    <v-list-item-content>
                      <v-list-item-title>
                        {{ selectAllEmployee ? "Unselect All" : "Select All" }}
                      </v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </template>
                <template v-slot:selection="{ item, index }">
                  <span v-if="index === 0 && editItems.UserIDs.length == 1"
                    >{{ item.first_name }} {{ item.last_name }}</span
                  >
                  <span
                    v-else-if="
                      index === 1 &&
                      editItems.UserIDs.length == employees.length
                    "
                    class=" "
                  >
                    All Selected
                  </span>
                  <span v-else-if="index === 1" class=" ">
                    Selected {{ editItems.UserIDs.length }} Employee(s)
                  </span>
                </template>
                <!-- <template v-slot:selection="{ item, index }">

                    <span v-if="index === 0">{{ item.first_name }} {{ item.last_name }}</span>

                    <span v-else-if="index === 1" class="grey--text text-caption">
                      (+{{ editedItem.employees.length - 1 }} others)
                    </span>
                  </template> -->
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
              <v-card outlined>
                <ul style="height: 150px; overflow-y: scroll">
                  <li v-for="(item, index) in result" :key="index">
                    {{ index }}
                    <ul>
                      <li v-for="(childItem, index) in item" :key="index">
                        {{ childItem }}
                      </li>
                    </ul>
                  </li>
                </ul>
              </v-card>
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
    valid: false,
    snackbar: false,
    loading: false,
    response: null,
    selectAllEmployee: false,

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
    isIndeterminateEmployee() {
      return (
        this.editItems.UserIDs.length > 0 &&
        this.editItems.UserIDs.length < this.employees.length
      );
    },
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
  watch: {
    selectAllEmployee(value) {
      if (value) {
        this.editItems.UserIDs = this.employees.map((e) => e.system_user_id);
      } else {
        this.editItems.UserIDs = [];
      }
    },
  },
  created() {},
  methods: {
    toggleEmployeeSelection() {
      this.selectAllEmployee = !this.selectAllEmployee;
    },
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
          employee_ids: UserIDs,
          dates,
        },
      };

      // return;
      let endpoint = "/" + type;
      if (type != "render_off" && type != "render_absent") {
        endpoint = "render_logs";
      }
      this.$axios
        .get(endpoint, payload)
        .then(({ data }) => {
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
