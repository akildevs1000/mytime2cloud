<template>
  <div v-if="can(`shift_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5">
      <v-col cols="3">
        <h3>Schedule</h3>
        <div>Dashboard / Schedule</div>
      </v-col>
      <v-col cols="12">
        <v-card elevation="0" class="px-5 pb-5">
          <v-card-title>
            <label class="col-form-label"><b>Schedule List </b></label>
            <v-spacer></v-spacer>
          </v-card-title>
          <v-card-title>
            <table style="width: 100%">
              <tr style="font-size: 15px">
                <td style="width: 130px; text-align: center">
                  <label class="col-form-label"><b>Date</b></label>
                </td>
                <td style="width: 130px; text-align: center">
                  <label class="col-form-label"><b>Day</b></label>
                </td>
                <td style="max-width: 150px; text-align: center">
                  <label class="col-form-label"><b>Shifts</b></label>
                </td>
                <td style="text-align: center">
                  <label class="col-form-label"><b>From</b></label>
                </td>
                <td style="text-align: center">
                  <label class="col-form-label"><b>To</b></label>
                </td>
              </tr>
              <tr
                v-for="(item, index) in days"
                :key="index"
                style="text-align: center; font-size: 15px"
              >
                <td style="width: 200px; text-align: center">
                  <label class="col-form-label"
                    ><b>{{ date[index] }}</b></label
                  >
                </td>
                <td style="width: 150px; text-align: center">
                  <label class="col-form-label"
                    ><b>{{ item }}</b></label
                  >
                </td>
                <td style="width: 400px">
                  <v-select
                    class="mx-5 py-2"
                    :items="shift"
                    dense
                    outlined
                    item-text="name"
                    item-value="id"
                    :v-model="shift_type[index]"
                    placeholder="Select"
                    :hide-details="true"
                  ></v-select>
                </td>
                <td style="max-width: 150px; text-align: center">
                  <label class="col-form-label">22:00</label>
                </td>
                <td style="max-width: 150px; text-align: center">
                  <label class="col-form-label">08:30</label>
                </td>
              </tr>
            </table>
          </v-card-title>
          <v-btn color="primary" class="ml-4" dark> Submit </v-btn>
        </v-card>
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    options: {},
    Model: "Shift",
    endpoint: "shift",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      { text: "Day" },
      { text: "Shift Type" },
      { text: "From" },
      { text: "To" },
    ],
    response: "",
    days: [],
    date: [],
    data: [],
    shift: [],
    shift_type: "",
    errors: [],
  }),

  watch: {},
  created() {
    this.loading = true;
    this.get_days();
    this.get_shifts();
  },

  methods: {
    get_days() {
      let today = new Date();
      for (let i = 0; i < 7; i++) {
        let day = new Date(today);
        day.setDate(today.getDate() + i);
        let now = day.toLocaleDateString("en-US", { weekday: "short" });
        this.days.push(now);
        this.date.push(day.toISOString().split("T")[0]);
      }
    },

    get_shifts() {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get("shift", { params: options }).then(({ data }) => {
        this.shift = data.data;

        let obj = {
          id: 7,
          name: "Holiday",
        };

        console.log(this.shift);
      });
    },
    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
  },
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
  padding: 3px;
}

tr:nth-child(even) {
  /* background-color: #e9e9e9; */
}
</style>
