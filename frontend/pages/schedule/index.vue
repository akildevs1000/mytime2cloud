<template>
  <div v-if="can('setting_company_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <!-- <v-dialog v-model="editDialog" max-width="50%">
      <v-card>
        <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span>Edit List</span>
        </v-toolbar>

        <v-card-text>
          <v-container>
            <v-card-title>
              <v-row>
                <v-col md="4">
                  <v-text-field
                    :hide-details="true"
                    v-model="name"
                    placeholder="Name"
                    outlined
                    dense
                    :class="errors && errors.name ? 'mb-0' : 'mb-2'"
                  ></v-text-field>
                  <small
                    style="font-size: 13px"
                    v-if="errors && errors.name"
                    class="error--text ma-0 pa-0"
                    >{{ errors.name[0] }}</small
                  >
                </v-col>
              </v-row>
              <table style="width: 100%">
                <tr style="font-size: 15px">
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
                  v-for="(item, index) in editShifts"
                  :key="index"
                  style="text-align: center; font-size: 15px"
                >
                  <td style="width: 150px; text-align: center">
                    <label class="col-form-label"
                      ><b>{{ item.days }}</b></label
                    >
                  </td>
                  <td style="width: 400px; text-align: left">
                    {{ item.shift_ids }}
                    <v-select
                      class="mx-5 py-2"
                      :items="shifts"
                      dense
                      outlined
                      item-text="name"
                      item-value="id"
                      v-model="item.shift_ids"
                      placeholder="Select"
                      :hide-details="true"
                      @change="getTimeRange(item.shift_ids)"
                    >
                    </v-select>
                    <small
                      class="red--text text-left"
                      v-if="!shift[index] && errors && errors.shift_ids"
                      style="margin-left: 20px; font-size: 13px"
                    >
                      {{ errors.shift_ids[0] }}
                    </small>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label">
                      {{ getFrom(item.shift_ids) }}
                    </label>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label">{{
                      getTo(item.shift_ids)
                    }}</label>
                  </td>
                  <pre>
                    {{ item }}
                    {{ updatedData(item) }}
                  </pre>
                </tr>
              </table>
            </v-card-title>
            <small
              style="font-size: 13px"
              v-if="errors && errors.days"
              class="error--text ma-0 pa-0"
            >
              {{ errors.days[0] }}
            </small>
          </v-container>
        </v-card-text>
        <v-card-actions class="mr-8">
          <v-spacer></v-spacer>
          <v-btn class="error" small @click="editDialog = false">
            Cancel
          </v-btn>
          <v-btn class="primary" small @click="update">Update</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog> -->

    <v-dialog v-model="editDialog" max-width="35%" persistent>
      <v-card class="pb-0 mb-0">
        <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span>Edit Schedule</span>
        </v-toolbar>
        <v-card-text class="pb-0 mb-0">
          <v-container>
            <v-card-title>
              <v-row>
                <v-col md="6">
                  <v-text-field
                    :hide-details="true"
                    v-model="editName"
                    placeholder="Name"
                    outlined
                    dense
                    :class="errors && errors.name ? 'mb-0' : 'mb-2'"
                  ></v-text-field>
                  <small
                    style="font-size: 12px"
                    v-if="errors && errors.name"
                    class="error--text ma-0 pa-0"
                    >{{ errors.name[0] }}</small
                  >
                </v-col>
              </v-row>
              <table style="width: 100%">
                <tr style="font-size: 15px">
                  <td style="width: 130px; text-align: center">
                    <label class="col-form-label"><b>Day</b></label>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label"><b>Shifts</b></label>
                  </td>
                  <td style="text-align: center; width: 120px">
                    <label class="col-form-label"><b>From</b></label>
                  </td>
                  <td style="text-align: center; width: 120px">
                    <label class="col-form-label"><b>To</b></label>
                  </td>
                </tr>
                <!-- <pre> {{ editShifts }}</pre> -->
                <tr
                  v-for="(item, index) in editShifts"
                  :key="index"
                  style="text-align: center; font-size: 15px"
                >
                  <td style="width: 80px; text-align: center">
                    <label class="col-form-label"
                      ><b>{{ item.day }}</b></label
                    >
                  </td>
                  <td
                    style="
                      width: 400px;
                      text-align: left;
                      padding: 8px 0px 0px 0px;
                    "
                  >
                    <v-select
                      class="mx-5 py-0"
                      :items="shifts"
                      dense
                      outlined
                      item-text="name"
                      item-value="id"
                      placeholder="Select"
                      :hide-details="true"
                      v-model="item.shift_id"
                    >
                    </v-select>
                    <small
                      class="error--text text-left py-0 my-0"
                      v-if="!shift[index] && errors && errors.shift_ids"
                      style="margin-left: 20px; font-size: 12px"
                    >
                      {{ errors.shift_ids[0] }}
                    </small>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label">
                      {{ getFrom(item.shift_id) }}
                    </label>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label">{{
                      getTo(item.shift_id)
                    }}</label>
                  </td>
                </tr>
              </table>
            </v-card-title>
            <v-card-actions class="mr-1">
              <v-spacer></v-spacer>
              <v-btn class="error" small @click="close"> Cancel </v-btn>
              <v-btn class="primary" small @click="update_schedule"
                >Update</v-btn
              >
            </v-card-actions>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialog" max-width="35%" persistent>
      <v-card class="pb-0 mb-0">
        <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span>{{ formTitle }} Schedule</span>
        </v-toolbar>

        <v-card-text class="pb-0 mb-0">
          <v-container>
            <v-card-title>
              <v-row>
                <v-col md="6">
                  <v-text-field
                    :hide-details="true"
                    v-model="name"
                    placeholder="Name"
                    outlined
                    dense
                    :class="errors && errors.name ? 'mb-0' : 'mb-2'"
                  ></v-text-field>
                  <small
                    style="font-size: 12px"
                    v-if="errors && errors.name"
                    class="error--text ma-0 pa-0"
                    >{{ errors.name[0] }}</small
                  >
                </v-col>
              </v-row>
              <table style="width: 100%">
                <tr style="font-size: 15px">
                  <td style="width: 130px; text-align: center">
                    <label class="col-form-label"><b>Day</b></label>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label"><b>Shifts</b></label>
                  </td>
                  <td style="text-align: center; width: 120px">
                    <label class="col-form-label"><b>From</b></label>
                  </td>
                  <td style="text-align: center; width: 120px">
                    <label class="col-form-label"><b>To</b></label>
                  </td>
                </tr>
                <tr
                  v-for="(item, index) in days"
                  :key="index"
                  style="text-align: center; font-size: 15px"
                >
                  <td style="width: 80px; text-align: center">
                    <label class="col-form-label"
                      ><b>{{ item }}</b></label
                    >
                  </td>
                  <td
                    style="
                      width: 400px;
                      text-align: left;
                      padding: 8px 0px 0px 0px;
                    "
                  >
                    <!-- {{ shift }}
                    {{ shift[index] }} -->
                    <v-select
                      class="mx-5 py-0"
                      :items="shifts"
                      dense
                      outlined
                      item-text="name"
                      item-value="id"
                      v-model="shift[index]"
                      placeholder="Select"
                      :hide-details="true"
                      @change="getTimeRange(shift[index])"
                    >
                    </v-select>
                    <small
                      class="error--text text-left py-0 my-0"
                      v-if="!shift[index] && errors && errors.shift_ids"
                      style="margin-left: 20px; font-size: 12px"
                    >
                      {{ errors.shift_ids[0] }}
                    </small>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label">
                      {{ getFrom(shift[index]) }}
                    </label>
                  </td>
                  <td style="max-width: 150px; text-align: center">
                    <label class="col-form-label">{{
                      getTo(shift[index])
                    }}</label>
                  </td>
                </tr>
              </table>
            </v-card-title>
            <v-card-actions class="mr-1">
              <v-spacer></v-spacer>
              <v-btn class="error" small @click="close"> Cancel </v-btn>
              <v-btn class="primary" small @click="save">Save</v-btn>
            </v-card-actions>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <div v-if="!preloader">
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
              <v-btn color="background" dark @click="dialog = true">
                <v-icon>mdi-plus</v-icon> Add Schedule
              </v-btn>
            </v-card-title>
            <v-card-title>
              <!-- <pre>{{ scheduleData }}</pre> -->
              <table style="width: 100%">
                <tr>
                  <td style="width: 130px">
                    <label class="col-form-label"><b>Name</b></label>
                  </td>
                  <td style="width: 85%">
                    <label class="col-form-label">Description</label>
                  </td>
                  <td style="width: 2px">
                    <div class="text-center">
                      <label class="col-form-label"> <b>Action</b></label>
                    </div>
                  </td>
                </tr>
                <!-- <pre>{{ scheduleData }}</pre> -->
                <tr v-for="(item, index) in scheduleData" :key="index">
                  <td style="max-width: 10px">
                    <label class="col-form-label">{{ item.name }}</label>
                  </td>
                  <td style="width: 50%">
                    <v-chip
                      class="col-form-label mr-1"
                      v-for="(j, s) in item.json"
                      :key="s"
                    >
                      {{ j.day }} {{ j.time }} {{ getShiftType(j.shift_id) }}
                    </v-chip>
                  </td>
                  <td style="width: 2px">
                    <v-menu bottom left>
                      <template v-slot:activator="{ on, attrs }">
                        <div class="text-center">
                          <v-btn dark-2 icon v-bind="attrs" v-on="on">
                            <v-icon>mdi-dots-vertical</v-icon>
                          </v-btn>
                        </div>
                      </template>
                      <v-list width="120" dense>
                        <v-list-item @click="editItem(item)">
                          <v-list-item-title style="cursor: pointer">
                            <v-icon color="secondary" small>
                              mdi-pencil
                            </v-icon>
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
              </table>
            </v-card-title>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    color: "primary",
    e1: 1,
    menu2: false,
    dialog: false,
    editDialog: false,
    preloader: false,
    loading: false,
    response: false,
    id: "",
    snackbar: false,
    data: [],
    options: {},
    errors: [],
    isEdit: false,
    editedItemId: "",
    Model: "ModelRoster",
    endpoint: "roster",
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
    days: [],
    date: [],
    data: [],
    shifts: [],
    editShifts: [],
    editName: "",
    shift: [],
    editJson: [],
    shift_ids: [],
    shiftNames: [],
    times: [],
    scheduleData: [],
    name: "",
    errors: [],
    selectedDays: [],
    edit_arr: [],
  }),

  watch: {
    dialog() {
      this.shift = {};
      this.errors = [];
      this.isEdit ? "" : (this.name = "");
    },
  },
  computed: {
    formTitle() {
      return this.isEdit ? "Edit" : "New";
    },
  },

  created() {
    this.preloader = false;
    this.id = this.$auth?.user?.company?.id;
    this.get_days();
    this.get_shifts();
    this.get_schedule();
  },
  methods: {
    getUpdateData(index, shift_id, day) {
      console.log(this.editShifts);
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },

    editItem(item) {
      this.editJson = item.json;
      this.editShifts = item.json;
      this.editName = item.name;
      this.isEdit = true;
      this.errors = [];
      this.editedItemId = item.id;
      // this.dialog = true;
      this.editDialog = true;
    },

    close() {
      this.editDialog = false;
      this.dialog = false;
      this.isEdit = false;
    },

    update_schedule() {
      let payload = {
        json: this.editShifts,
        company_id: this.$auth?.user?.company?.id,
        name: this.editName,
      };
      console.log(payload);
      console.log(this.editedItemId);
      // return;
      this.$axios
        .put("/roster" + "/" + this.editedItemId, payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.get_schedule();
            this.isEdit = false;
            this.editDialog = false;
            this.snackbar = data.status;
            this.response = data.message;
          }
        })
        .catch((err) => console.log(err));
    },

    getShiftType(id) {
      if (id == -1) {
        return "(H)";
      } else if (id == -2) {
        return "(A)";
      } else if (id == 33) {
        return "(FL)";
      }
    },

    save() {
      this.shiftNames = [];
      for (let x in this.shift) {
        let filteredData = this.shifts.find((e) => e.id == this.shift[x]);
        let name = filteredData.name || "---";
        this.shiftNames.push(name);
      }
      let payload = {
        days: this.days,
        shift_ids: this.shift,
        shift_names: this.shiftNames,
        company_id: this.$auth?.user?.company?.id,
        name: this.name,
      };

      if (this.isEdit) {
        this.$axios
          .put("/roster" + "/" + this.editedItemId, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.get_schedule();
              this.isEdit = false;
              this.dialog = false;
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
      } else {
        console.log(payload);
        this.$axios
          .post("/roster", payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = data.errors;
              return;
            }
            this.get_schedule();
            this.isEdit = false;
            this.dialog = false;
            this.snackbar = data.status;
            this.response = data.message;
          })
          .catch((e) => console.log(e));
      }
    },

    getTimeRange(id) {
      let { on_duty_time: on, off_duty_time: off } = this.shifts.find(
        (e) => e.id == id
      );
      let range = id == -1 || id == -2 ? "---" : on + "-" + off;
      if (!this.times.includes(range)) {
        this.times.push(range);
      }
      return;
    },

    getFrom(id) {
      let shift = this.shifts.find((e) => e.id == id);
      return (shift && shift.on_duty_time) || "---";
    },

    getTo(id) {
      let shift = this.shifts.find((e) => e.id == id);
      return (shift && shift.off_duty_time) || "---";
    },

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
        let arr = [
          {
            id: -1,
            name: "Off",
          },
          {
            id: -2,
            name: "AutoShift",
          },
        ];
        this.shifts = data.data.concat(arr);
      });
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete("roster" + "/" + item.id)
          .then(({ data }) => {
            this.snackbar = data.status;
            this.get_schedule();
            this.response = data.message;
          })
          .catch((err) => console.log(err));
    },

    get_schedule() {
      let options = {
        per_page: 20,
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get("roster", { params: options }).then(({ data }) => {
        this.scheduleData = data.data;
      });
    },
  },
};
</script>
<style scoped>
td,
th {
  border: 1px solid #dddddd;
  padding-left: 5px;
}
/* tr:nth-child(even) {
  background-color: #dddddd;
} */
</style>
<style scoped>
/* @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&display=swap'); */

