 <template>
  <div>
    <v-card color="primary">
      <v-system-bar></v-system-bar>
      <v-toolbar flat>
        <v-toolbar-title>Akil Security & Alarm System</v-toolbar-title>
        <v-spacer> </v-spacer>

        <!-- <v-img
            lazy-src="https://picsum.photos/id/11/10/6"
            max-height="300"
            max-width="150"
            src="https://picsum.photos/id/11/500/300"
          ></v-img> -->

        <v-list-item-avatar tile size="130" class="pa-0">
          <v-img
            style="
              border-radius: 100%;
              height: 95px;
              max-width: 108px;
              margin: 35px 0px 0px 23px;
            "
            src="https://picsum.photos/id/11/500/300"
          >
          </v-img>
        </v-list-item-avatar>
      </v-toolbar>
      <v-banner single-line :sticky="sticky">
        We can't save your edits while you are in offline mode.

        <!-- <template v-slot:actions>
          <v-btn text color="deep-purple accent-4"> Get Online </v-btn>
        </template> -->
      </v-banner>
      <v-card-text class="grey lighten-4">
        <v-sheet height="200" class="mx-auto">sdsdsd</v-sheet>
      </v-card-text>
    </v-card>

    <v-simple-table>
      <template v-slot:default>
        <thead>
          <tr>
            <th
              class="text-left font-weight-black"
              style="font-size: 15px"
              v-for="(item, index) in headers"
              :key="index"
            >
              <label>{{ item.text }}</label>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in 10" :key="i">
            <td style="width: 50px">02-Sep-22</td>
            <td style="width: 80px">E0001</td>
            <td style="width: 110px">fahath</td>
            <td style="width: 60px">it</td>
            <td style="width: 60px">Auto Shift</td>
            <td style="width: 50px">---</td>
            <td style="width: 30px">
              <v-icon :color="i > 5 ? 'error' : 'success'">
                {{ i > 5 ? "mdi-close-thick" : "mdi-check" }}
              </v-icon>
            </td>
            <td style="width: 50px">9.30</td>
            <td style="width: 40px">8.00</td>
            <td style="width: 50px">8</td>
            <td style="width: 50px">2</td>
            <td style="width: 52px">15</td>
            <td style="width: 50px">25</td>
            <td style="width: 50px">MED</td>
            <td style="width: 50px">CCT</td>
          </tr>
        </tbody>
      </template>
    </v-simple-table>
  </div>
</template>
<script>
export default {
  data() {
    return {
      items: [],
      headers: [
        { text: "Date", align: "left", sortable: false, value: "date" },
        { text: "E.ID", align: "left", sortable: false, value: "employee_id" },
        {
          text: "First Name",
          align: "left",
          sortable: false,
          value: "employee.first_name",
        },
        {
          text: "Dept",
          align: "left",
          sortable: false,
          value: "employee.department.name",
        },
        {
          text: "Shift Type",
          align: "left",
          sortable: false,
          value: "shift_type.name",
        },
        {
          text: "Shift",
          align: "left",
          sortable: false,
          value: "shift",
        },
        { text: "Status", align: "left", sortable: false, value: "status" },
        { text: "In", align: "left", sortable: false, value: "in" },
        { text: "Out", align: "left", sortable: false, value: "out" },
        {
          text: "Total Hrs",
          align: "left",
          sortable: false,
          value: "total_hrs",
        },
        { text: "OT", align: "left", sortable: false, value: "ot" },
        {
          text: "Late coming",
          align: "left",
          sortable: false,
          value: "late_coming",
        },
        {
          text: "Early Going",
          align: "left",
          sortable: false,
          value: "early_going",
        },
        {
          text: "D.In",
          align: "left",
          sortable: false,
          value: "device_in",
        },
        {
          text: "D.Out",
          align: "left",
          sortable: false,
          value: "device_out",
        },
      ],
    };
  },

  created() {},

  computed: {
    getDaysInCurrentMonth() {
      const date = new Date();
      return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    },

    getDayInCurrentMonth() {
      const date = new Date();
      return date.getDate();
    },

    randNo() {
      return Math.floor(Math.random() * 2);
    },
  },

  methods: {},
};
</script>
<style scoped>
table,
th,
td,
tbody {
  border-width: thin;

  border: 1px solid #e0e0e0;
}

th,
td {
  padding: 1px;
}
table,
th {
  text-align: center;
}
</style>
