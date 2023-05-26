<template>
  <div v-if="can(`employee_schedule_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialog" max-width="1100">
      <v-card>
        <v-card-title>
          {{ Module }}
        </v-card-title>

        <v-card-text>
          <v-row>
            <v-col>
              <input
                style="padding-top: 8px;"
                class="form-control"
                v-model="editedItem.timezone_name"
              />
              <span
                class="error--text"
                v-if="errors.timezone_name && errors.timezone_name[0]"
              >
                {{ errors.timezone_name[0] }}
              </span>
            </v-col>
            <v-col>
              <select
                @change="setDefault(editedItem.timezone_id)"
                class="form-select"
                v-model="editedItem.timezone_id"
                :error-messages="errors.timezone_id && errors.timezone_id[0]"
              >
                <option disabled selected value="0">Timezone Id</option>
                <option v-for="n in 64" :key="n" :value="n"
                  >Tz{{ n }} <span v-if="n == 1">(24 Hrs)</span>
                </option>
              </select>
              <span
                class="error--text"
                v-if="errors.timezone_id && errors.timezone_id[0]"
              >
                {{ errors.timezone_id[0] }}
              </span>
            </v-col>
          </v-row>
        </v-card-text>

        <v-card-text>
          <table style="width:100%;">
            <thead>
              <tr class="background white--text" dark>
                <th class="text-center">Time</th>
                <th
                  class="text-center"
                  colspan="2"
                  v-for="n in 4"
                  :key="n"
                  :value="n"
                >
                  Interval {{ n }}
                </th>
              </tr>
              <tr>
                <th class="text-center">Weekday</th>
                <th class="text-center">begin Time</th>
                <th class="text-center">End Time</th>
                <th class="text-center">begin Time</th>
                <th class="text-center">End Time</th>
                <th class="text-center">begin Time</th>
                <th class="text-center">End Time</th>
                <th class="text-center">begin Time</th>
                <th class="text-center">End Time</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(d, index) in days" :key="index">
                <td>{{ d.name }}</td>
                <td>
                  <!-- <v-menu
                    ref="menu"
                    v-model="menu2"
                    :close-on-content-click="false"
                    :nudge-right="40"
                    :return-value.sync="time"
                    transition="scale-transition"
                    offset-y
                    max-width="290px"
                    min-width="290px"
                  >
                    <template v-slot:activator="{ on, attrs }">
                      <v-text-field
                        v-model="time"
                        label="HH:MM"
                        append-icon="mdi-clock-time-four-outline"
                        readonly
                        v-bind="attrs"
                        v-on="on"
                      ></v-text-field>
                    </template>
                    <v-time-picker
                      v-if="menu2"
                      v-model="time"
                      format="24hr"
                      full-width
                      @click:minute="$refs.menu.save(time)"
                    ></v-time-picker>
                  </v-menu> -->

                  <!-- @input="
                      processInput(
                        d.index,
                        'interval1',
                        'begin',
                        editedItem.interval[d.index]['interval1']['begin']
                      )
                    " -->
                  <input
                    v-model="editedItem.interval[d.index]['interval1']['begin']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval1']['end']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval2']['begin']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval2']['end']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval3']['begin']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval3']['end']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval4']['begin']"
                    type="time"
                  />
                </td>
                <td>
                  <input
                    v-model="editedItem.interval[d.index]['interval4']['end']"
                    type="time"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </v-card-text>

        <v-card-actions v-if="!readOnly">
          <v-btn small color="primary" @click="submit">Submit</v-btn>
          <v-btn small color="background white--text" @click="reset"
            >Reset</v-btn
          >
          <v-btn small color="grey white--text" @click="close">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="syncDeviceDialog" max-width="1100">
      <v-card>
        <v-card-title>
          Sync Device
        </v-card-title>
        <v-card-text>
          <table style="width:100%;">
            <thead>
              <tr class="background white--text" dark>
                <th style="width:20%;">Device ID</th>
                <th style="width:70%;">Message</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(d, index) in deviceResults" :key="index">
                <td>{{ d.DeviceID }}</td>
                <td>{{ d.message }}</td>
                <td class="text-center">
                  <v-icon color="primary" v-if="d.status">mdi-check</v-icon>
                  <v-icon color="error" v-else>mdi-close</v-icon>
                </td>
              </tr>
            </tbody>
          </table>
          <br />
          <v-btn
            small
            color="grey white--text"
            @click="syncDeviceDialog = false"
          >
            Close</v-btn
          >
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row class="mt-5 mb-5">
      <v-col cols="6">
        <h3>{{ Module }}</h3>
        <div>Dashboard / {{ Module }}</div>
      </v-col>
      <v-col cols="6">
        <div class="text-right">
          <v-btn @click="openDeviceDialog" small color="primary" class="mb-2">
            Sync Device <v-icon class="mx-1">mdi-laptop</v-icon>
          </v-btn>
          <v-btn @click="dialog = true" small color="primary" class="mb-2">
            {{ Module }} <v-icon class="mx-1">mdi-plus</v-icon></v-btn
          >
        </div>
      </v-col>
    </v-row>
    <!-- <v-row>
      <v-col xs="12" sm="12" md="3" cols="12">
        <v-select
          class="form-control custom-text-box shadow-none"
          @change="getDataFromApi(`timezone`)"
          v-model="pagination.per_page"
          :items="[50, 100, 500, 1000]"
          placeholder="Per Page Records"
          solo
          flat
          :hide-details="true"
        ></v-select>
      </v-col>

      <v-col xs="12" sm="12" md="3" cols="12">
        <input
          class="form-control py-3 custom-text-box floating shadow-none"
          placeholder="Search..."
          @input="searchIt"
          v-model="search"
          type="text"
        />
      </v-col>
    </v-row> -->

    <v-card>
      <table>
        <thead>
          <tr class="background white--text" dark>
            <th class="text-center">Timezone ID</th>
            <th class="text-center">Timezone Name</th>
            <th class="text-center">Days</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <v-progress-linear
          v-if="loading"
          :active="loading"
          :indeterminate="loading"
          absolute
          color="primary"
        ></v-progress-linear>
        <tbody>
          <tr
            v-for="(item, index) in data"
            :key="index"
            style="font-size: 13px"
          >
            <td class="text-center">{{ item.timezone_id }}</td>
            <td class="text-center">{{ item.timezone_name }}</td>
            <td class="text-center">
              <v-btn
                v-for="({ day, isScheduled }, idx) in item.scheduled_days"
                :key="idx"
                :class="isScheduled ? `circle-btn-green` : `circle-btn-grey`"
                class="mx-1"
                fab
                small
              >
                <span :class="isScheduled ? `primary--text` : `grey--text`">{{
                  day
                }}</span>
              </v-btn>
            </td>
            <td class="text-center">
              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dark-2 icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list width="120" dense>
                  <v-list-item @click="viewItem(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-eye </v-icon>
                      View
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="editItem(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-pencil </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="deleteItem(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="error" small> mdi-delete </v-icon>
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </td>
          </tr>
        </tbody>
      </table>
    </v-card>
    <v-row>
      <v-col md="12" class="float-right">
        <div class="float-right">
          <v-pagination
            v-model="pagination.current"
            :length="pagination.total"
            @input="onPageChange"
            :total-visible="12"
          ></v-pagination>
        </div>
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
let days = [
  { order: "", dayWeek: "0", index: "0", name: "Sunday", short_name: "SU" },
  { order: "", dayWeek: "1", index: "1", name: "Monday", short_name: "M" },
  { order: "", dayWeek: "2", index: "2", name: "Tuesday", short_name: "T" },
  { order: "", dayWeek: "3", index: "3", name: "Wednesday", short_name: "W" },
  { order: "", dayWeek: "4", index: "4", name: "Thursday", short_name: "TH" },
  { order: "", dayWeek: "5", index: "5", name: "Friday", short_name: "F" },
  { order: "", dayWeek: "6", index: "6", name: "Saturday", short_name: "SA" }
];
export default {
  data: () => ({
    pagination: {
      current: 1,
      total: 0,
      per_page: 10
    },
    Module: "Timezone",
    options: {},
    endpoint: "timezone",
    search: "",
    snackbar: false,
    dialog: false,
    syncDeviceDialog: false,

    loading: false,
    loading_dialog: false,
    isEdit: false,
    total: 0,
    response: "",
    data: [],
    dayBoxes: [],
    errors: [],

    days,
    editedItem: {
      timezone_id: "0",
      timezone_name: "Timzone Name",
      interval: {
        "1": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "2": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "3": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "4": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "5": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "6": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "0": { interval1: {}, interval2: {}, interval3: {}, interval4: {} }
      }
    },

    defaultItem: {
      timezone_id: "0",
      timezone_name: "Timzone Name",
      interval: {
        "1": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "2": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "3": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "4": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "5": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "6": { interval1: {}, interval2: {}, interval3: {}, interval4: {} },
        "0": { interval1: {}, interval2: {}, interval3: {}, interval4: {} }
      }
    },

    deviceResults: [],
    readOnly: false,
    editedIndex: -1
  }),

  computed: {},

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true
    },
    options_dialog: {
      handler() {
        this.getDataFromApiForDialog();
      },
      deep: true
    },
    search() {
      this.pagination.current = 1;
      this.searchIt();
    }
  },
  created() {
    this.loading = true;
    this.loading_dialog = true;

    this.options = {
      params: {
        per_page: 1000,
        company_id: this.$auth.user.company.id
      }
    };
    this.editedItem.company_id = this.$auth.user.company.id;
  },

  methods: {
    // processInput(index, interval, type, input) {
    //   this.editedItem.interval[index] = { "begin": interval,type, input };
    // },
    onPageChange() {
      this.getDataFromApi();
    },
    viewItem(item) {
      this.dialog = true;
      this.readOnly = true;
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
    },
    editItem(item) {
      console.log(this.editedItem, item);
      this.dialog = true;
      this.readOnly = false;
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
    },
    showShortDays(days) {
      this.editedItem.interval = days;
      console.log(days);

      let arr = [];
      for (let day in days) {
        for (let interval in days[day]) {
          if (
            days[day][interval].hasOwnProperty("begin") &&
            days[day][interval].hasOwnProperty("end")
          ) {
            arr.push({
              day: this.days[day]["short_name"],
              dayWeek: this.days[day]["dayWeek"],
              isScheduled: true
            });
            break;
          } else {
            arr.push({
              day: this.days[day]["short_name"],
              dayWeek: this.days[day]["dayWeek"],
              isScheduled: false
            });
            break;
          }
        }
      }
      return arr;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, c => c.toUpperCase());
      }
    },
    async openDeviceDialog() {
      if (!this.data.length) {
        this.snackbar = true;
        this.response = "No data found";
        return;
      }
      this.syncDeviceDialog = true;

      try {
        let endpoint = "getDevicesCountForTimezone";
        const { data } = await this.$axios.post(endpoint, this.editedItem);
        this.processTimeZone(data);
      } catch (error) {
        console.log(error && error.message);
      }
    },
    processTimeZone(devices) {
      this.deviceResults = [];
      let payload = {
        company_id: this.$auth.user.company.id
      };
      devices.forEach(async DeviceID => {
        try {
          let endpoint = `${DeviceID}/WriteTimeGroup`;
          const { data } = await this.$axios.post(endpoint, payload);
          let json = {
            DeviceID,
            message: `Error found on device`,
            status: false
          };

          if (data.status == 200) {
            json.message = `Timezone data has been upload `;
            json.status = true;
          }

          this.deviceResults.push(json);
        } catch (error) {
          console.log(error && error.message);
        }
      });
    },
    close() {
      this.dialog = false;
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    //main
    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      let page = this.pagination.current;

      let options = {
        params: {
          per_page: this.pagination.per_page,
          page: page,
          company_id: this.$auth.user.company.id
        }
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },
    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
      }
    },
    setDefault(v) {
      if (v == 1) {
        this.days.forEach((e, i) => {
          console.log(this.editedItem.interval[e.index]);
          for (let j = 1; j <= 4; j++) {
            this.editedItem.interval[e.index][`interval${j}`]["begin"] =
              "00:00";
            this.editedItem.interval[e.index][`interval${j}`]["end"] = "23:59";
          }
        });
      }
    },
    reset() {
      this.days.forEach(e => {
        for (let j = 1; j <= 4; j++) {
          this.editedItem.interval[e.index][`interval${j}`] = {};
          this.editedItem.interval[e.index][`interval${j}`] = {};
        }
      });
    },
    submit() {
      let sortedDays = this.showShortDays(this.editedItem.interval);
      this.editedItem["scheduled_days"] = sortedDays;
      console.log(this.editedItem);
      return;
      return this.editedIndex === -1 ? this.store() : this.update();
    },
    store() {
      this.$axios
        .post(`/${this.endpoint}`, this.editedItem)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
            return;
          }
          this.snackbar = data.status;
          this.response = data.message;
          this.getDataFromApi();
        })
        .catch(err => {
          console.log(err.message);
        });
    },

    update() {
      this.$axios
        .put(`/${this.endpoint}/${this.editedItem.id}`, this.editedItem)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
            return;
          }
          this.snackbar = data.status;
          this.response = data.message;
          this.getDataFromApi();
        })
        .catch(err => {
          console.log(err.message);
        });
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/` + item.id)
          .then(({ data }) => {
            const index = this.data.indexOf(item);
            this.data.splice(index, 1);
            this.snackbar = data.status;
            this.response = data.message;
            this.getDataFromApi();
          })
          .catch(err => console.log(err));
    }
  }
};
</script>
<style scoped>
.circle-btn-grey {
  border-radius: 50%;
  border: 1px solid grey;
}
.circle-btn-green {
  border-radius: 50%;
  border: 1px solid #5fafa3;
}
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
/* input[type="time"]::-webkit-datetime-edit-ampm-field {
  display: none;
} */
</style>