* {
  box-sizing: border-box;
}

body > div {
  min-height: 100vh;
  display: flex;
  font-family: "Roboto", sans-serif;
}

.table_responsive {
  max-width: 900px;
  border: 1px solid #00bcd4;
  background-color: #efefef33;
  padding: 15px;
  overflow: auto;
  margin: auto;
  border-radius: 4px;
}

table {
  width: 100%;
  font-size: 13px;
  color: #444;
  white-space: nowrap;
  border-collapse: collapse;
}

table > thead {
  background-color: #00bcd4;
  color: #fff;
}

table > thead th {
  padding: 15px;
}

table th,
table td {
  border: 1px solid #00000017;
  padding: 10px 15px;
}

table > tbody > tr > td > img {
  display: inline-block;
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  border: 4px solid #fff;
  box-shadow: 0 2px 6px #0003;
}

.action_btn {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.action_btn > a {
  text-decoration: none;
  color: #444;
  background: #fff;
  border: 1px solid;
  display: inline-block;
  padding: 7px 20px;
  font-weight: bold;
  border-radius: 3px;
  transition: 0.3s ease-in-out;
}

.action_btn > a:nth-child(1) {
  border-color: #26a69a;
}

.action_btn > a:nth-child(2) {
  border-color: orange;
}

.action_btn > a:hover {
  box-shadow: 0 3px 8px #0003;
}

table > tbody > tr {
  background-color: #fff;
  transition: 0.3s ease-in-out;
}

table > tbody > tr:nth-child(even) {
  background-color: rgb(238, 238, 238);
}

table > tbody > tr:hover {
  filter: drop-shadow(0px 2px 6px #0002);
}
</style>
