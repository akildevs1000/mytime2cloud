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
              <v-col md="12">
                <v-text-field
                  v-model="editItems.UserID"
                  label="User Id"
                ></v-text-field>
              </v-col>
              <v-col md="12">
                <v-menu
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
                </v-menu>
              </v-col>
            </v-col>
          </v-form>
        </v-row>
      </v-container>
    </v-card-text>
    <v-card-actions>
      <v-spacer></v-spacer>
      <span class="mx-2">Render As: </span>

      <v-btn class="primary" small @click="renderByType(endpoint)">Log</v-btn>
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
export default {
  props: ["endpoint"],
  data: () => ({
    snackbar: false,
    editItems: {
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      time: null,
    },
  }),
  created() {},
  methods: {
    renderByType(type) {
      const { UserID, date, reason } = this.editItems;
      const company_id = this.$auth.user.company.id;

      if (!UserID || !date) {
        alert("System User Id and Date field is required");
        return;
      }

      let payload = {
        params: {
          date,
          UserID,
          updated_by: this.$auth.user.id,
          company_id,
          manual_entry: true,
          reason,
        },
      };

      this.$axios
        .get("/" + type, payload)
        .then(({ data }) => {
          this.loading = false;
          this.snackbar = true; //snackbar : false,
          this.response = data.message;
          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
